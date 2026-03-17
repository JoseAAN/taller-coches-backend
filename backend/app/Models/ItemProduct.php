<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemProduct extends Model
{
    protected $fillable = ['item_id', 'product_id'];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
