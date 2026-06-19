<?php

namespace Modules\Production\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Production\Models\Bom;
use Modules\Production\Models\BomItem;
use Modules\Production\Models\ProductionOrder;
use Modules\Stock\Models\StockItem;
use Modules\Stock\Models\StockMovement;
use Illuminate\Support\Facades\DB;

class ProductionController extends Controller
{
    /**
     * Display a listing of BOMs (Fichas Técnicas).
     */
    public function boms()
    {
        $boms = Bom::with(['product', 'items.product'])->get();
        return response()->json($boms);
    }

    /**
     * Store a new BOM.
     */
    public function storeBom(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'name' => 'required|string|max:255',
            'quantity' => 'required|numeric|min:0.0001',
            'production_cost' => 'required|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.0001',
        ]);

        return DB::transaction(function () use ($request) {
            $bom = Bom::create($request->only('product_id', 'name', 'quantity', 'production_cost'));

            foreach ($request->items as $item) {
                $bom->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                ]);
            }

            return response()->json($bom->load('items.product'), 201);
        });
    }

    /**
     * List Production Orders (Ordens de Produção).
     */
    public function orders()
    {
        $orders = ProductionOrder::with('bom.product')->get();
        return response()->json($orders);
    }

    /**
     * Store new Production Order.
     */
    public function storeOrder(Request $request)
    {
        $request->validate([
            'bom_id' => 'required|exists:boms,id',
            'quantity' => 'required|numeric|min:0.0001',
            'additional_cost' => 'nullable|numeric|min:0',
        ]);

        $order = ProductionOrder::create([
            'bom_id' => $request->bom_id,
            'quantity' => $request->quantity,
            'additional_cost' => $request->additional_cost ?? 0.00,
            'status' => 'pending',
        ]);

        return response()->json($order, 201);
    }

    /**
     * Start Production Order.
     */
    public function startOrder($id)
    {
        $order = ProductionOrder::findOrFail($id);
        if ($order->status !== 'pending') {
            return response()->json(['message' => 'Apenas ordens pendentes podem ser iniciadas.'], 400);
        }

        // Consume raw materials from stock (StockOut)
        $bom = $order->bom;
        
        return DB::transaction(function () use ($order, $bom) {
            foreach ($bom->items as $item) {
                // Total required = (item qty / bom yield qty) * order quantity
                $requiredQty = ($item->quantity / $bom->quantity) * $order->quantity;

                // Create stock movement (Out)
                StockMovement::create([
                    'product_id' => $item->product_id,
                    'type' => 'out',
                    'quantity' => $requiredQty,
                    'reference_type' => 'production_order',
                    'reference_id' => $order->id,
                ]);

                // Update stock item
                $stockItem = StockItem::where('product_id', $item->product_id)->first();
                if ($stockItem) {
                    $stockItem->decrement('quantity', $requiredQty);
                    
                    // Dispatch low stock if applicable
                    if ($stockItem->quantity <= 5) {
                        event(new \Modules\Stock\Events\StockLowAlert($stockItem));
                    }
                }
            }

            $order->update(['status' => 'in_progress']);

            return response()->json(['message' => 'Produção iniciada. Matérias-primas consumidas.', 'order' => $order]);
        });
    }

    /**
     * Complete Production Order.
     */
    public function completeOrder($id)
    {
        $order = ProductionOrder::findOrFail($id);
        if ($order->status !== 'in_progress') {
            return response()->json(['message' => 'Apenas ordens em progresso podem ser finalizadas.'], 400);
        }

        $bom = $order->bom;

        return DB::transaction(function () use ($order, $bom) {
            // Add finished good to stock (StockIn)
            StockMovement::create([
                'product_id' => $bom->product_id,
                'type' => 'in',
                'quantity' => $order->quantity,
                'reference_type' => 'production_order',
                'reference_id' => $order->id,
            ]);

            $stockItem = StockItem::firstOrCreate(
                ['product_id' => $bom->product_id],
                ['quantity' => 0, 'reserved_quantity' => 0]
            );
            $stockItem->increment('quantity', $order->quantity);

            $order->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);

            return response()->json(['message' => 'Produção finalizada. Produto final adicionado ao estoque.', 'order' => $order]);
        });
    }
}
