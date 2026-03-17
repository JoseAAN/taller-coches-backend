<?php

namespace Database\Seeders;

use App\Models\Invoice;
use App\Models\Cart;
use App\Models\User;
use Illuminate\Database\Seeder;

class InvoiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear algunas facturas desde carritos para los usuarios existentes
        $users = User::all();
        
        foreach ($users as $user) {
            $cart = Cart::where('user_id', $user->id)->first();
            if ($cart) {
                \Log::info("Creando factura para usuario {$user->id} con carrito {$cart->id}");
                Invoice::factory()->create([
                    'user_id' => $user->id,
                    'cart_id' => $cart->id,
                    'total' => $cart->price ?? 100.00
                ]);
            }
        }
    }
}
