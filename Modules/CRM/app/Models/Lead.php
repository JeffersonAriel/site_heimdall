<?php

namespace Modules\CRM\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Customer;

class Lead extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'email', 'phone', 'source', 'status', 'pipeline_stage_id', 'customer_id'];

    public function stage()
    {
        return $this->belongsTo(PipelineStage::class, 'pipeline_stage_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function deals()
    {
        return $this->hasMany(Deal::class);
    }

    public function activities()
    {
        return $this->hasMany(Activity::class);
    }
}
