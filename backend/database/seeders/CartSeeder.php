<?php

namespace Database\Seeders;

use App\Models\Cart;
use App\Models\Product;
use App\Models\Item;
use App\Models\ItemType;
use App\Models\ItemProduct;
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
                $priceAtMoment = $product->price;

                // 1. Crear el Item general (Tipo PRODUCT)
                $item = Item::create([
                    'cart_id' => $cart->id,
                    'item_type_id' => ItemType::PRODUCT,
                    'quantity' => $quantity,
                    'price_at_time' => $priceAtMoment,
                    'subtotal' => $quantity * $priceAtMoment,
                ]);

                // 2. Crear la relación específica con el producto
                ItemProduct::create([
                    'item_id' => $item->id,
                    'product_id' => $product->id,
                ]);
            }

            // Actualizar el precio total del carrito
            $cart->price = $cart->items()->sum('subtotal');
            $cart->save();
        });
    }
}
