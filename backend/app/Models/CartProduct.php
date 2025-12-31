<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class CartProduct extends Pivot
{
    protected $table = 'carts_products';
    
    // Indica que el ID es auto-incremental si tu tabla pivot tiene 'id'
    public $incrementing = true;

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
