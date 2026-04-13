<?php

namespace App\Http\Controllers;

use App\interfaces\Sorter;
use Illuminate\Http\Request;
use App\Models\Invoice;
use App\Models\Cart;
use App\Models\Product;
use App\Http\Resources\InvoiceResource;
use App\Http\Resources\InvoiceCollection;
use App\Interfaces\CheckInvoiceFormat;
use App\Models\ItemType;
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
            'cart_id' => 'required|exists:carts,id',
            'stripe_session_id' => 'required|string', //la sesion la usaremos para que no se pueda crear una factura sin pagar
        ]);

        $data['user_id'] = $request->user()->id;
        $cart = Cart::with('items.itemProduct.product')->find($data['cart_id']);

        if (!$cart || (int) $cart->user_id !== (int) $request->user()->id) {
            return response()->json(['message' => 'No tienes permisos para facturar este carrito'], 403);
        }

        $total = (float) $cart->items->sum('subtotal');
        if ($total <= 0) {
            return response()->json(['message' => 'No se puede generar una factura para un carrito vacio'], 422);
        }

        $existingInvoiceBySession = Invoice::where('stripe_session_id', $data['stripe_session_id'])->first();
        if ($existingInvoiceBySession) {
            return new InvoiceResource($existingInvoiceBySession);
        }

        $existingInvoiceByCart = Invoice::where('cart_id', $cart->id)->first();
        if ($existingInvoiceByCart) {
            return new InvoiceResource($existingInvoiceByCart);
        }

        try {
            \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));
            //verificamos que el id de la sesión sea correcto
            $session = \Stripe\Checkout\Session::retrieve($data['stripe_session_id']);

            //si no ha pagado correctamente lanzamos el error
            if ($session->payment_status !== 'paid') {
                return response()->json(['message' => 'El pago no ha sido completado en Stripe o fue rechazado.'], 402);
            }

            $sessionCartId = (string) ($session->metadata->cart_id ?? '');
            $sessionUserId = (string) ($session->metadata->user_id ?? '');
            $sessionTotal = (int) ($session->metadata->cart_total_cents ?? 0);
            $expectedTotal = (int) round($total * 100);

            if (
                $sessionCartId !== (string) $cart->id ||
                $sessionUserId !== (string) $request->user()->id ||
                $sessionTotal !== $expectedTotal
            ) {
                return response()->json(['message' => 'La sesión de Stripe no coincide con el carrito o el usuario autenticado.'], 403);
            }
        } catch (\Exception $e) {
            return response()->json(['message' => 'Firma de sesión inválida. Acceso ilegal detectado.'], 403);
        }

        return DB::transaction(function () use ($data, $request) {
            $cart = Cart::with('items.itemProduct.product')->find($data['cart_id']);

            if (!$cart || (int) $cart->user_id !== (int) $request->user()->id) {
                return response()->json(['message' => 'No tienes permisos para facturar este carrito'], 403);
            }

            $total = (float) $cart->items->sum('subtotal');
            if ($total <= 0) {
                return response()->json(['message' => 'No se puede generar una factura para un carrito vacio'], 422);
            }

            $data['total'] = $total;

            $existingInvoiceBySession = Invoice::where('stripe_session_id', $data['stripe_session_id'])->lockForUpdate()->first();
            if ($existingInvoiceBySession) {
                return new InvoiceResource($existingInvoiceBySession);
            }

            $existingInvoiceByCart = Invoice::where('cart_id', $cart->id)->lockForUpdate()->first();
            if ($existingInvoiceByCart) {
                return new InvoiceResource($existingInvoiceByCart);
            }

            // Validar stock suficiente al momento de facturar
            // Solo si todo sigue disponible se crea la factura y se descuenta inventario
            $stockRequirements = collect($cart->items)
                ->filter(function ($item) {
                    return $item->item_type_id == \App\Models\ItemType::PRODUCT
                        && $item->itemProduct
                        && $item->itemProduct->product_id;
                })
                ->groupBy(function ($item) {
                    return $item->itemProduct->product_id;
                })
                ->map(function ($items) {
                    return (int) $items->sum('quantity');
                });
            // La función lockforupdate() se usa para evitar que se modifique el carrito mientras se realiza la factura
            $products = collect();
            if ($stockRequirements->isNotEmpty()) {
                $products = Product::whereIn('id', $stockRequirements->keys())
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

                foreach ($stockRequirements as $productId => $requiredQuantity) {
                    $product = $products->get((int) $productId);

                    if (!$product || $product->stock < $requiredQuantity) {
                        return response()->json([
                            'message' => 'No hay stock suficiente para completar la compra.',
                        ], 409);
                    }
                }
            }

            $invoice = Invoice::create($data);

            $formatCheck = $this->validateInvoiceFormat($invoice->invoice_number);
            if (!$formatCheck['valid']) {
                throw new \Exception($formatCheck['message']);
            }

            foreach ($cart->items as $item) {
                if ($item->item_type_id == ItemType::PRODUCT && $item->itemProduct) {
                    $product = $products->get((int) $item->itemProduct->product_id);
                    if ($product) {
                        $product->stock -= $item->quantity;
                        $product->save();
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
    public function show(Request $request, string $id)
    {
        $invoice = Invoice::with(['cart.items.itemProduct.product', 'cart.items.type', 'cart.items.itemAppointment.appointment', 'user'])->find($id);
        if (!$invoice) {
            return response()->json(['message' => 'Factura no encontrada'], 404);
        }

        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'No autenticado'], 401);
        }

        if ($user->role?->name !== 'admin' && (int) $invoice->user_id !== (int) $user->id) {
            return response()->json(['message' => 'No tienes permisos para ver esta factura'], 403);
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

    /**
     * Crear una sesión de Stripe Checkout para el carrito
     */
    public function createStripeSession(Request $request)
    {
        //aqui crearemos la sesion de stripe que este necesita y los datos del carrito para enviarle la info y que se pueda ver
        $data = $request->validate([
            'cart_id' => 'required|exists:carts,id',
        ]);

        $cart = Cart::with(['items.itemProduct.product', 'items.itemAppointment.appointment.service'])->find($data['cart_id']);

        if (!$cart || (int) $cart->user_id !== (int) $request->user()->id) {
            return response()->json(['message' => 'No tienes permisos para este carrito'], 403);
        }

        $total = (float) $cart->items->sum('subtotal');
        if ($total <= 0) {
            return response()->json(['message' => 'El carrito está vacío'], 422);
        }

        \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));

        $lineItems = [];
        foreach ($cart->items as $item) {
            $name = 'Producto/Servicio';
            
            if ($item->item_type_id == ItemType::PRODUCT && $item->itemProduct && $item->itemProduct->product) {
                 $name = $item->itemProduct->product->name;
            } elseif ($item->item_type_id == ItemType::SERVICE && $item->itemAppointment && $item->itemAppointment->appointment && $item->itemAppointment->appointment->service) {
                 $name = $item->itemAppointment->appointment->service->name;
            }

            $lineItems[] = [
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => [
                        'name' => $name,
                    ],
                    'unit_amount' => (int)round($item->price_at_time * 100),
                ],
                'quantity' => $item->quantity,
            ];
        }

        $frontendUrl = env('FRONTEND_URL', 'http://localhost:5173');

        $session = \Stripe\Checkout\Session::create([
            'payment_method_types' => ['card'],
            'line_items' => $lineItems,
            'mode' => 'payment',
            'client_reference_id' => (string) $cart->id,
            'metadata' => [
                'cart_id' => (string) $cart->id,
                'user_id' => (string) $request->user()->id,
                'cart_total_cents' => (string) ((int) round($total * 100)),
            ],
            'success_url' => $frontendUrl . '/cart?stripe_success=true&cart_id=' . $cart->id . '&session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => $frontendUrl . '/cart?stripe_cancel=true',
        ]);

        return response()->json(['url' => $session->url]);
    }
}
