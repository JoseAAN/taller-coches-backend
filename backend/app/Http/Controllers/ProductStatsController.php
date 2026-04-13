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
}
