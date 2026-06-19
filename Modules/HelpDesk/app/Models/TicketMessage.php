<?php

namespace Modules\HelpDesk\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Customer;
use App\Models\User;

class TicketMessage extends Model
{
    protected $table = 'ticket_messages';

    protected $fillable = ['ticket_id', 'user_id', 'customer_id', 'message'];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
