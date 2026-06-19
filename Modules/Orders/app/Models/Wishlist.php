<?php

namespace Modules\Orders\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Wishlist extends Model
{
    use SoftDeletes;

    protected $table = 'wishlists';

    protected $fillable = ['customer_id', 'product_id'];

    public function product()
    {
        return $this->belongsTo(\Modules\Products\Models\Product::class);
    }
}
