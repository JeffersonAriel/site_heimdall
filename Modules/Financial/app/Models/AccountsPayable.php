<?php

namespace Modules\Financial\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AccountsPayable extends Model
{
    use SoftDeletes;

    protected $table = 'accounts_payable';

    protected $fillable = ['supplier_id', 'description', 'amount', 'due_date', 'status'];

    protected $casts = [
        'due_date' => 'date',
    ];
}
