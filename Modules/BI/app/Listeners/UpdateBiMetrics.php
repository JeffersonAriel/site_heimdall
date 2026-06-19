<?php

namespace Modules\BI\Listeners;

use Modules\Orders\Events\OrderCreated;
use Modules\BI\Models\MetricSnapshot;
use Modules\Orders\Models\Order;
use Modules\Products\Models\Product;
use Modules\Stock\Models\StockItem;
use App\Models\Customer;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class UpdateBiMetrics
{
    /**
     * Handle the event.
     */
    public function handle($event): void
    {
        Log::info("BI: Recalculating metrics based on event.");

        $now = Carbon::now();

        // 1. Total revenue (Faturamento total)
        $totalRevenue = Order::where('status', '!=', 'canceled')->sum('total');
        MetricSnapshot::create([
            'metric_key' => 'faturamento_total',
            'value' => $totalRevenue,
            'calculated_at' => $now,
        ]);

        // 2. Average Ticket (Ticket médio)
        $orderCount = Order::where('status', '!=', 'canceled')->count();
        $averageTicket = $orderCount > 0 ? ($totalRevenue / $orderCount) : 0;
        MetricSnapshot::create([
            'metric_key' => 'ticket_medio',
            'value' => $averageTicket,
            'calculated_at' => $now,
        ]);

        // 3. Conversion Rate (Taxa de conversão: orders / customers)
        $customerCount = Customer::where('status', 'active')->count();
        $conversionRate = $customerCount > 0 ? (($orderCount / $customerCount) * 100) : 0;
        MetricSnapshot::create([
            'metric_key' => 'taxa_conversao',
            'value' => $conversionRate,
            'calculated_at' => $now,
        ]);

        // 4. Critical Stock (Estoque crítico - items below safety threshold e.g. 5 units)
        $criticalStockCount = StockItem::whereRaw('quantity - reserved_quantity <= 5')->count();
        MetricSnapshot::create([
            'metric_key' => 'estoque_critico',
            'value' => $criticalStockCount,
            'calculated_at' => $now,
        ]);
    }
}
