<?php

namespace Modules\Stock\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockLot extends Model
{
    use SoftDeletes;

    protected $table = 'stock_lots';

    protected $fillable = ['product_id', 'lot_number', 'expiry_date', 'quantity'];

    protected $casts = [
        'expiry_date' => 'date',
    ];

    public function product()
    {
        return $this->belongsTo(\Modules\Products\Models\Product::class);
    }
}
