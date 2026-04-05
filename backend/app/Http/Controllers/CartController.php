<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Resources\CartCollection;
use App\Http\Resources\CartResource;
use App\Models\Cart;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CartController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $carts = Cart::with(
            'items.itemProduct.product.images',
            'items.itemAppointment.appointment.service.images',
            'items.type'
        )->get();
        return new CartCollection($carts);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //Añadir verificación de admin
        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'price' => 'required|numeric',
        ]);
        $cart = Cart::create($data);
        return new CartResource($cart);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Cart $cart)
    {
        if ($this->userCannotAccessCart($request, $cart)) {
            return response()->json(['message' => 'No tienes permisos para acceder a este carrito.'], Response::HTTP_FORBIDDEN);
        }

        return new CartResource($cart->load(
            'items.itemProduct.product.images',
            'items.itemAppointment.appointment.service.images',
            'items.type'
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {

        $data = $request->validate([
            'user_id' => 'sometimes|exists:users,id',
            'price' => 'sometimes|numeric',
        ]);

        $cart = Cart::find($id);

        if (!$cart) {
            return response()->json(['message' => 'Carrito no encontrado'], 404);
        }

        $cart->update($data);
        return new CartResource($cart);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, int $id)
    {
        $cart = Cart::find($id);

        if (!$cart) {
            return response()->json(['message' => 'Carrito no encontrado'], 404);
        }

        $cart->delete();
        return response()->json(['message' => 'Carrito eliminado correctamente'], 200);
        //
    }

    public function getCartByUserId(Request $request)
    {

        $userId = $request->user()->id;

        // Find a cart for this user that hasn't been invoiced yet
        $cart = Cart::where('user_id', $userId)
            ->doesntHave('invoice')
            ->first();

        if (!$cart) {
            $cart = Cart::create([
                'user_id' => $userId,
                'price' => 0
            ]);
        }

        $cart->load(
            'items.itemProduct.product.images',
            'items.itemAppointment.appointment.service.images',
            'items.type'
        );
        if (!$cart) {
            return response()->json(['message' => 'Carrito no encontrado para el usuario'], 404);
        }

        return new CartResource($cart);
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
}
