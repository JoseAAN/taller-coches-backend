<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "ServiceType",
    required: ["name"],
    title: "Tipo de Servicio",
    description: "Categoría o tipo de servicio"
)]
class ServiceType extends Model
{
    use HasFactory;

    #[OA\Property(format: "int64", description: "ID del tipo de servicio", example: 1)]
    private $id;

    #[OA\Property(description: "Nombre del tipo de servicio", example: "Mecánica General")]
    private $name;

    protected $fillable = ['name'];

    public function services()
    {
        return $this->hasMany(Service::class);
    }
}
