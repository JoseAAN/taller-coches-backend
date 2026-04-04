<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RestockSubscription extends Model
{
    protected $fillable = [
        'product_id',
        'email',
        'is_notified'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
