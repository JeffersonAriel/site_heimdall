<?php

namespace Modules\CRM\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\CRM\Models\Lead;
use Modules\CRM\Models\Deal;
use Modules\CRM\Models\Pipeline;
use Modules\CRM\Models\PipelineStage;
use Modules\CRM\Models\Activity;
use Modules\CRM\Events\DealUpdated;

class CRMController extends Controller
{
    /**
     * Display the Kanban Board pipeline stages and leads/deals.
     */
    public function pipeline(Request $request)
    {
        $pipeline = Pipeline::with(['stages.leads.deals', 'stages.deals'])->first();
        if (!$pipeline) {
            // Seed a default pipeline if none exists
            $pipeline = Pipeline::create(['name' => 'Funil de Vendas Padrão']);
            $stages = [
                ['name' => 'Novo Lead', 'order_position' => 1, 'color' => '#3B82F6'],
                ['name' => 'Contato Realizado', 'order_position' => 2, 'color' => '#F59E0B'],
                ['name' => 'Proposta Enviada', 'order_position' => 3, 'color' => '#8B5CF6'],
                ['name' => 'Negócio Fechado', 'order_position' => 4, 'color' => '#10B981'],
                ['name' => 'Negócio Perdido', 'order_position' => 5, 'color' => '#EF4444'],
            ];
            foreach ($stages as $stage) {
                $pipeline->stages()->create($stage);
            }
            $pipeline->load('stages.leads.deals', 'stages.deals');
        }

        return response()->json($pipeline);
    }

    /**
     * Move lead/deal between stages.
     */
    public function updateStage(Request $request, $id)
    {
        $request->validate([
            'type' => 'required|in:lead,deal',
            'pipeline_stage_id' => 'required|exists:pipeline_stages,id',
        ]);

        if ($request->type === 'lead') {
            $lead = Lead::findOrFail($id);
            $lead->update(['pipeline_stage_id' => $request->pipeline_stage_id]);
            return response()->json(['message' => 'Estágio do Lead atualizado com sucesso.', 'lead' => $lead]);
        } else {
            $deal = Deal::findOrFail($id);
            $deal->update(['pipeline_stage_id' => $request->pipeline_stage_id]);
            
            // Dispatch DealUpdated event
            DealUpdated::dispatch($deal);

            return response()->json(['message' => 'Estágio do Negócio atualizado com sucesso.', 'deal' => $deal]);
        }
    }

    /**
     * Create Activity follow-up for a lead.
     */
    public function storeActivity(Request $request)
    {
        $request->validate([
            'lead_id' => 'required|exists:leads,id',
            'title' => 'required|string|max:255',
            'type' => 'required|in:call,email,meeting,task',
            'due_at' => 'required|date',
            'description' => 'nullable|string',
        ]);

        $activity = Activity::create($request->all());

        return response()->json(['message' => 'Atividade agendada com sucesso.', 'activity' => $activity], 201);
    }

    /**
     * List activities.
     */
    public function activities(Request $request)
    {
        $activities = Activity::with('lead')->orderBy('due_at')->get();
        return response()->json($activities);
    }
}
