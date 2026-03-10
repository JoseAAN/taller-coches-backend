<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Resources\CartCollection;
use App\Http\Resources\CartResource;
use App\Models\Cart;
use Illuminate\Http\Request;

class CartController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $carts = Cart::with('products')->get();
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
    public function show(Cart $cart)
    {
        return new CartResource($cart->load('products'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {

        if ($request->user()->role->name !== 'admin') {
            return response()->json(['message' => 'No tienes permiso de administrador'], 403);
        }

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
        if ($request->user()->role->name !== 'admin') {
            return response()->json(['message' => 'No tienes permiso de administrador'], 403);
        }
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
        $cart = Cart::firstOrCreate(
            ['user_id' => $userId],
            ['price' => 0]
        );
        
        $cart->load('products');

        if (!$cart) {
            return response()->json(['message' => 'Carrito no encontrado para el usuario'], 404);
        }

        return new CartResource($cart);
    }
}
