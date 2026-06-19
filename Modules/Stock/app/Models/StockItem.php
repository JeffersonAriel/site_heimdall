<?php

namespace Modules\Stock\Models;

use Illuminate\Database\Eloquent\Model;

class StockItem extends Model
{
    protected $fillable = ['product_id', 'quantity', 'reserved_quantity'];

    public function product()
    {
        return $this->belongsTo(\Modules\Products\Models\Product::class);
    }
}
