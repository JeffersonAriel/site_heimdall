<?php

namespace Modules\Stock\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Stock\Models\StockItem;

class StockLowAlert
{
    use Dispatchable, SerializesModels;

    public StockItem $stockItem;

    public function __construct(StockItem $stockItem)
    {
        $this->stockItem = $stockItem;
    }
}
