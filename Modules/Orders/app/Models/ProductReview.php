<?php

namespace Modules\Orders\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductReview extends Model
{
    use SoftDeletes;

    protected $table = 'product_reviews';

    protected $fillable = ['customer_id', 'product_id', 'rating', 'comment', 'approved'];

    public function customer()
    {
        return $this->belongsTo(\App\Models\Customer::class);
    }

    public function product()
    {
        return $this->belongsTo(\Modules\Products\Models\Product::class);
    }
}
