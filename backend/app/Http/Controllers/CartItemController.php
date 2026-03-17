<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Item;
use App\Models\ItemType;
use App\Models\ItemProduct;
use App\Models\ItemAppointment;
use App\Models\Product;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CartItemController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'cart_id' => 'required|exists:carts,id',
            'item_type_id' => 'required|exists:item_types,id',
            'product_id' => 'required_if:item_type_id,1|exists:products,id',
            'appointment_id' => 'required_if:item_type_id,2|exists:appointments,id',
            'quantity' => 'required|numeric|min:1',
            'price_at_time' => 'required|numeric',
        ]);

        return DB::transaction(function () use ($request) {
            $cartId = $request->cart_id;
            $typeId = $request->item_type_id;
            $targetId = ($typeId == ItemType::PRODUCT) ? $request->product_id : $request->appointment_id;
            $quantity = $request->quantity;
            $price = $request->price_at_time;

            // 1. Crear el Item general
            $item = Item::create([
                'cart_id' => $cartId,
                'item_type_id' => $typeId,
                'quantity' => $quantity,
                'price_at_time' => $price,
                'subtotal' => $quantity * $price,
            ]);

            // 2. Crear la relación específica
            if ($typeId == ItemType::PRODUCT) {
                ItemProduct::create([
                    'item_id' => $item->id,
                    'product_id' => $targetId,
                ]);
            } elseif ($typeId == ItemType::SERVICE) {
                ItemAppointment::create([
                    'item_id' => $item->id,
                    'appointment_id' => $targetId,
                ]);
            }

            // 3. Recalcular total del carrito
            $this->updateCartTotal($cartId);

            return response()->json($item->load(['itemProduct', 'itemAppointment']), 201);
        });
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $item = Item::findOrFail($id);

        $request->validate([
            'quantity' => 'required|numeric|min:1',
        ]);

        $item->quantity = $request->quantity;
        $item->subtotal = $item->quantity * $item->price_at_time;
        $item->save();

        $this->updateCartTotal($item->cart_id);

        return response()->json($item);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $item = Item::findOrFail($id);
        $cartId = $item->cart_id;
        $item->delete();

        $this->updateCartTotal($cartId);

        return response()->json(['message' => 'Item eliminado del carrito'], 200);
    }

    private function updateCartTotal($cartId)
    {
        $cart = Cart::find($cartId);
        if ($cart) {
            $cart->price = $cart->items()->sum('subtotal');
            $cart->save();
        }
    }
}
