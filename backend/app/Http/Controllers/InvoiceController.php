<?php

namespace App\Http\Controllers;

use App\interfaces\Sorter;
use Illuminate\Http\Request;
use App\Models\Invoice;
use App\Models\Cart;
use App\Http\Resources\InvoiceResource;
use App\Http\Resources\InvoiceCollection;
use App\Interfaces\CheckInvoiceFormat;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller implements Sorter, CheckInvoiceFormat
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Invoice::with('user');

        // Buscador por número de factura o nombre de usuario
        if ($request->has('search')) {
            $searchTerm = '%' . $request->search . '%';
            $query->where(function($q) use ($searchTerm) {
                $q->where('invoice_number', 'like', $searchTerm)
                  ->orWhereHas('user', function($uQuery) use ($searchTerm) {
                      $uQuery->where('name', 'like', $searchTerm);
                  });
            });
        }

        $this->sort(
            $query,
            $request,
            ['created_at', 'invoice_number', 'total']
        );

        return new InvoiceCollection($query->paginate(10));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'total' => 'required|numeric',
            'cart_id' => 'nullable|exists:carts,id',
            'appointment_id' => 'nullable|exists:appointments,id',
        ]);

        $data['user_id'] = $request->user()->id;

        return DB::transaction(function () use ($data) {
            $invoice = Invoice::create($data);
            
            $formatCheck = $this->validateInvoiceFormat($invoice->invoice_number);
            if (!$formatCheck['valid']) {
                throw new \Exception($formatCheck['message']);
            }

           
            if (isset($data['cart_id'])) {
                $cart = Cart::with('items.itemProduct.product')->find($data['cart_id']);
                if ($cart) {
                    foreach ($cart->items as $item) {
                        if ($item->item_type_id == \App\Models\ItemType::PRODUCT && $item->itemProduct) {
                            $product = $item->itemProduct->product;
                            if ($product) {
                                $product->stock = max(0, $product->stock - $item->quantity);
                                $product->save();
                            }
                        }
                    }
                }
            }

            return new InvoiceResource($invoice);
        });
    }

    /**
     * Get the invoice by cart ID for the authenticated user.
     */
    public function getByCart(Request $request, $cartId)
    {
        $userId = $request->user()->id;
        
        $invoice = Invoice::where('user_id', $userId)
            ->where('cart_id', $cartId)
            ->first();

        if (!$invoice) {
            return response()->json(['message' => 'No se encontraron facturas para este carrito'], 404);
        }

        return new InvoiceResource($invoice);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $invoice = Invoice::with(['cart.items.itemProduct.product', 'cart.items.type', 'cart.items.itemAppointment.appointment', 'user'])->find($id);
        if (!$invoice) {
            return response()->json(['message' => 'Factura no encontrada'], 404);
        }
        return new InvoiceResource($invoice);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {

        $data = $request->validate([
            'invoice_number' => 'sometimes|string|unique:invoices,invoice_number,' . $id,
            'total' => 'sometimes|numeric',
        ]);

        $invoice = Invoice::find($id);
        if (!$invoice) {
            return response()->json(['message' => 'Factura no encontrada'], 404);
        }

        $invoice->update($data);
        return new InvoiceResource($invoice);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id, Request $request)
    {

        $invoice = Invoice::find($id);
        if (!$invoice) {
            return response()->json(['message' => 'Factura no encontrada'], 404);
        }

        $invoice->delete();
        return response()->json(['message' => 'Factura eliminada correctamente']);
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
        //Patron: INV-XXXXX
        $pattern = '/^INV-\d{5}$/';

        if (!preg_match($pattern, $invoiceNumber)) {
            return [
                'valid' => false,
                'message' => 'El número de factura debe tener el formato INV-XXXXX'
            ];
        }

        return [
            'valid' => true,
            'message' => ''
        ];
    }
}
