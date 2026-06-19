<?php

namespace Modules\CRM\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PipelineStage extends Model
{
    use SoftDeletes;

    protected $table = 'pipeline_stages';

    protected $fillable = ['pipeline_id', 'name', 'order_position', 'color'];

    public function pipeline()
    {
        return $this->belongsTo(Pipeline::class);
    }

    public function leads()
    {
        return $this->hasMany(Lead::class, 'pipeline_stage_id');
    }

    public function deals()
    {
        return $this->hasMany(Deal::class, 'pipeline_stage_id');
    }
}
