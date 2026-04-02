<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "Cita",
    title: "Cita del Taller",
    description: "Detalles de una cita para limpieza o reparación",
    properties: [
        new OA\Property(property: "id", type: "integer", example: 1),
        new OA\Property(property: "cliente_id", type: "integer", example: 5),
        new OA\Property(property: "fecha", type: "string", format: "date-time", example: "2026-05-15 10:30:00"),
        new OA\Property(property: "servicio", type: "string", example: "Lavado Premium"),
        new OA\Property(property: "estado", type: "string", enum: ["pendiente", "completada", "cancelada"])
    ]
)]
class Appointment extends Model
{
    protected $fillable = [
        'vehicle_id',
        'service_id',
        'appointment_date',
        'end_time',
        'final_price',
        'status',
    ];

    protected $casts = [
        'appointment_date' => 'datetime',
        'end_time' => 'datetime',
    ];

    protected $guarded = [];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    // Relacion con la factura unificada
    public function invoice()
    {
        return $this->hasOne(Invoice::class);
    }

    // Relacion con el usuario
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function service() // El tipo de servicio que se le hizo
{
    return $this->belongsTo(Service::class, 'service_id');
}
}
