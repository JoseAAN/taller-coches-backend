<?php

namespace App\Http\Controllers;

use App\Models\CartInvoice;
use Illuminate\Http\Request;
use App\Http\Resources\CartInvoiceResource;
use App\Http\Resources\CartInvoiceCollection;

class CartInvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $carts = CartInvoice::all();
        return new CartInvoiceCollection($carts);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(CartInvoice $cartinvoice)
    {

        //Buscamos una factura por el ID interno de la factura
         return new CartInvoiceResource($cartinvoice);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
