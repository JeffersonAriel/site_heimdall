<?php

namespace Modules\Financial\Models;

use Illuminate\Database\Eloquent\Model;

class AccountsReceivable extends Model
{
    protected $fillable = ['customer_id', 'order_id', 'amount', 'due_date', 'status'];

    protected $casts = [
        'due_date' => 'date',
    ];

    public function customer()
    {
        return $this->belongsTo(\App\Models\Customer::class);
    }

    public function order()
    {
        return $this->belongsTo(\Modules\Orders\Models\Order::class);
    }
}
