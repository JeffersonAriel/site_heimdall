<?php

namespace Modules\Stock\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Stock\Models\StockItem;
use Modules\Stock\Models\StockMovement;
use Modules\Stock\Models\StockLocation;
use Modules\Stock\Models\StockLot;
use Modules\Products\Models\Product;
use Illuminate\Support\Facades\DB;

class StockController extends Controller
{
    /**
     * Get stock items list.
     */
    public function index()
    {
        $stocks = StockItem::with('product')->get();
        return response()->json($stocks);
    }

    /**
     * Get stock movements history.
     */
    public function movements()
    {
        $movements = StockMovement::orderBy('created_at', 'desc')->limit(100)->get();
        return response()->json($movements);
    }

    /**
     * Move stock manually (In, Out, Transfer).
     */
    public function move(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'type' => 'required|in:in,out,transfer',
            'quantity' => 'required|integer|min:1',
            'reference_type' => 'nullable|string',
            'reference_id' => 'nullable|integer',
        ]);

        return DB::transaction(function () use ($request) {
            // Create movement
            $movement = StockMovement::create($request->all());

            // Adjust StockItem
            $stockItem = StockItem::firstOrCreate(
                ['product_id' => $request->product_id],
                ['quantity' => 0, 'reserved_quantity' => 0]
            );

            if ($request->type === 'in') {
                $stockItem->increment('quantity', $request->quantity);
            } elseif ($request->type === 'out') {
                $stockItem->decrement('quantity', $request->quantity);

                // Dispatch low stock warning
                if ($stockItem->quantity <= 5) {
                    event(new \Modules\Stock\Events\StockLowAlert($stockItem));
                }
            }

            return response()->json(['message' => 'Movimentação realizada com sucesso.', 'movement' => $movement]);
        });
    }

    /**
     * Get stock locations.
     */
    public function locations()
    {
        return response()->json(StockLocation::all());
    }

    /**
     * Store new stock location.
     */
    public function storeLocation(Request $request)
    {
        $request->validate([
            'warehouse' => 'required|string|max:255',
            'aisle' => 'nullable|string|max:255',
            'shelf' => 'nullable|string|max:255',
            'position' => 'nullable|string|max:255',
        ]);

        $location = StockLocation::create($request->all());
        return response()->json($location, 201);
    }

    /**
     * Get stock lots.
     */
    public function lots()
    {
        return response()->json(StockLot::with('product')->get());
    }

    /**
     * Store new stock lot.
     */
    public function storeLot(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'lot_number' => 'required|string|max:255',
            'expiry_date' => 'nullable|date',
            'quantity' => 'required|integer|min:1',
        ]);

        $lot = StockLot::create($request->all());
        return response()->json($lot, 201);
    }

    /**
     * Calculate ABC Curve based on product sales revenue.
     */
    public function abcCurve()
    {
        // Calculate total sales per product
        $sales = DB::table('order_items')
            ->select('product_id', DB::raw('SUM(quantity * price) as revenue'))
            ->groupBy('product_id')
            ->orderBy('revenue', 'desc')
            ->get();

        $totalRevenue = $sales->sum('revenue');
        if ($totalRevenue == 0) {
            $totalRevenue = 1; // avoid division by zero
        }

        $cumulativeRevenue = 0;
        $abcData = [];

        foreach ($sales as $sale) {
            $cumulativeRevenue += $sale->revenue;
            $percentage = ($cumulativeRevenue / $totalRevenue) * 100;

            if ($percentage <= 70) {
                $class = 'A';
            } elseif ($percentage <= 90) {
                $class = 'B';
            } else {
                $class = 'C';
            }

            $product = Product::find($sale->product_id);

            $abcData[] = [
                'product_id' => $sale->product_id,
                'name' => $product ? $product->name : 'Desconhecido',
                'sku' => $product ? $product->sku : '-',
                'revenue' => $sale->revenue,
                'percentage' => round(($sale->revenue / $totalRevenue) * 100, 2),
                'class' => $class,
            ];
        }

        return response()->json($abcData);
    }
}
