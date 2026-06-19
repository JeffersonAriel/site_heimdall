<?php

namespace Modules\Notifications\Listeners;

use Modules\Stock\Events\StockLowAlert;
use Modules\Notifications\Services\NotificationService;

class NotifyOnStockLow
{
    public function __construct(protected NotificationService $notificationService) {}

    public function handle(StockLowAlert $event): void
    {
        $stockItem = $event->stockItem;
        $productName = $stockItem->product ? $stockItem->product->name : 'Produto ID: ' . $stockItem->product_id;

        $this->notificationService->send(
            "Alerta de Estoque Baixo",
            "O estoque do produto '{$productName}' atingiu nível crítico: {$stockItem->quantity} unidades restantes.",
            'danger'
        );
    }
}
