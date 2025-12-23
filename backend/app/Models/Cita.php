<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Cita extends Model
{
    protected $guarded = [];

    // Relación: Una Cita tiene UNA Factura
    // Relación: Una Cita tiene UNA Factura de Servicio
    public function factura()
    {
        return $this->hasOne(FacturaServicio::class);
    }

    public function user() {
    return $this->belongsTo(User::class);
    }
}