<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    protected $guarded = [];

    // Relacion con la factura de servicios
    public function invoice()
    {
        return $this->hasOne(ServiceInvoice::class);
    }

    // Relacion con el usuario
    public function user() {
        return $this->belongsTo(User::class);
    }
}
