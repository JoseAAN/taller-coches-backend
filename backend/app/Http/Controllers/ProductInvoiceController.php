<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProductInvoiceCollection;
use App\Http\Resources\ProductInvoiceResource;
use App\Models\ProductInvoice;
use Illuminate\Http\Request;

class ProductInvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = ProductInvoice::query();

        // Buscador por número de factora
        if ($request->has('search')) {
            $searchTerm = '%' . $request->search . '%';
            $query->where('invoice_number', 'like', $searchTerm);
        }

        $productInvoices = $query->paginate(10);

        return new ProductInvoiceCollection($productInvoices);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $data = $request->validate([
            'invoice_number' => 'required|string|unique:product_invoices,invoice_number',
            'total' => 'required|numeric',
            'cart_id' => 'required|exists:carts,id',
        ]);
        $productInvoice = ProductInvoice::create($data);
        return new ProductInvoiceResource($productInvoice);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $productInvoice = ProductInvoice::with('cart')->find($id);
        if (!$productInvoice) {
            return response()->json(['message' => 'Factura de producto no encontrada'], 404);
        }
        return new ProductInvoiceResource($productInvoice);
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
            'invoice_number' => 'sometimes|string|unique:product_invoices,invoice_number,' . $id,
            'total' => 'sometimes|numeric',
            'cart_id' => 'sometimes|exists:carts,id',
        ]);
        $productInvoice = ProductInvoice::find($id);
        if (!$productInvoice) {
            return response()->json(['message' => 'Factura de producto no encontrada'], 404);
        }

        $productInvoice->update($data);
        return new ProductInvoiceResource($productInvoice);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id, Request $request)
    {
        if ($request->user()->role->name !== 'admin') {
            return response()->json(['message' => 'No tienes permiso de administrador'], 403);
        }

        $productInvoice = ProductInvoice::find($id);
        if (!$productInvoice) {
            return response()->json(['message' => 'Factura de producto no encontrada'], 404);
        }

        $productInvoice->delete();
        return response()->json(['message' => 'Factura de producto eliminada correctamente']);
    }
}
