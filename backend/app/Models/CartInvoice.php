<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "CartInvoice",
    title: "Factura de Carrito",
    description: "Factura generada a partir de un carrito"
)]
class CartInvoice extends Model
{
     #[OA\Property(format: "int64", description: "ID de la factura", example: 1)]
     private $id;

     protected $guarded = [];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
