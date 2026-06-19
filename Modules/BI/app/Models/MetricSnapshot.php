<?php

namespace Modules\BI\Models;

use Illuminate\Database\Eloquent\Model;

class MetricSnapshot extends Model
{
    protected $fillable = ['metric_key', 'value', 'calculated_at', 'metadata'];

    protected $casts = [
        'value' => 'decimal:4',
        'calculated_at' => 'datetime',
        'metadata' => 'array',
    ];
}
