<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    use HasFactory;

    protected $fillable = [
        'url',
        'is_primary',
    ];

    /**
     *Casteo de atributos.
     * Indica que 'is_primary' siempre debe tratarse como un booleano
     */
    protected $casts = [
        'is_primary' => 'boolean',
    ];

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_images')
                    ->withTimestamps();
    }

    public function services()
    {
        return $this->belongsToMany(Service::class, 'service_images')
                    ->withTimestamps();
    }
}
