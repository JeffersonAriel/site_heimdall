<?php

namespace Modules\Notifications\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Notification extends Model
{
    use SoftDeletes;

    protected $table = 'notifications';

    protected $fillable = ['user_id', 'title', 'message', 'type', 'read_at'];

    protected $casts = [
        'read_at' => 'datetime',
    ];
}
