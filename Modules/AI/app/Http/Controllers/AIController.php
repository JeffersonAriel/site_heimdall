<?php

namespace Modules\AI\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\AI\Models\AiLog;
use Modules\AI\Services\AiService;
use Modules\Products\Models\Product;
use App\Models\FeatureFlag;

class AIController extends Controller
{
    protected AiService $aiService;

    public function __construct(AiService $aiService)
    {
        $this->aiService = $aiService;
    }

    /**
     * Get AI Logs / Auditoria.
     */
    public function logs()
    {
        $logs = AiLog::orderBy('created_at', 'desc')->limit(50)->get();
        return response()->json($logs);
    }

    /**
     * Generate product description manually or triggered by click.
     */
    public function generateDescription(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        $product = Product::findOrFail($request->product_id);
        $description = $this->aiService->generateProductDescription($product->name, $product->category ?? 'Geral');

        if ($description) {
            $product->description = $description;
            $product->save();
        }

        return response()->json([
            'message' => 'Descrição gerada e salva com sucesso!',
            'description' => $description,
        ]);
    }

    /**
     * Get feature flag config.
     */
    public function config()
    {
        $flag = FeatureFlag::where('key', 'ai.enabled')->first();
        return response()->json([
            'enabled' => $flag ? (bool)$flag->enabled : false,
            'config' => $flag ? $flag->config : null,
        ]);
    }

    /**
     * Update feature flag.
     */
    public function updateConfig(Request $request)
    {
        $request->validate([
            'enabled' => 'required|boolean',
        ]);

        $flag = FeatureFlag::updateOrCreate(
            ['key' => 'ai.enabled'],
            ['enabled' => $request->enabled]
        );

        return response()->json([
            'message' => 'Feature flag da IA atualizada com sucesso.',
            'enabled' => (bool)$flag->enabled,
        ]);
    }
}
