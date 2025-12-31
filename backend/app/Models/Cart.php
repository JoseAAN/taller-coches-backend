<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Product;
use App\Models\User; 
class Cart extends Model
{
    use HasFactory;
    protected $table = 'carts';

    protected $fillable = [
        'id',
        'user_id',
        'price',
    ];

	public function products()
	{
		return $this->belongsToMany(Product::class, 'carts_products')
            ->using(CartProduct::class)
            ->withPivot('quantity', 'priceInTime', 'totalPerProduct')
            ->withTimestamps();
	}

    	public function user()
	{
		return $this->belongsTo(User::class);
	}
}
