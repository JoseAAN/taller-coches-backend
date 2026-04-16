<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\Invoice;
use App\Models\Item;
use App\Models\ItemProduct;
use App\Models\ItemType;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class StripeCheckoutTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    // Test 1: La creación de una factura rechaza una sesión de pago de otro carrito o usuario
    public function test_invoice_creation_rejects_paid_session_from_another_cart_or_user(): void
    {
        [$user, $rawToken, $cart] = $this->createAuthenticatedCartScenario();

        Mockery::mock('alias:Stripe\Stripe')
            ->shouldReceive('setApiKey')
            ->once();

        $stripeSession = (object) [
            'payment_status' => 'paid',
            'metadata' => (object) [
                'cart_id' => (string) ($cart->id + 999),
                'user_id' => (string) $user->id,
                'cart_total_cents' => '2500',
            ],
        ];

        Mockery::mock('alias:Stripe\Checkout\Session')
            ->shouldReceive('retrieve')
            ->once()
            ->with('cs_test_wrong')
            ->andReturn($stripeSession);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $rawToken,
            'Accept' => 'application/json',
        ])->postJson('/api/v1/invoices', [
            'cart_id' => $cart->id,
            'stripe_session_id' => 'cs_test_wrong',
        ]);

        $response->assertStatus(403)
            ->assertJsonPath('message', 'La sesión de Stripe no coincide con el carrito o el usuario autenticado.');

        $this->assertDatabaseCount('invoices', 0);
    }

    // Test 2: La creación de una factura es idempotente (no se puede crear dos veces) para la misma sesión de Stripe
    public function test_invoice_creation_is_idempotent_for_same_stripe_session(): void
    {
        [$user, $rawToken, $cart] = $this->createAuthenticatedCartScenario();

        $invoice = Invoice::create([
            'total' => 25.00,
            'cart_id' => $cart->id,
            'user_id' => $user->id,
            'stripe_session_id' => 'cs_test_repeat',
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $rawToken,
            'Accept' => 'application/json',
        ])->postJson('/api/v1/invoices', [
            'cart_id' => $cart->id,
            'stripe_session_id' => 'cs_test_repeat',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.id', $invoice->id);

        $this->assertDatabaseCount('invoices', 1);
        $this->assertSame(5, Product::first()->stock);
    }

    // Función auxiliar para crear un carrito autenticado
    private function createAuthenticatedCartScenario(): array
    {
        $clientRole = Role::create(['name' => 'client']);
        ItemType::create(['id' => ItemType::PRODUCT, 'name' => 'PRODUCT']);
        ItemType::create(['id' => ItemType::SERVICE, 'name' => 'SERVICE']);

        $rawToken = 'token-checkout-user';
        $user = User::create([
            'name' => 'Checkout User',
            'email' => 'checkout@example.com',
            'password' => bcrypt('password'),
            'role_id' => $clientRole->id,
            'api_token' => hash('sha256', $rawToken),
        ]);

        $cart = Cart::create([
            'user_id' => $user->id,
            'price' => 25.00,
        ]);

        $product = Product::create([
            'name' => 'Champu premium',
            'description' => 'Producto de prueba',
            'price' => 25.00,
            'stock' => 5,
        ]);

        $item = Item::create([
            'cart_id' => $cart->id,
            'item_type_id' => ItemType::PRODUCT,
            'quantity' => 1,
            'price_at_time' => 25.00,
            'subtotal' => 25.00,
        ]);

        ItemProduct::create([
            'item_id' => $item->id,
            'product_id' => $product->id,
        ]);

        return [$user, $rawToken, $cart];
    }
}
