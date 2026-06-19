<?php

namespace Modules\Orders\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Coupon extends Model
{
    use SoftDeletes;

    protected $table = 'coupons';

    protected $fillable = ['code', 'type', 'value', 'min_order_value', 'expires_at'];

    protected $casts = [
        'expires_at' => 'date',
    ];
}
