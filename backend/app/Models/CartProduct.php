<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "CartProduct",
    required: ["cart_id", "product_id", "quantity"],
    title: "Producto en Carrito",
    description: "Relación entre carrito y producto"
)]
class CartProduct extends Pivot
{
    protected $table = 'carts_products';
    
    // Indica que el ID es auto-incremental si tu tabla pivot tiene 'id'
    public $incrementing = true;

    #[OA\Property(format: "int64", description: "ID del registro", example: 1)]
    private $id;

    #[OA\Property(format: "int64", description: "ID del carrito", example: 1)]
    private $cart_id;

    #[OA\Property(format: "int64", description: "ID del producto", example: 5)]
    private $product_id;

    #[OA\Property(format: "int64", description: "Cantidad", example: 2)]
    private $quantity;

    #[OA\Property(format: "float", description: "Precio unitario en el momento", example: 50.00)]
    private $priceInTime;

    #[OA\Property(format: "float", description: "Total por producto (cantidad * precio)", example: 100.00)]
    private $totalPerProduct;

    protected $fillable = [
        'cart_id',
        'product_id',
        'quantity',
        'priceInTime',
        'totalPerProduct',
    ];

    // Relación con el producto
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // Relación con el carrito
    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }
}
