<?php

namespace Modules\CRM\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pipeline extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'description'];

    public function stages()
    {
        return $this->hasMany(PipelineStage::class)->orderBy('order_position');
    }
}
