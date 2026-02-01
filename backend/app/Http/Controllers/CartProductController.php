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
        $data = $cartProductRequest->validated();
        $data['totalPerProduct'] = $this->calculateTotalPerProduct($data['quantity'], $data['priceInTime']);
        $cartProduct = CartProduct::create($data);
        return response()->json($cartProduct, 201);
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
    public function update(Request $request, string $id, CartProductRequest $cartProductRequest)
    {
        //
        if ($request->user()->role->name !== 'admin') {
            return response()->json(['message' => 'No tienes permiso de administrador'], 403);
        }
        $cartProduct = CartProduct::findOrFail($id);
        $data = $cartProductRequest->validated();
        $data['totalPerProduct'] = $this->calculateTotalPerProduct($data['quantity'], $data['priceInTime']);
        $cartProduct->update($data);
        return response()->json($cartProduct);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        if ($request->user()->role->name !== 'admin') {
            return response()->json(['message' => 'No tienes permiso de administrador'], 403);
        }
        $cartProduct = CartProduct::findOrFail($id);
        $cartProduct->delete();
        return response()->json(['message' => 'Producto eliminado del carrito'], 204);
    }

    private function calculateTotalPerProduct($quantity, $priceInTime)
    {
        return $quantity * $priceInTime;
    }

    public function sort($query, Request $request, array $allowedSorts, string $defaultSort = 'created_at', string $defaultOrder = 'desc'){
        $sortBy = $request->get('sort_by', $defaultSort);
        $sortOrder = $request->get('sort_order', $defaultOrder);

        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = $defaultSort;
        }

        return $query->orderBy($sortBy, $sortOrder);
    }
}
