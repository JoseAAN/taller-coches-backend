<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Product;
use App\Models\User; 
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "Cart",
    required: ["user_id", "price"],
    title: "Carrito",
    description: "Carrito de compras"
)]
class Cart extends Model
{
    use HasFactory;
    protected $table = 'carts';

    #[OA\Property(format: "int64", description: "ID del carrito", example: 1)]
    private $id;

    #[OA\Property(format: "int64", description: "ID del usuario propietario", example: 1)]
    private $user_id;

    #[OA\Property(format: "float", description: "Precio total del carrito", example: 150.00)]
    private $price;

    protected $fillable = [
        'id',
        'user_id',
        'price',
    ];

    public function items()
    {
        return $this->hasMany(Item::class);
    }

    	public function user()
	{
		return $this->belongsTo(User::class);
	}

    public function invoice()
    {
        return $this->hasOne(Invoice::class, 'cart_id');
    }
}
