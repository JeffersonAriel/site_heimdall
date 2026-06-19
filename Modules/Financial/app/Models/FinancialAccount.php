<?php

namespace Modules\Financial\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FinancialAccount extends Model
{
    use SoftDeletes;

    protected $table = 'financial_accounts';

    protected $fillable = ['name', 'type', 'balance'];

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'financial_account_id');
    }
}
