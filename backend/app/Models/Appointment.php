<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    protected $fillable = [
        'vehicle_id',
        'service_id',
        'appointment_date',
        'final_price'
    ];

    protected $casts = [
        'appointment_date' => 'datetime',
    ];

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
