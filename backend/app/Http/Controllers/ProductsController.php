<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
// Carbon es una librería de PHP (incluida en Laravel) para manejar fechas.
// Se usa Carbon::now() en lugar de la función SQL NOW() porque:
// - NOW() es una función específica de MySQL y no funciona en SQLite (usado en tests).
// - Carbon::now() genera la fecha desde PHP, lo que lo hace compatible con cualquier base de datos.
// - Ambas producen el mismo resultado: la fecha y hora actuales
use Carbon\Carbon;

/**
 * Controlador de Productos usando SQL puro (sin Eloquent ORM).
 * Todas las consultas usan sentencias preparadas (prepared statements)
 * con placeholders (?) para prevenir inyección SQL.
 */
class ProductsController extends Controller
{
    /**
     * Listar productos con filtros opcionales.
     * Usa: SELECT con JOIN, WHERE, LIKE y paginación manual.
     */
    public function index(Request $request)
    {
        
        $bindings = [];

        // Seleccionar todos los productos
        $sql = "SELECT p.id, p.name, p.price, p.description, p.stock, p.created_at, p.updated_at FROM products p";
        $countSql = "SELECT COUNT(DISTINCT p.id) as total FROM products p";

        $whereClauses = [];

        // Filtro por categoría: JOIN con tabla pivote
        if ($request->has('category_id')) {
            $sql .= " INNER JOIN products_categories pc ON p.id = pc.product_id";
            $countSql .= " INNER JOIN products_categories pc ON p.id = pc.product_id";
            $whereClauses[] = "pc.category_id = ?";
            $bindings[] = $request->category_id;
        }

        // Filtro por búsqueda de texto (nombre o descripción)
        // Usa LIKE con parámetro preparado para evitar inyección SQL
        if ($request->has('search')) {
            $whereClauses[] = "(p.name LIKE ? OR p.description LIKE ?)";
            $searchTerm = '%' . $request->search . '%';
            $bindings[] = $searchTerm;
            $bindings[] = $searchTerm;
        }

        // Ensamblar cláusulas WHERE si existen
        if (!empty($whereClauses)) {
            $whereString = " WHERE " . implode(" AND ", $whereClauses);
            $sql .= $whereString;
            $countSql .= $whereString;
        }

        // Agrupar para evitar duplicados cuando hay JOIN
        $sql .= " GROUP BY p.id, p.name, p.price, p.description, p.stock, p.created_at, p.updated_at";

        // Ordenar por ID descendente (más recientes primero)
        $sql .= " ORDER BY p.id DESC";

        // --- Paginación manual ---
        $perPage = 6;
        $page = max(1, (int) $request->get('page', 1));
        $offset = ($page - 1) * $perPage;

        // Contar total de resultados para la paginación
        $totalResult = DB::select($countSql, $bindings);
        $total = $totalResult[0]->total;

        // Añadir LIMIT y OFFSET para paginar
        $sql .= " LIMIT ? OFFSET ?";
        $paginatedBindings = array_merge($bindings, [$perPage, $offset]);

        // Ejecutar consulta principal con prepared statement
        $products = DB::select($sql, $paginatedBindings);

        // Para cada producto, obtener sus categorías con una consulta JOIN
        foreach ($products as &$product) {
            $categories = DB::select(
                "SELECT c.name 
                 FROM categories c 
                 INNER JOIN products_categories pc ON c.id = pc.category_id 
                 WHERE pc.product_id = ?",
                [$product->id]
            );
            // Extraer solo los nombres de las categorías
            $product->categories = array_map(fn($cat) => $cat->name, $categories);
        }

        // Construir respuesta con metadatos de paginación
        return response()->json([
            'data' => $products,
            'meta' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'last_page' => (int) ceil($total / $perPage),
                'path' => $request->url(), // URL base para la paginación del frontend
            ]
        ]);
    }

    /**
     * Mostrar un producto específico por ID.
     * Usa: SELECT con WHERE y prepared statement.
     */
    public function show(int $id)
    {
        // Buscar el producto por ID con prepared statement
        $products = DB::select("SELECT * FROM products WHERE id = ?", [$id]);

        // Verificar si el producto existe
        if (empty($products)) {
            return response()->json(['message' => 'Producto no encontrado'], 404);
        }

        $product = $products[0];

        // Obtener categorías del producto mediante JOIN
        $categories = DB::select(
            "SELECT c.name
             FROM categories c
             INNER JOIN products_categories pc ON c.id = pc.category_id
             WHERE pc.product_id = ?",
            [$product->id]
        );
        $product->categories = array_map(fn($cat) => $cat->name, $categories);

        return response()->json(['data' => $product]);
    }

    /**
     * Crear un nuevo producto.
     * Usa: INSERT INTO con prepared statement y transacción.
     */
    public function store(Request $request)
    {
        // Validación de datos de entrada (Laravel)
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'categories' => 'array|exists:categories,id',
        ]);

        // Usar transacción para asegurar consistencia (producto + categorías)
        DB::beginTransaction();

        try {
            $now = Carbon::now();

            // INSERT del producto con prepared statement
            DB::insert(
                "INSERT INTO products (name, description, price, stock, created_at, updated_at) 
                 VALUES (?, ?, ?, ?, ?, ?)",
                [
                    $validatedData['name'],
                    $validatedData['description'] ?? null,
                    $validatedData['price'],
                    $validatedData['stock'],
                    $now,
                    $now,
                ]
            );

            // Obtener el ID del producto recién insertado
            $productId = DB::getPdo()->lastInsertId();

            // Insertar relaciones con categorías en tabla pivote
            if (isset($validatedData['categories'])) {
                foreach ($validatedData['categories'] as $categoryId) {
                    DB::insert(
                        "INSERT INTO products_categories (product_id, category_id, created_at, updated_at) 
                         VALUES (?, ?, ?, ?)",
                        [$productId, $categoryId, $now, $now]
                    );
                }
            }

            DB::commit();

            // Devolver el producto creado
            return $this->show($productId);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error al crear el producto', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Actualizar un producto existente.
     * Usa: UPDATE con prepared statement y transacción.
     */
    public function update(Request $request, int $id)
    {
        // Verificar que el producto existe
        $existing = DB::select("SELECT * FROM products WHERE id = ?", [$id]);
        if (empty($existing)) {
            return response()->json(['message' => 'Producto no encontrado'], 404);
        }

        // Validación de datos
        $validatedData = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'price' => 'sometimes|numeric|min:0',
            'stock' => 'sometimes|integer|min:0',
            'categories' => 'array|exists:categories,id',
        ]);

        DB::beginTransaction();

        try {
            // Construir UPDATE dinámico solo con los campos proporcionados
            $setClauses = [];
            $bindings = [];

            if (isset($validatedData['name'])) {
                $setClauses[] = "name = ?";
                $bindings[] = $validatedData['name'];
            }
            if (array_key_exists('description', $validatedData)) {
                $setClauses[] = "description = ?";
                $bindings[] = $validatedData['description'];
            }
            if (isset($validatedData['price'])) {
                $setClauses[] = "price = ?";
                $bindings[] = $validatedData['price'];
            }
            if (isset($validatedData['stock'])) {
                $setClauses[] = "stock = ?";
                $bindings[] = $validatedData['stock'];
            }

            // Siempre actualizar updated_at
            $now = Carbon::now();
            $setClauses[] = "updated_at = ?";
            $bindings[] = $now;

            if (!empty($setClauses)) {
                $sql = "UPDATE products SET " . implode(", ", $setClauses) . " WHERE id = ?";
                $bindings[] = $id;
                DB::update($sql, $bindings);
            }

            // Sincronizar categorías: borrar las actuales e insertar las nuevas
            if (isset($validatedData['categories'])) {
                // DELETE las asociaciones anteriores
                DB::delete("DELETE FROM products_categories WHERE product_id = ?", [$id]);

                // INSERT las nuevas asociaciones
                foreach ($validatedData['categories'] as $categoryId) {
                    DB::insert(
                        "INSERT INTO products_categories (product_id, category_id, created_at, updated_at) 
                         VALUES (?, ?, ?, ?)",
                        [$id, $categoryId, $now, $now]
                    );
                }
            }

            DB::commit();

            // Devolver el producto actualizado
            return $this->show($id);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error al actualizar el producto', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Eliminar un producto.
     * Usa: DELETE con prepared statement.
     */
    public function destroy(Request $request, int $id)
    {
        // Verificar que el producto existe antes de borrar
        $existing = DB::select("SELECT id FROM products WHERE id = ?", [$id]);
        if (empty($existing)) {
            return response()->json(['message' => 'Producto no encontrado'], 404);
        }

        // Eliminar relaciones en tabla pivote primero
        DB::delete("DELETE FROM products_categories WHERE product_id = ?", [$id]);

        // Eliminar el producto
        DB::delete("DELETE FROM products WHERE id = ?", [$id]);

        return response()->json(['message' => 'Producto borrado correctamente']);
    }
}
