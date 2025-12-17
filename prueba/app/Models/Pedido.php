<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
    protected $guarded = [];

    // Relación: Un Pedido tiene UNA Factura
    public function factura()
    {
        // (Modelo Hijo, Nombre de la relación polimórfica)
        return $this->morphOne(Factura::class, 'facturable');
    }

    public function user() {
    return $this->belongsTo(User::class);
    }
}   