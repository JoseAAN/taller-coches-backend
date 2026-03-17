<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "Order",
    title: "Pedido",
    description: "Pedido realizado por un usuario"
)]
class Order extends Model
{
    #[OA\Property(format: "int64", description: "ID del pedido", example: 1)]
    private $id;

    protected $guarded = [];

    // Relacion con la factura unificada
    public function invoice()
    {
        return $this->hasOne(Invoice::class);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }
}
