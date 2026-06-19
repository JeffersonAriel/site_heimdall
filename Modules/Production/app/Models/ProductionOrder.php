<?php

namespace Modules\Production\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductionOrder extends Model
{
    use SoftDeletes;

    protected $table = 'production_orders';

    protected $fillable = ['bom_id', 'quantity', 'status', 'additional_cost', 'completed_at'];

    protected $casts = [
        'completed_at' => 'datetime',
    ];

    public function bom()
    {
        return $this->belongsTo(Bom::class);
    }
}
