<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Factura de servicios
class ServiceInvoice extends Model
{
    protected $guarded = [];

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
