<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Resources\ProductsResource;
use App\Http\Resources\ProductsCollection;

class ProductsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Product::with('categories');

        //TODO/ filtros: precio_min y precio_max

        // Filtrar por Categoría
        if ($request->has('category_id')) {
            $query->whereHas('categories', function ($q) use ($request) {
                $q->where('categories.id', $request->category_id);
            });
        }

        // Búsqueda por texto (nombre o descripción)
        if ($request->has('search')) {
            $searchTerm = '%' . $request->search . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', $searchTerm)
                  ->orWhere('description', 'like', $searchTerm);
            });
        }

        // Paginación de 6 elementos por página TODO: Tener en cuenta que esto es lo que se cambiara con las cookies
        $products = $query->paginate(6);

        return new ProductsCollection($products);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {


        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'categories' => 'array|exists:categories,id', // Array de IDs de categorías
        ]);

        $product = Product::create($validatedData);

        if (isset($validatedData['categories'])) {
            $product->categories()->attach($validatedData['categories']);
        }

        // Devolvemos el recurso recién creado
        return new ProductsResource($product->load('categories'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        return new ProductsResource($product->load('categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {


        $validatedData = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'price' => 'sometimes|numeric|min:0',
            'stock' => 'sometimes|integer|min:0',
            'categories' => 'array|exists:categories,id',
        ]);

        $product->update($validatedData);

        if (isset($validatedData['categories'])) {
            $product->categories()->sync($validatedData['categories']);
        }

        return new ProductsResource($product->load('categories'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Product $product) 
    // Nota: Añadimos Request para poder acceder al usuario si no usamos helpers
    {


        $product->delete();
        return response()->json(['message' => 'Product deleted successfully']);
    }
}
