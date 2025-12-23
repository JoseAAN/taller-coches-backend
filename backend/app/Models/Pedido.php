<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
    protected $guarded = [];

    // Relación: Un Pedido tiene UNA Factura
    // Relación: Un Pedido tiene UNA Factura de Producto
    public function factura()
    {
        return $this->hasOne(FacturaProducto::class);
    }

    public function user() {
    return $this->belongsTo(User::class);
    }
}   