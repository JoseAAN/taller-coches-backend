<?php

namespace App\Http\Controllers;

use App\interfaces\Sorter;
use Illuminate\Http\Request;
use App\Models\ProductInvoice;
use App\Models\Cart;
use App\Http\Resources\ProductInvoiceResource;
use App\Http\Resources\ProductInvoiceCollection;
use App\Interfaces\CheckInvoiceFormat;

class ProductInvoiceController extends Controller implements Sorter, CheckInvoiceFormat
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

        // TODO: Sorts con nomrbes personalizados
        //Esto tengo que darle una vuelta para que allowedSorts tenga nombres personalizados y no los que vienen de la BD
        $this->sort(
            $query,
            $request,
            ['created_at', 'invoice_number', 'total']
        );

        return new ProductInvoiceCollection($query->paginate(10));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $data = $request->validate([
            'total' => 'required|numeric',
            'cart_id' => 'required|exists:carts,id',
        ]);

        
        // Ensure invoice_number is generated
        $data['invoice_number'] = ProductInvoice::generateInvoiceNumber();
        $productInvoice = ProductInvoice::create($data);
        $formatCheck = $this->validateInvoiceFormat($productInvoice->invoice_number);
        if (!$formatCheck['valid']) {
            return response()->json([
                'message' => $formatCheck['message']
            ], 403);
        }

        // Reduce stock from products
        $cart = Cart::with('products')->find($data['cart_id']);
        if ($cart) {
            foreach ($cart->products as $product) {
                // Ensure stock doesn't go below 0
                $purchasedQty = $product->pivot->quantity;
                $product->stock = max(0, $product->stock - $purchasedQty);
                $product->save();
            }
        }

        return new ProductInvoiceResource($productInvoice);
    }

    /**
     * Get the invoice by cart ID for the authenticated user.
     */
    public function getByCart(Request $request, $cartId)
    {
        $userId = $request->user()->id;
        
        $invoice = ProductInvoice::whereHas('cart', function($query) use ($userId, $cartId) {
            $query->where('user_id', $userId)->where('id', $cartId);
        })->first();

        if (!$invoice) {
            return response()->json(['message' => 'No se encontraron facturas para este carrito'], 404);
        }

        return new ProductInvoiceResource($invoice);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $productInvoice = ProductInvoice::with('cart')->find($id);
        if (!$productInvoice) {
            return response()->json(['message' => 'Factura de producto no encontrada'], 403);
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

        $data = $request->validate ([
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

    public function sort($query, Request $request, array $allowedSorts, string $defaultSort = 'created_at', string $defaultOrder = 'desc'){
        $sortBy = $request->get('sort_by', $defaultSort);
        $sortOrder = $request->get('sort_order', $defaultOrder);

        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = $defaultSort;
        }

        return $query->orderBy($sortBy, $sortOrder);
    }

    public function validateInvoiceFormat(string $invoiceNumber){
        //Patron: PINV-XXXX
        $pattern = '/^PINV-\d{5}$/';

        if (!preg_match($pattern, $invoiceNumber)) {
            return [
                'valid' => false,
                'message' => 'El número de factura debe tener el formato PINV-XXXXX'
            ];
        }

        return [
            'valid' => true,
            'message' => ''
        ];
    }

}
