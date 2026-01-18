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
        
    }

    /**
     * Display the specified resource.
     */
    public function show(Cart $cart)
    {
        return new CartResource($cart);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        if ($request->user()->role->name !== 'admin') {
            return response()->json(['message' => 'No tienes permiso de administrador'], 403);
        }
    }
    
    /**
     * Remove the specified resource from storage.
    */
    public function destroy(Request $request, string $id)
    {
        if ($request->user()->role->name !== 'admin') {
            return response()->json(['message' => 'No tienes permiso de administrador'], 403);
        }
        //
    }
}
