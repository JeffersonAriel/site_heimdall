<?php

namespace Modules\Notifications\Listeners;

use Modules\Orders\Events\OrderCreated;
use Modules\Notifications\Services\NotificationService;

class NotifyOnOrderCreated
{
    public function __construct(protected NotificationService $notificationService) {}

    public function handle(OrderCreated $event): void
    {
        $order = $event->order;
        $customerName = $order->customer ? $order->customer->name : 'Cliente';
        
        $this->notificationService->send(
            "Novo Pedido Recebido",
            "O pedido #{$order->id} de {$customerName} foi recebido no valor de R$ " . number_class_format($order->total) . ".",
            'info'
        );
    }
}

// Simple formatting helper if not defined
if (!function_exists('number_class_format')) {
    function number_class_format($val) {
        return number_format($val, 2, ',', '.');
    }
}
