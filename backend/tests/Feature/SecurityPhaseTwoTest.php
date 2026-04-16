<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\Item;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SecurityPhaseTwoTest extends TestCase
{
    use RefreshDatabase;

    // Test 1: Un cliente no puede acceder al carrito de otro usuario
    public function test_client_cannot_access_another_users_cart(): void
    {
        $owner = $this->createUserWithRole('client', 'owner@test.com', 'owner-token');
        $intruder = $this->createUserWithRole('client', 'intruder@test.com', 'intruder-token');

        $cart = Cart::create([
            'user_id' => $owner->id,
            'price' => 99.99,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer intruder-token',
        ])->getJson("/api/v1/carts/{$cart->id}");

        $response->assertStatus(403)
            ->assertJsonPath('message', 'No tienes permisos para acceder a este carrito.');
    }

    // Test 2: Un cliente no puede eliminar un item del carrito de otro usuario
    public function test_client_cannot_delete_another_users_cart_item(): void
    {
        $owner = $this->createUserWithRole('client', 'cart-owner@test.com', 'cart-owner-token');
        $intruder = $this->createUserWithRole('client', 'cart-intruder@test.com', 'cart-intruder-token');

        $this->seedItemTypes();

        $cart = Cart::create([
            'user_id' => $owner->id,
            'price' => 20.00,
        ]);

        $item = Item::create([
            'cart_id' => $cart->id,
            'item_type_id' => 1,
            'quantity' => 1,
            'price_at_time' => 20.00,
            'subtotal' => 20.00,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer cart-intruder-token',
        ])->deleteJson("/api/v1/cart-items/{$item->id}");

        $response->assertStatus(403)
            ->assertJsonPath('message', 'No tienes permisos para modificar este item.');

        $this->assertDatabaseHas('items', ['id' => $item->id]);
    }

    // Test 3: La navegación de administración requiere autenticación
    public function test_admin_navigation_requires_authentication(): void
    {
        $response = $this->getJson('/api/v1/admin-navigation');

        $response->assertStatus(401)
            ->assertJsonPath('message', 'Token no proporcionado.');
    }

    // Función auxiliar para crear un usuario con un rol específico y un token
    private function createUserWithRole(string $roleName, string $email, string $token): User
    {
        $role = Role::firstOrCreate(['name' => $roleName]);

        return User::create([
            'name' => ucfirst($roleName) . ' User',
            'email' => $email,
            'password' => bcrypt('password'),
            'role_id' => $role->id,
            'api_token' => hash('sha256', $token),
        ]);
    }

    // Función auxiliar para sembrar tipos de items
    private function seedItemTypes(): void
    {
        DB::table('item_types')->insertOrIgnore([
            ['id' => 1, 'name' => 'PRODUCT', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'name' => 'SERVICE', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
