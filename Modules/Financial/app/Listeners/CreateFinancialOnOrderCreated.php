<?php

namespace Modules\Financial\Listeners;

use Modules\Orders\Events\OrderCreated;
use Modules\Financial\Models\AccountsReceivable;
use Carbon\Carbon;

class CreateFinancialOnOrderCreated
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

        AccountsReceivable::create([
            'customer_id' => $order->customer_id,
            'order_id' => $order->id,
            'amount' => $order->total,
            'due_date' => Carbon::now()->addDays(7), // Exemplo: vencimento em 7 dias
            'status' => 'pending',
        ]);
    }
}
