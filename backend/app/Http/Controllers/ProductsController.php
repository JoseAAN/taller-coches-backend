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

        // Ordenar por ID ascendente
        $sql .= " ORDER BY p.id ASC";

        // --- Paginación manual ---
        $perPage = 12;
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

        if (!empty($products)) {
            // 1. Extraemos todos los IDs de esta página
            $productIds = array_map(fn($p) => $p->id, $products);
            $placeholders = implode(',', array_fill(0, count($productIds), '?'));

            // ---------------------------------------------------------
            // 2A. LÓGICA DE CATEGORÍAS (Tu código súper optimizado)
            // ---------------------------------------------------------
            $categoriesRelations = DB::select(
                "SELECT pc.product_id, c.name
                 FROM categories c
                 INNER JOIN products_categories pc ON c.id = pc.category_id
                 WHERE pc.product_id IN ($placeholders)",
                $productIds
            );

            $categoriesByProduct = [];
            foreach ($categoriesRelations as $row) {
                $categoriesByProduct[$row->product_id][] = $row->name;
            }

            // ---------------------------------------------------------
            // 2B. NUEVO: LÓGICA DE IMÁGENES (Misma optimización)
            // ---------------------------------------------------------
            $imagesRelations = DB::select(
                "SELECT pi.product_id, i.id, i.url, i.is_primary
                 FROM images i
                 INNER JOIN product_images pi ON i.id = pi.image_id
                 WHERE pi.product_id IN ($placeholders)",
                $productIds
            );

            $imagesByProduct = [];
            foreach ($imagesRelations as $row) {
                $imagesByProduct[$row->product_id][] = [
                    'id' => $row->id,
                    'url' => $row->url,
                    // Convertimos el 1/0 a booleano aquí mismo
                    'is_primary' => (bool) $row->is_primary
                ];
            }

            // ---------------------------------------------------------
            // 3. ASIGNACIÓN FINAL EN O(N)
            // ---------------------------------------------------------
            foreach ($products as &$product) {
                $product->categories = $categoriesByProduct[$product->id] ?? [];
                // Asignamos las imágenes (si no tiene, se queda un array vacío)
                $product->images = $imagesByProduct[$product->id] ?? [];
            }
        }

        // Construir respuesta con metadatos de paginación
        return response()->json([
            'data' => $products,
            'meta' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'last_page' => (int) ceil($total / $perPage),
                'path' => $request->url(),
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

        // --- NUEVO: Obtener imágenes del producto mediante JOIN ---
        $images = DB::select(
            "SELECT i.id, i.url, i.is_primary
             FROM images i
             INNER JOIN product_images pi ON i.id = pi.image_id
             WHERE pi.product_id = ?",
            [$product->id]
        );

        // Formateamos las imágenes (y convertimos el 1/0 de MySQL a un true/false real)
        $product->images = array_map(function ($img) {
            return [
                'id' => $img->id,
                'url' => $img->url,
                'is_primary' => (bool) $img->is_primary
            ];
        }, $images);
        // ----------------------------------------------------------

        return response()->json(['data' => $product]);
    }

    /**
     * Crear un nuevo producto.
     * Usa: INSERT INTO con prepared statement y transacción.
     */
    public function store(Request $request)
    {
        // 1. Validación de datos de entrada (Añadimos image_url)
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'categories' => 'array|exists:categories,id',
            'image_url' => 'nullable|string|max:255', // <-- NUEVO: Validamos la imagen
        ]);

        // Usar transacción para asegurar consistencia (producto + categorías + imágenes)
        DB::beginTransaction();

        try {
            $now = Carbon::now();

            // 2. INSERT del producto
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

            // 3. Insertar relaciones con categorías en tabla pivote
            if (isset($validatedData['categories'])) {
                foreach ($validatedData['categories'] as $categoryId) {
                    DB::insert(
                        "INSERT INTO products_categories (product_id, category_id, created_at, updated_at)
                         VALUES (?, ?, ?, ?)",
                        [$productId, $categoryId, $now, $now]
                    );
                }
            }

            // 4. NUEVO: Insertar la imagen y su relación
            if (!empty($validatedData['image_url'])) {
                // A. Insertamos la imagen en su tabla marcándola como principal (1)
                DB::insert(
                    "INSERT INTO images (url, is_primary, created_at, updated_at)
                     VALUES (?, ?, ?, ?)",
                    [$validatedData['image_url'], 1, $now, $now] // 1 = true (booleano en MySQL)
                );

                // Recuperamos el ID de la imagen que se acaba de crear
                $imageId = DB::getPdo()->lastInsertId();

                // B. Vinculamos el producto y la imagen en la tabla pivote
                DB::insert(
                    "INSERT INTO product_images (product_id, image_id, created_at, updated_at)
                     VALUES (?, ?, ?, ?)",
                    [$productId, $imageId, $now, $now]
                );
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

        // Validación de datos (Añadimos image_url)
        $validatedData = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'price' => 'sometimes|numeric|min:0',
            'stock' => 'sometimes|integer|min:0',
            'categories' => 'array|exists:categories,id',
            'image_url' => 'nullable|string|max:255', // <-- NUEVO: Validación de la imagen
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

            // --- NUEVO: LÓGICA DE ACTUALIZACIÓN DE IMÁGENES ---
            if (array_key_exists('image_url', $validatedData)) {
                $imageUrl = $validatedData['image_url'];

                // 1. Buscamos si este producto ya tiene una imagen principal asignada
                $existingImage = DB::select("
                    SELECT i.id
                    FROM images i
                    JOIN product_images pi ON i.id = pi.image_id
                    WHERE pi.product_id = ? AND i.is_primary = 1
                    LIMIT 1
                ", [$id]);

                if (!empty($imageUrl)) {
                    if (!empty($existingImage)) {
                        // A. Si ya tenía foto, simplemente actualizamos la URL
                        DB::update(
                            "UPDATE images SET url = ?, updated_at = ? WHERE id = ?",
                            [$imageUrl, $now, $existingImage[0]->id]
                        );
                    } else {
                        // B. Si NO tenía foto, la creamos y la vinculamos
                        DB::insert(
                            "INSERT INTO images (url, is_primary, created_at, updated_at) VALUES (?, ?, ?, ?)",
                            [$imageUrl, 1, $now, $now]
                        );
                        $imageId = DB::getPdo()->lastInsertId();
                        DB::insert(
                            "INSERT INTO product_images (product_id, image_id, created_at, updated_at) VALUES (?, ?, ?, ?)",
                            [$id, $imageId, $now, $now]
                        );
                    }
                } else {
                    // C. Si nos envían la imagen vacía (null), borramos el vínculo para quitarle la foto al producto
                    if (!empty($existingImage)) {
                        DB::delete("DELETE FROM product_images WHERE product_id = ? AND image_id = ?", [$id, $existingImage[0]->id]);
                        // Opcional: DB::delete("DELETE FROM images WHERE id = ?", [$existingImage[0]->id]);
                    }
                }
            }
            // -------------------------------------------------

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
