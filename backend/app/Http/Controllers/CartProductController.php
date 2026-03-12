<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\interfaces\Sorter;
use App\Models\CartProduct;
use Illuminate\Http\Request;
use App\Http\Requests\CartProductRequest;
use App\Http\Resources\CartProductCollection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CartProductController extends Controller implements Sorter
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = CartProduct::query();
        $this->sort(
            $query,
            $request,
            ['created_at', 'priceInTime', 'totalPerProduct', 'updated_at', 'cart_id']
        );
        $carProducts = CartProduct::all();
        return new CartProductCollection($query->paginate(10));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, CartProductRequest $cartProductRequest)
    {
        try {
            $data = $cartProductRequest->validated();
            
            $cartProduct = CartProduct::where('cart_id', $data['cart_id'])
                ->where('product_id', $data['product_id'])
                ->first();

            $product = \App\Models\Product::findOrFail($data['product_id']);
            $currentQuantity = $cartProduct ? $cartProduct->quantity : 0;
            $newTotalQuantity = $currentQuantity + $data['quantity'];

            if ($newTotalQuantity > $product->stock) {
                return response()->json([
                    'message' => "Stock insuficiente. Solo quedan {$product->stock} unidades disponibles.",
                    'available_stock' => $product->stock
                ], 400);
            }

            //actualizamos la cantidad y el totalperproduct
            if ($cartProduct) {
                $cartProduct->quantity = $newTotalQuantity;
                $cartProduct->totalPerProduct = $this->calculateTotalPerProduct(
                    $cartProduct->quantity,
                    $data['priceInTime']
                );
                $cartProduct->save();
            } else {
                $data['totalPerProduct'] = $this->calculateTotalPerProduct(
                    $data['quantity'],
                    $data['priceInTime']
                );
                $cartProduct = CartProduct::create($data);
            }
            
            // Recalcular total del carrito
            $cart = \App\Models\Cart::find($cartProduct->cart_id);
            if ($cart) {
                $cart->price = $cart->products->sum(function ($p) {
                    return $p->pivot->totalPerProduct;
                });
                $cart->save();
            }

            return response()->json($cartProduct, 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al añadir el producto al carrito', 'error' => $e->getMessage()], 500);
        }

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $cartProduct = CartProduct::findOrFail($id);
        return response()->json($cartProduct);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $cartProduct = CartProduct::findOrFail($id);

        $data = $request->validate([
            'quantity' => 'required|numeric|min:1',
            'priceInTime' => 'required|numeric',
        ]);

        $product = \App\Models\Product::findOrFail($cartProduct->product_id);

        if ($data['quantity'] > $product->stock) {
            return response()->json([
                'message' => "Stock insuficiente. Solo quedan {$product->stock} unidades disponibles.",
                'available_stock' => $product->stock
            ], 400);
        }

        $data['totalPerProduct'] = $this->calculateTotalPerProduct($data['quantity'], $data['priceInTime']);
        $cartProduct->update($data);

        // Recalcular total del carrito
        $cart = Cart::find($cartProduct->cart_id);
        if ($cart) {
            $cart->price = $cart->products->sum(function ($p) {
                return $p->pivot->totalPerProduct;
            });
            $cart->save();
        }

        return response()->json($cartProduct);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        $cartProduct = CartProduct::findOrFail($id);
        $cartId = $cartProduct->cart_id;
        $cartProduct->delete();

        // Recalcular total del carrito
        $cart = Cart::find($cartId);
        if ($cart) {
            $cart->price = $cart->products->sum(function ($p) {
                return $p->pivot->totalPerProduct;
            });
            $cart->save();
        }

        return response()->json(['message' => 'Producto eliminado del carrito'], 200);
    }

    private function calculateTotalPerProduct($quantity, $priceInTime)
    {
        return $quantity * $priceInTime;
    }

    public function sort($query, Request $request, array $allowedSorts, string $defaultSort = 'created_at', string $defaultOrder = 'desc')
    {
        $sortBy = $request->get('sort_by', $defaultSort);
        $sortOrder = $request->get('sort_order', $defaultOrder);

        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = $defaultSort;
        }

        return $query->orderBy($sortBy, $sortOrder);
    }
}
