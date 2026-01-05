<?php

namespace Database\Seeders;

use App\Models\Cart;
use App\Models\Product;
use Illuminate\Database\Seeder;

class CartSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = Product::all();

        // Creamos 10 carritos
        Cart::factory(10)->create()->each(function ($cart) use ($products) {
            // A cada carrito le ponemos entre 1 y 5 productos aleatorios
            $randomProducts = $products->random(rand(1, 5));

            foreach ($randomProducts as $product) {
                $quantity = rand(1, 5);
                $priceAtMoment = $product->price; //Cogemos el precio actual del producto

                // Insertamos en la tabla intermedia cart_product
                $cart->products()->attach($product->id, [
                    'quantity' => $quantity,
                    'priceInTime' => $priceAtMoment,
                    'totalPerProduct' => $quantity * $priceAtMoment,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });
    }
}
