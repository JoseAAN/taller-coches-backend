<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Cita extends Model
{
    protected $guarded = [];

    // Relación: Una Cita tiene UNA Factura
    public function factura()
    {
        return $this->morphOne(Factura::class, 'facturable');
    }

    public function user() {
    return $this->belongsTo(User::class);
    }
}