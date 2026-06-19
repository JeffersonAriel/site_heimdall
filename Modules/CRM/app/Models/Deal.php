<?php

namespace Modules\CRM\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Orders\Models\Order;

class Deal extends Model
{
    use SoftDeletes;

    protected $fillable = ['lead_id', 'order_id', 'title', 'value', 'status', 'pipeline_stage_id'];

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function stage()
    {
        return $this->belongsTo(PipelineStage::class, 'pipeline_stage_id');
    }
}
