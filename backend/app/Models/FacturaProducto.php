<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FacturaProducto extends Model
{
    protected $table = 'facturas_productos';
    protected $guarded = [];

    // Relación con el Pedido (Origen)
    public function pedido()
    {
        return $this->belongsTo(Pedido::class);
    }

    // Relación con el Usuario
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
