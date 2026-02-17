<?php

namespace App\Http\Docs;

use OpenApi\Attributes as OA;

class ProductControllerDocs
{
    #[OA\Get(
        path: "/api/products",
        summary: "Listar productos",
        description: "Obtiene una lista paginada de productos. Permite filtrar por categoría y buscar por nombre/descripción.",
        tags: ["Productos"],
        parameters: [
            new OA\Parameter(
                name: "category_id",
                in: "query",
                description: "Filtrar por ID de categoría",
                required: false,
                schema: new OA\Schema(type: "integer")
            ),
            new OA\Parameter(
                name: "search",
                in: "query",
                description: "Buscar por nombre o descripción",
                required: false,
                schema: new OA\Schema(type: "string")
            ),
            new OA\Parameter(
                name: "page",
                in: "query",
                description: "Número de página",
                required: false,
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Lista de productos",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "data", type: "array", items: new OA\Items(ref: "#/components/schemas/Product")),
                        new OA\Property(property: "links", type: "object"),
                        new OA\Property(property: "meta", type: "object")
                    ]
                )
            )
        ]
    )]
    public function index() {}

    #[OA\Post(
        path: "/api/products",
        summary: "Crear producto",
        description: "Crea un nuevo producto en el sistema.",
        tags: ["Productos"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["name", "price", "stock"],
                properties: [
                    new OA\Property(property: "name", type: "string", example: "Batería 12V"),
                    new OA\Property(property: "description", type: "string", example: "Batería de larga duración"),
                    new OA\Property(property: "price", type: "number", format: "float", example: 120.50),
                    new OA\Property(property: "stock", type: "integer", example: 10),
                    new OA\Property(
                        property: "categories",
                        type: "array",
                        items: new OA\Items(type: "integer"),
                        example: [1, 3],
                        description: "Array de IDs de categorías"
                    )
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Producto creado exitosamente",
                content: new OA\JsonContent(ref: "#/components/schemas/Product")
            ),
            new OA\Response(response: 422, description: "Error de validación")
        ]
    )]
    public function store() {}

    #[OA\Get(
        path: "/api/products/{id}",
        summary: "Obtener producto",
        description: "Obtiene los detalles de un producto específico.",
        tags: ["Productos"],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Detalles del producto",
                content: new OA\JsonContent(ref: "#/components/schemas/Product")
            ),
            new OA\Response(response: 404, description: "Producto no encontrado")
        ]
    )]
    public function show() {}

    #[OA\Put(
        path: "/api/products/{id}",
        summary: "Actualizar producto",
        description: "Actualiza los datos de un producto existente.",
        tags: ["Productos"],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "integer")
            )
        ],
        requestBody: new OA\RequestBody(
            description: "Datos del producto a actualizar",
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "name", type: "string"),
                    new OA\Property(property: "description", type: "string"),
                    new OA\Property(property: "price", type: "number", format: "float"),
                    new OA\Property(property: "stock", type: "integer"),
                    new OA\Property(
                        property: "categories",
                        type: "array",
                        items: new OA\Items(type: "integer")
                    )
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Producto actualizado",
                content: new OA\JsonContent(ref: "#/components/schemas/Product")
            ),
            new OA\Response(response: 404, description: "Producto no encontrado"),
            new OA\Response(response: 422, description: "Error de validación")
        ]
    )]
    public function update() {}

    #[OA\Delete(
        path: "/api/products/{id}",
        summary: "Eliminar producto",
        description: "Elimina un producto del sistema.",
        tags: ["Productos"],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(response: 200, description: "Producto eliminado correctamente"),
            new OA\Response(response: 404, description: "Producto no encontrado")
        ]
    )]
    public function destroy() {}
}
