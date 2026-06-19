<?php

namespace Modules\Stock\Models;

use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    protected $fillable = ['product_id', 'type', 'quantity', 'reference_type', 'reference_id'];
}
