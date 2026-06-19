<?php

namespace Modules\AI\Listeners;

use Modules\Stock\Events\StockLowAlert;
use Modules\AI\Services\AiService;
use Illuminate\Support\Facades\Log;

class SuggestStockReplenishment
{
    protected AiService $aiService;

    public function __construct(AiService $aiService)
    {
        $this->aiService = $aiService;
    }

    /**
     * Handle the event.
     */
    public function handle(StockLowAlert $event): void
    {
        $stockItem = $event->stockItem;
        Log::info("AI Listener: Analisando reposição de estoque para o produto #{$stockItem->product_id}");

        // Call the service to get suggestion (stub internally checks if feature flag is active)
        $suggestion = $this->aiService->suggestReplenishment(
            "Produto #{$stockItem->product_id}",
            $stockItem->quantity
        );

        if ($suggestion) {
            Log::info("AI Listener: Sugestão obtida: {$suggestion}");
        }
    }
}
