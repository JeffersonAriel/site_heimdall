<?php

namespace Modules\Stock\Listeners;

use Modules\Orders\Events\OrderCreated;
use Modules\Stock\Models\StockItem;
use Modules\Stock\Models\StockMovement;

class ReserveStockOnOrderCreated
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(OrderCreated $event): void
    {
        $order = $event->order;

        foreach ($order->items as $item) {
            // Register stock movement
            StockMovement::create([
                'product_id' => $item->product_id,
                'type' => 'reserve',
                'quantity' => $item->quantity,
                'reference_type' => 'order',
                'reference_id' => $order->id,
            ]);

            // Update stock reserved quantity
            $stockItem = StockItem::firstOrCreate(
                ['product_id' => $item->product_id],
                ['quantity' => 0, 'reserved_quantity' => 0]
            );

            $stockItem->increment('reserved_quantity', $item->quantity);
            $stockItem->decrement('quantity', $item->quantity);

            // Dispatch StockLowAlert if remaining quantity is low (e.g. <= 5)
            if ($stockItem->quantity <= 5) {
                event(new \Modules\Stock\Events\StockLowAlert($stockItem));
            }
        }
    }
}
