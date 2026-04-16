<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Cart;
use App\Models\Item;
use App\Models\ItemAppointment;
use App\Models\ItemProduct;
use App\Models\ItemType;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

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
        ]);

        return DB::transaction(function () use ($request) {
            $cartId = $request->cart_id;
            $cart = Cart::with('user.role')->findOrFail($cartId);

            if ($this->userCannotAccessCart($request, $cart)) {
                return response()->json(['message' => 'No tienes permisos para modificar este carrito.'], Response::HTTP_FORBIDDEN);
            }

            $typeId = $request->item_type_id;
            $targetId = ($typeId == ItemType::PRODUCT) ? $request->product_id : $request->appointment_id;
            $quantity = $request->quantity;

            if ($typeId == ItemType::PRODUCT) {
                $product = Product::findOrFail($targetId);
                
                $existingItem = Item::where('cart_id', $cartId)
                    ->where('item_type_id', ItemType::PRODUCT)
                    ->whereHas('itemProduct', function ($q) use ($targetId) {
                        $q->where('product_id', $targetId);
                    })->first();

                $existingQuantity = $existingItem ? $existingItem->quantity : 0;

                if (($existingQuantity + $quantity) > $product->stock) {
                    return response()->json(['message' => 'No hay suficiente stock para este producto.'], Response::HTTP_UNPROCESSABLE_ENTITY);
                }

                if ($existingItem) {
                    $existingItem->quantity += $quantity;
                    $existingItem->subtotal = $existingItem->quantity * $existingItem->price_at_time;
                    $existingItem->save();
                    
                    $this->updateCartTotal($cartId);
                    
                    return response()->json($existingItem->load(['itemProduct', 'itemAppointment']), 200);
                }
            }

            $price = $this->resolveItemPrice($request, $typeId, $targetId);

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
        $item = Item::with('cart.user.role')->findOrFail($id);

        $request->validate([
            'quantity' => 'required|numeric|min:1',
        ]);

        if ($this->userCannotAccessItem($request, $item)) {
            return response()->json(['message' => 'No tienes permisos para modificar este item.'], Response::HTTP_FORBIDDEN);
        }

        if ($item->item_type_id == ItemType::PRODUCT) {
            $product = $item->itemProduct->product;
            
            $existingQuantity = Item::where('cart_id', $item->cart_id)
                ->where('item_type_id', ItemType::PRODUCT)
                ->where('id', '!=', $item->id)
                ->whereHas('itemProduct', function ($q) use ($product) {
                    $q->where('product_id', $product->id);
                })->sum('quantity');

            if (($existingQuantity + $request->quantity) > $product->stock) {
                return response()->json(['message' => 'No hay suficiente stock para este producto.'], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
        }

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
        $request = request();
        $item = Item::with('cart.user.role')->findOrFail($id);

        if ($this->userCannotAccessItem($request, $item)) {
            return response()->json(['message' => 'No tienes permisos para modificar este item.'], Response::HTTP_FORBIDDEN);
        }

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

    private function userCannotAccessCart(Request $request, Cart $cart): bool
    {
        $user = $request->user();

        if (!$user) {
            return true;
        }

        if ($user->role?->name === 'admin') {
            return false;
        }

        return (int) $cart->user_id !== (int) $user->id;
    }

    private function userCannotAccessItem(Request $request, Item $item): bool
    {
        if (!$item->relationLoaded('cart')) {
            $item->load('cart.user.role');
        }

        return $this->userCannotAccessCart($request, $item->cart);
    }

    private function resolveItemPrice(Request $request, int $typeId, int $targetId): float
    {
        if ($typeId === ItemType::PRODUCT) {
            return (float) Product::findOrFail($targetId)->price;
        }

        if ($typeId === ItemType::SERVICE) {
            $appointment = Appointment::with('vehicle')->findOrFail($targetId);

            if ($this->userCannotAccessAppointment($request, $appointment)) {
                abort(Response::HTTP_FORBIDDEN, 'No tienes permisos para anadir esta cita al carrito.');
            }

            return (float) $appointment->final_price;
        }

        abort(Response::HTTP_UNPROCESSABLE_ENTITY, 'Tipo de item no soportado.');
    }

    private function userCannotAccessAppointment(Request $request, Appointment $appointment): bool
    {
        $user = $request->user();

        if (!$user) {
            return true;
        }

        if (!$appointment->relationLoaded('vehicle')) {
            $appointment->load('vehicle');
        }

        if ($user->role?->name === 'admin') {
            return false;
        }

        return (int) $appointment->vehicle?->user_id !== (int) $user->id;
    }
}
