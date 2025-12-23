<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $guarded = [];

    // Relacion con la factura de productos
    public function invoice()
    {
        return $this->hasOne(ProductInvoice::class);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }
}
