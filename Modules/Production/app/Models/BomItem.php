<?php

namespace Modules\Production\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Products\Models\Product;

class BomItem extends Model
{
    protected $table = 'bom_items';

    protected $fillable = ['bom_id', 'product_id', 'quantity'];

    public function bom()
    {
        return $this->belongsTo(Bom::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
