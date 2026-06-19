<?php

namespace Modules\HelpDesk\Events;

use Illuminate\Queue\SerializesModels;
use Modules\HelpDesk\Models\Ticket;

class TicketCreated
{
    use SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public Ticket $ticket) {}
}
