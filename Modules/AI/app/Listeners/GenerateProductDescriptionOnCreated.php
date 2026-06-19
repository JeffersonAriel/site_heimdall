<?php

namespace Modules\AI\Listeners;

use Modules\Products\Events\ProductCreated;
use Modules\AI\Services\AiService;
use Illuminate\Support\Facades\Log;

class GenerateProductDescriptionOnCreated
{
    protected AiService $aiService;

    public function __construct(AiService $aiService)
    {
        $this->aiService = $aiService;
    }

    /**
     * Handle the event.
     */
    public function handle(ProductCreated $event): void
    {
        $product = $event->product;
        Log::info("AI Listener: Gerando descrição para produto #{$product->id}");

        // Only generate if description is currently empty
        if (empty($product->description)) {
            $description = $this->aiService->generateProductDescription(
                $product->name,
                $product->category ?? 'Geral'
            );

            if ($description) {
                $product->description = $description;
                $product->save();
                Log::info("AI Listener: Descrição preenchida com sucesso para o produto #{$product->id}");
            }
        }
    }
}
