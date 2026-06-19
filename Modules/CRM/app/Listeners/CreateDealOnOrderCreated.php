<?php

namespace Modules\CRM\Listeners;

use Modules\Orders\Events\OrderCreated;
use Modules\CRM\Models\Lead;
use Modules\CRM\Models\Deal;
use Modules\CRM\Models\PipelineStage;
use Illuminate\Support\Facades\Log;

class CreateDealOnOrderCreated
{
    /**
     * Handle the event.
     */
    public function handle(OrderCreated $event): void
    {
        $order = $event->order;
        Log::info("CRM: Criando Deal/Negócio para o Pedido #{$order->id}");

        // Find or create lead for the customer
        $lead = Lead::where('customer_id', $order->customer_id)->first();
        if (!$lead) {
            // Fetch customer details
            $customer = $order->customer;
            $stage = PipelineStage::orderBy('order_position')->first();
            $lead = Lead::create([
                'name' => $customer ? $customer->name : "Cliente #{$order->customer_id}",
                'email' => $customer?->email,
                'phone' => $customer?->phone,
                'source' => 'e-commerce',
                'status' => 'qualified',
                'pipeline_stage_id' => $stage?->id,
                'customer_id' => $order->customer_id,
            ]);
        }

        // Create deal associated with lead
        $dealStage = PipelineStage::orderBy('order_position', 'desc')->first(); // or qualified stage
        
        Deal::create([
            'lead_id' => $lead->id,
            'order_id' => $order->id,
            'title' => "Negócio - Pedido #{$order->id}",
            'value' => $order->total,
            'status' => 'open',
            'pipeline_stage_id' => $lead->pipeline_stage_id ?: $dealStage?->id,
        ]);
    }
}
