<?php

namespace Modules\Production\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Products\Models\Product;

class Bom extends Model
{
    use SoftDeletes;

    protected $table = 'boms';

    protected $fillable = ['product_id', 'name', 'quantity', 'production_cost'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function items()
    {
        return $this->hasMany(BomItem::class, 'bom_id');
    }
}
