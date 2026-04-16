<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Tests Unitarios del modelo Product.
 * Validan la estructura del modelo (atributos fillable y relaciones).
 */
class ProductTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test 1: Verificar que el modelo tiene los campos fillable correctos.
     */
    public function test_product_has_fillable_attributes(): void
    {
        $product = new Product();
        $fillable = $product->getFillable();

        $this->assertContains('name', $fillable);
        $this->assertContains('price', $fillable);
        $this->assertContains('description', $fillable);
        $this->assertContains('stock', $fillable);
    }

    /**
     * Test 2: Verificar que existe la relación con categorías (belongsToMany).
     */
    public function test_product_has_categories_relationship(): void
    {
        $product = new Product();

        // Verificar que el método categories() existe y devuelve una relación BelongsToMany
        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\BelongsToMany::class,
            $product->categories()
        );
    }

    /**
     * Test 3: Verificar que existe la relación con items (belongsToMany).
     */
    public function test_product_has_items_relationship(): void
    {
        $product = new Product();

        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\BelongsToMany::class,
            $product->items()
        );
    }
}
