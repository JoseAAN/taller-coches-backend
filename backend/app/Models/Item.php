<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Item extends Model
{
    protected $fillable = [
        'cart_id',
        'item_type_id',
        'quantity',
        'price_at_time',
        'subtotal',
    ];

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(ItemType::class, 'item_type_id');
    }

    public function itemProduct(): HasOne
    {
        return $this->hasOne(ItemProduct::class);
    }

    public function itemAppointment(): HasOne
    {
        return $this->hasOne(ItemAppointment::class);
    }

    // Helper para obtener el objeto real (Producto o Cita)
    public function getTargetAttribute()
    {
        if ($this->item_type_id == ItemType::PRODUCT) {
            return $this->itemProduct->product;
        }
        return $this->itemAppointment->appointment;
    }
}
