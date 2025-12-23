<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FacturaServicio extends Model
{
    protected $table = 'facturas_servicios';
    protected $guarded = [];

    // Relación con la Cita (Origen)
    public function cita()
    {
        return $this->belongsTo(Cita::class);
    }

    // Relación con el Usuario
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
