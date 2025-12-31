<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'name',
        'price',
        'description',
        'stock',
    ];

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'products_categories');
    }

    public function carts()
    {
        return $this->belongsToMany(Cart::class, 'carts_products')
            ->using(CartProduct::class)
            ->withPivot('quantity', 'priceInTime', 'totalPerProduct')
            ->withTimestamps();
    }


}
