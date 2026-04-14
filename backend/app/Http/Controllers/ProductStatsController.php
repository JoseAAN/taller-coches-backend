<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductStatsController extends Controller
{
    /**
     * Obtiene los productos más vendidos usando Eloquent ORM.
     * Calcula la suma de cantidades de items vendidos, restringido a carritos facturados.
     */
    public function getBestSellers(Request $request)
    {
        $limit = $request->validate([
            'limit' => 'nullable|integer|min:1|max:100'
        ])
        ['limit'] ?? 10;

        $bestSellers = Product::with(['images', 'categories'])
            ->withSum(['items as total_sold' => function ($query) {
                $query->whereHas('cart.invoice');
            }], 'quantity')
            ->having('total_sold', '>', 0) // descarto productos con 0 ventas completadas
            ->orderByDesc('total_sold')
            ->limit($limit)
            ->get();

        return response()->json($bestSellers);
    }

    /**
     * Obtiene productos relacionados en base a las categorías del producto especificado.
     */
    public function getRelatedProducts($id)
    {
        $product = Product::findOrFail($id);

        $categoryIds = $product->categories()->pluck('categories.id');

        $relatedProducts = Product::with(['images', 'categories'])
            ->whereHas('categories', function ($query) use ($categoryIds) {
                $query->whereIn('categories.id', $categoryIds);
            })
            ->where('id', '!=', $id)
            ->limit(10)
            ->get();

        return response()->json([
            'status' => true,
            'data' => $relatedProducts
        ]);
    }
}
