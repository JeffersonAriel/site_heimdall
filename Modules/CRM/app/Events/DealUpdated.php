<?php

namespace Modules\CRM\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\CRM\Models\Deal;

class DealUpdated
{
    use Dispatchable, SerializesModels;

    public Deal $deal;

    public function __construct(Deal $deal)
    {
        $this->deal = $deal;
    }
}
