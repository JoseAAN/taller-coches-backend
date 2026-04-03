<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "Product",
    required: ["name", "price", "stock"],
    title: "Producto",
    description: "Modelo de producto"
)]
class Product extends Model
{
    use HasFactory;

    #[OA\Property(format: "int64", description: "ID del producto", example: 1)]
    private $id;

    #[OA\Property(description: "Nombre del producto", example: "Aceite de Motor")]
    private $name;

    #[OA\Property(description: "Descripción del producto", example: "Aceite sintético 5W-30")]
    private $description;

    #[OA\Property(format: "float", description: "Precio del producto", example: 45.99)]
    private $price;

    #[OA\Property(format: "int64", description: "Stock disponible", example: 100)]
    private $stock;

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

    public function images()
    {
        return $this->belongsToMany(Image::class, 'product_images')->withTimestamps();
    }



}
