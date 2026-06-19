<?php

namespace Modules\Stock\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockLocation extends Model
{
    use SoftDeletes;

    protected $table = 'stock_locations';

    protected $fillable = ['warehouse', 'aisle', 'shelf', 'position'];
}
