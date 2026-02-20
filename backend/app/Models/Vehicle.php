<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "Vehicle",
    required: ["license_plate", "vehicle_type_id", "user_id"],
    title: "Vehículo",
    description: "Vehículo de un usuario"
)]
class Vehicle extends Model
{
    #[OA\Property(format: "int64", description: "ID del vehículo", example: 1)]
    private $id;

    #[OA\Property(description: "Matrícula", example: "1234ABC")]
    private $license_plate;

    #[OA\Property(format: "int64", description: "ID del tipo de vehículo", example: 1)]
    private $vehicle_type_id;

    #[OA\Property(format: "int64", description: "ID del usuario propietario", example: 1)]
    private $user_id;

    use HasFactory;
    protected $table = 'vehicles';
    protected $fillable = [
        'license_plate',
        'vehicle_type_id',
        'user_id',
        'color',
        'model',
        'brand',
    ];

    public function vehicleType()
    {
        return $this->belongsTo(VehicleType::class);
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(VehicleType::class, 'vehicle_type_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
