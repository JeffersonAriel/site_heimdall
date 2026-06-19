<?php

namespace Modules\HelpDesk\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\HelpDesk\Models\Ticket;
use Modules\HelpDesk\Models\TicketMessage;
use Illuminate\Support\Facades\Auth;

class HelpDeskController extends Controller
{
    /**
     * List tickets for internal ERP view.
     */
    public function index(Request $request)
    {
        $tickets = Ticket::with('customer')->orderBy('created_at', 'desc')->get();
        return response()->json($tickets);
    }

    /**
     * List tickets for logged customer (e-commerce).
     */
    public function customerTickets(Request $request)
    {
        $tickets = Ticket::where('customer_id', Auth::guard('customer')->id())
            ->orderBy('created_at', 'desc')
            ->get();
        return response()->json($tickets);
    }

    /**
     * Create a ticket (customer side).
     */
    public function store(Request $request)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'category' => 'required|string',
            'priority' => 'required|in:low,medium,high',
            'description' => 'required|string',
        ]);

        $ticket = Ticket::create([
            'customer_id' => Auth::guard('customer')->id() ?? 1, // fallback default
            'subject' => $request->subject,
            'category' => $request->category,
            'status' => 'open',
            'priority' => $request->priority,
            'description' => $request->description,
        ]);

        event(new \Modules\HelpDesk\Events\TicketCreated($ticket));

        return response()->json($ticket, 201);
    }

    /**
     * Get ticket details and conversation logs.
     */
    public function show($id)
    {
        $ticket = Ticket::with(['customer', 'messages.user', 'messages.customer'])->findOrFail($id);
        return response()->json($ticket);
    }

    /**
     * Answer ticket (internal user side).
     */
    public function reply(Request $request, $id)
    {
        $request->validate([
            'message' => 'required|string',
        ]);

        $ticket = Ticket::findOrFail($id);

        $message = TicketMessage::create([
            'ticket_id' => $ticket->id,
            'user_id' => Auth::id(), // Logged internal user
            'message' => $request->message,
        ]);

        $ticket->update(['status' => 'answered']);

        return response()->json($message, 201);
    }

    /**
     * Answer ticket (customer side).
     */
    public function customerReply(Request $request, $id)
    {
        $request->validate([
            'message' => 'required|string',
        ]);

        $ticket = Ticket::findOrFail($id);

        $message = TicketMessage::create([
            'ticket_id' => $ticket->id,
            'customer_id' => Auth::guard('customer')->id() ?? 1,
            'message' => $request->message,
        ]);

        $ticket->update(['status' => 'open']);

        return response()->json($message, 201);
    }
}
