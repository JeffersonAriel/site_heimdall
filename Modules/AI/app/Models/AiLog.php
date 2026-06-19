<?php

namespace Modules\AI\Models;

use Illuminate\Database\Eloquent\Model;

class AiLog extends Model
{
    protected $fillable = ['module', 'prompt', 'response', 'tokens_used'];
}
