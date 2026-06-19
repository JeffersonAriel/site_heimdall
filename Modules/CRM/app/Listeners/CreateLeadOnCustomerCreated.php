<?php

namespace Modules\CRM\Listeners;

use App\Models\Customer;
use Modules\CRM\Models\Lead;
use Modules\CRM\Models\PipelineStage;
use Modules\CRM\Events\LeadCreated;
use Illuminate\Support\Facades\Log;

class CreateLeadOnCustomerCreated
{
    /**
     * Handle the event.
     */
    public function handle($event): void
    {
        // Event can be from CheckoutController or custom Laravel/Model event
        $customer = $event->customer ?? null;
        if (!$customer) {
            return;
        }

        Log::info("CRM: Criando lead para novo cliente: {$customer->email}");

        // Get first stage of first pipeline
        $stage = PipelineStage::orderBy('order_position')->first();

        $lead = Lead::create([
            'name' => $customer->name,
            'email' => $customer->email,
            'phone' => $customer->phone,
            'source' => 'site',
            'status' => 'new',
            'pipeline_stage_id' => $stage?->id,
            'customer_id' => $customer->id,
        ]);

        LeadCreated::dispatch($lead);
    }
}
