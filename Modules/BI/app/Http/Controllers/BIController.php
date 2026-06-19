<?php

namespace Modules\BI\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\BI\Models\MetricSnapshot;
use Modules\Orders\Models\Order;
use Modules\Orders\Models\OrderItem;
use Modules\Products\Models\Product;
use Modules\Stock\Models\StockItem;
use App\Models\Customer;
use Carbon\Carbon;

class BIController extends Controller
{
    /**
     * Get BI Dashboard KPIs.
     */
    public function kpis()
    {
        // Fetch last metrics calculated
        $faturamento = MetricSnapshot::where('metric_key', 'faturamento_total')->latest()->value('value') ?? 0;
        $ticketMedio = MetricSnapshot::where('metric_key', 'ticket_medio')->latest()->value('value') ?? 0;
        $conversionRate = MetricSnapshot::where('metric_key', 'taxa_conversao')->latest()->value('value') ?? 0;
        $stockCritico = MetricSnapshot::where('metric_key', 'estoque_critico')->latest()->value('value') ?? 0;

        return response()->json([
            'faturamento_total' => (float)$faturamento,
            'ticket_medio' => (float)$ticketMedio,
            'taxa_conversao' => (float)$conversionRate,
            'estoque_critico' => (int)$stockCritico,
        ]);
    }

    /**
     * Get top selling products.
     */
    public function topProducts()
    {
        // Query top sold products based on OrderItems
        $topProducts = OrderItem::select('product_id', \DB::raw('SUM(quantity) as total_qty'), \DB::raw('SUM(quantity * price) as total_revenue'))
            ->groupBy('product_id')
            ->orderBy('total_qty', 'desc')
            ->limit(5)
            ->get();

        $topProducts->load('product');

        return response()->json($topProducts);
    }

    /**
     * Get revenue progression per period (monthly/daily).
     */
    public function revenuePeriod(Request $request)
    {
        $days = $request->get('days', 30);
        $startDate = Carbon::now()->subDays($days);

        $revenue = Order::where('status', '!=', 'canceled')
            ->where('created_at', '>=', $startDate)
            ->select(\DB::raw('DATE(created_at) as date'), \DB::raw('SUM(total) as total'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return response()->json($revenue);
    }
}
