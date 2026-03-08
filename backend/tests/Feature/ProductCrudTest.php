<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

/**
 * Tests de Feature para el CRUD de Productos (SQL puro).
 * Validan los endpoints HTTP, autenticación, validación y respuestas.
 */
class ProductCrudTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Crear un usuario admin autenticado con token.
     */
    private function createAdminUser(): User
    {
        $role = Role::create(['name' => 'admin']);
        $user = User::create([
            'name' => 'Admin Test',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'role_id' => $role->id,
            'api_token' => 'admin-test-token-123',
        ]);
        return $user;
    }

    /**
     * Crear un usuario cliente autenticado con token.
     */
    private function createClientUser(): User
    {
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $role = Role::firstOrCreate(['name' => 'client']);
        $user = User::create([
            'name' => 'Client Test',
            'email' => 'client@test.com',
            'password' => bcrypt('password'),
            'role_id' => $role->id,
            'api_token' => 'client-test-token-456',
        ]);
        return $user;
    }

    /**
     * Insertar un producto de prueba directamente con SQL.
     */
    private function insertTestProduct(string $name = 'Producto Test', float $price = 25.00, int $stock = 10): int
    {
        $now = Carbon::now();
        DB::insert(
            "INSERT INTO products (name, description, price, stock, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?)",
            [$name, 'Descripción de prueba', $price, $stock, $now, $now]
        );
        return (int) DB::getPdo()->lastInsertId();
    }

    /**
     * Insertar una categoría de prueba.
     */
    private function insertTestCategory(string $name = 'Categoría Test'): int
    {
        $now = Carbon::now();
        DB::insert(
            "INSERT INTO categories (name, created_at, updated_at) VALUES (?, ?, ?)",
            [$name, $now, $now]
        );
        return (int) DB::getPdo()->lastInsertId();
    }

    // ========================================
    // Tests de INDEX (GET /api/v1/products)
    // ========================================

    /**
     * Test 1: Listar productos devuelve 200 y datos.
     */
    public function test_index_returns_products_list(): void
    {
        $this->insertTestProduct('Aceite Motor');
        $this->insertTestProduct('Filtro Aire');

        $response = $this->getJson('/api/v1/products');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'data',
                     'meta' => ['current_page', 'per_page', 'total', 'last_page']
                 ])
                 ->assertJsonPath('meta.total', 2);
    }

    /**
     * Test 2: Filtrar por búsqueda funciona.
     */
    public function test_index_filters_by_search(): void
    {
        $this->insertTestProduct('Aceite Motor');
        $this->insertTestProduct('Filtro Aire');

        $response = $this->getJson('/api/v1/products?search=Aceite');

        $response->assertStatus(200)
                 ->assertJsonPath('meta.total', 1);
    }

    /**
     * Test 3: Filtrar por categoría funciona.
     */
    public function test_index_filters_by_category(): void
    {
        $productId = $this->insertTestProduct('Aceite Motor');
        $categoryId = $this->insertTestCategory('Lubricantes');

        // Asociar producto a categoría
        $now = Carbon::now();
        DB::insert(
            "INSERT INTO products_categories (product_id, category_id, created_at, updated_at) VALUES (?, ?, ?, ?)",
            [$productId, $categoryId, $now, $now]
        );

        // Producto sin categoría
        $this->insertTestProduct('Filtro Aire');

        $response = $this->getJson("/api/v1/products?category_id={$categoryId}");

        $response->assertStatus(200)
                 ->assertJsonPath('meta.total', 1);
    }

    // ========================================
    // Tests de SHOW (GET /api/v1/products/{id})
    // ========================================

    /**
     * Test 4: Ver producto existente devuelve 200.
     */
    public function test_show_returns_product(): void
    {
        $productId = $this->insertTestProduct('Aceite Motor', 45.99, 20);

        $response = $this->getJson("/api/v1/products/{$productId}");

        $response->assertStatus(200)
                 ->assertJsonPath('data.name', 'Aceite Motor')
                 ->assertJsonPath('data.price', 45.99);
    }

    /**
     * Test 5: Ver producto inexistente devuelve 404.
     */
    public function test_show_returns_404_for_missing_product(): void
    {
        $response = $this->getJson('/api/v1/products/9999');

        $response->assertStatus(404)
                 ->assertJsonPath('message', 'Producto no encontrado');
    }

    // ========================================
    // Tests de STORE (POST /api/v1/products)
    // ========================================

    /**
     * Test 6: Crear producto como admin devuelve 200.
     */
    public function test_store_creates_product_as_admin(): void
    {
        $admin = $this->createAdminUser();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer admin-test-token-123',
        ])->postJson('/api/v1/products', [
            'name' => 'Nuevo Producto',
            'description' => 'Creado en test',
            'price' => 29.99,
            'stock' => 50,
        ]);

        $response->assertStatus(200)
                 ->assertJsonPath('data.name', 'Nuevo Producto');

        // Verificar que existe en la base de datos
        $products = DB::select("SELECT * FROM products WHERE name = ?", ['Nuevo Producto']);
        $this->assertCount(1, $products);
    }

    /**
     * Test 7: Crear producto sin token devuelve 401.
     */
    public function test_store_fails_without_auth(): void
    {
        $response = $this->postJson('/api/v1/products', [
            'name' => 'Producto Sin Auth',
            'price' => 10.00,
            'stock' => 5,
        ]);

        $response->assertStatus(401);
    }

    /**
     * Test 8: Crear producto con datos inválidos devuelve 422.
     */
    public function test_store_fails_with_invalid_data(): void
    {
        $admin = $this->createAdminUser();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer admin-test-token-123',
        ])->postJson('/api/v1/products', [
            // Falta 'name' (required), 'price' (required), 'stock' (required)
        ]);

        $response->assertStatus(422);
    }

    // ========================================
    // Tests de UPDATE (PUT /api/v1/products/{id})
    // ========================================

    /**
     * Test 9: Actualizar producto como admin devuelve 200.
     */
    public function test_update_modifies_product_as_admin(): void
    {
        $admin = $this->createAdminUser();
        $productId = $this->insertTestProduct('Producto Original', 20.00, 10);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer admin-test-token-123',
        ])->putJson("/api/v1/products/{$productId}", [
            'name' => 'Producto Actualizado',
            'price' => 35.50,
        ]);

        $response->assertStatus(200)
                 ->assertJsonPath('data.name', 'Producto Actualizado');

        // Verificar el cambio en la base de datos
        $updated = DB::select("SELECT * FROM products WHERE id = ?", [$productId]);
        $this->assertEquals('Producto Actualizado', $updated[0]->name);
        $this->assertEquals(35.50, $updated[0]->price);
    }

    /**
     * Test 10: Actualizar producto inexistente devuelve 404.
     */
    public function test_update_returns_404_for_missing_product(): void
    {
        $admin = $this->createAdminUser();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer admin-test-token-123',
        ])->putJson('/api/v1/products/9999', [
            'name' => 'No existe',
        ]);

        $response->assertStatus(404);
    }

    // ========================================
    // Tests de DESTROY (DELETE /api/v1/products/{id})
    // ========================================

    /**
     * Test 11: Eliminar producto como admin devuelve 200.
     */
    public function test_destroy_deletes_product_as_admin(): void
    {
        $admin = $this->createAdminUser();
        $productId = $this->insertTestProduct('Producto a Borrar');

        $response = $this->withHeaders([
            'Authorization' => 'Bearer admin-test-token-123',
        ])->deleteJson("/api/v1/products/{$productId}");

        $response->assertStatus(200)
                 ->assertJsonPath('message', 'Producto borrado correctamente');

        // Verificar que ya no existe
        $deleted = DB::select("SELECT * FROM products WHERE id = ?", [$productId]);
        $this->assertEmpty($deleted);
    }

    /**
     * Test 12: Eliminar como cliente (no admin) devuelve 403.
     */
    public function test_destroy_fails_without_admin_role(): void
    {
        $client = $this->createClientUser();
        $productId = $this->insertTestProduct('Producto Protegido');

        $response = $this->withHeaders([
            'Authorization' => 'Bearer client-test-token-456',
        ])->deleteJson("/api/v1/products/{$productId}");

        $response->assertStatus(403);
    }
}
