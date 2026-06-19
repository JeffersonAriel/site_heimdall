<?php

namespace Modules\Fiscal\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Orders\Models\Order;

class Invoice extends Model
{
    use SoftDeletes;

    protected $table = 'invoices';

    protected $fillable = ['order_id', 'invoice_number', 'key', 'status', 'xml_path'];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
