<?php

namespace Modules\Notifications\Listeners;

use Modules\HelpDesk\Events\TicketCreated;
use Modules\Notifications\Services\NotificationService;

class NotifyOnTicketCreated
{
    public function __construct(protected NotificationService $notificationService) {}

    public function handle(TicketCreated $event): void
    {
        $ticket = $event->ticket;
        $customerName = $ticket->customer ? $ticket->customer->name : 'Cliente';

        $this->notificationService->send(
            "Novo Ticket de Suporte",
            "Cliente {$customerName} abriu o chamado #{$ticket->id}: '{$ticket->subject}' com prioridade {$ticket->priority}.",
            'warning'
        );
    }
}
