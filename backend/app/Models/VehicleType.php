<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "VehicleType",
    title: "Tipo de Vehículo",
    description: "Clasificación de vehículos"
)]
class VehicleType extends Model
{

    protected $fillable = ['name'];
    #[OA\Property(format: "int64", description: "ID del tipo", example: 1)]
    private $id;

    #[OA\Property(description: "Nombre del tipo", example: "Turismo")]
    private $name;

    public function vehicles(): HasMany
    {
        return $this->hasMany(Vehicle::class);
    }
}
