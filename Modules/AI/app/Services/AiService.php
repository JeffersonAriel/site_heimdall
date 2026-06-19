<?php

namespace Modules\AI\Services;

use App\Models\FeatureFlag;
use Modules\AI\Models\AiLog;
use Illuminate\Support\Facades\Log;

class AiService
{
    /**
     * Generate product description based on product information.
     */
    public function generateProductDescription(string $name, string $category): ?string
    {
        if (!FeatureFlag::isEnabled('ai.enabled')) {
            Log::info("AI Service: Ignorado - Feature Flag ai.enabled está desativada.");
            return null;
        }

        $prompt = "Escreva uma descrição atraente de e-commerce de 2 parágrafos para o produto: {$name} na categoria {$category}.";

        // Stub/Mock LLM Call for local testing and compatibility
        $response = "Apresentamos o novo {$name}! Projetado especialmente para a categoria {$category}, este produto combina durabilidade, sofisticação e a mais alta tecnologia para atender a todas as suas expectativas do dia a dia.\n\nCom design ergonômico e acabamento premium, o {$name} é a escolha ideal para quem busca performance excelente sem abrir mão da elegância. Adquira já o seu e experimente uma nova era de praticidade!";
        
        AiLog::create([
            'module' => 'Products',
            'prompt' => $prompt,
            'response' => $response,
            'tokens_used' => 120,
        ]);

        return $response;
    }

    /**
     * Suggest stock replenishment based on items.
     */
    public function suggestReplenishment(string $productName, int $currentQuantity): ?string
    {
        if (!FeatureFlag::isEnabled('ai.enabled')) {
            return null;
        }

        $prompt = "O produto {$productName} está com estoque crítico de {$currentQuantity} unidades. Sugira quantidade ideal de reposição.";
        $response = "Recomendamos a compra de 50 unidades adicionais do produto {$productName} para atender a demanda estimada das próximas 4 semanas, baseado em padrões sazonais de venda.";

        AiLog::create([
            'module' => 'Stock',
            'prompt' => $prompt,
            'response' => $response,
            'tokens_used' => 85,
        ]);

        return $response;
    }

    /**
     * Simple sales analysis report.
     */
    public function analyzeSales(array $salesData): ?string
    {
        if (!FeatureFlag::isEnabled('ai.enabled')) {
            return null;
        }

        $prompt = "Analise o seguinte faturamento dos últimos meses: " . json_encode($salesData);
        $response = "Análise de IA: Detectamos uma tendência de crescimento consistente de 8% ao mês. Os finais de semana representam 60% do volume total de pedidos. Recomendamos manter promoções direcionadas nas sextas-feiras.";

        AiLog::create([
            'module' => 'BI',
            'prompt' => $prompt,
            'response' => $response,
            'tokens_used' => 150,
        ]);

        return $response;
    }
}
