<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "Service",
    required: ["name", "price", "service_type_id"],
    title: "Servicio",
    description: "Modelo de servicio ofertado"
)]
class Service extends Model
{
    use HasFactory;

    #[OA\Property(format: "int64", description: "ID del servicio", example: 1)]
    private $id;

    #[OA\Property(description: "Nombre del servicio", example: "Cambio de Aceite")]
    private $name;

    #[OA\Property(format: "float", description: "Precio del servicio", example: 50.00)]
    private $price;

    #[OA\Property(format: "int64", description: "Duración media en minutos", example: 45)]
    private $average_duration;

    #[OA\Property(description: "Descripción del servicio", example: "Cambio de aceite y filtro")]
    private $description;

    #[OA\Property(format: "int64", description: "ID del tipo de servicio asociado", example: 2)]
    private $service_type_id;

    #[OA\Property(description: "Mostrar en página de inicio", example: true)]
    private $show_on_home;

    protected $fillable = [
        'name',
        'price',
        'average_duration',
        'description',
        'service_type_id',
        'show_on_home',
    ];

    public function serviceType()
    {
        return $this->belongsTo(ServiceType::class);
    }
}
