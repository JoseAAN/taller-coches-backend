<?php

namespace App\Http\Docs;

use OpenApi\Attributes as OA;

class CartControllerDocs
{
    #[OA\Get(
        path: "/api/carts",
        summary: "Listar carritos",
        description: "Obtiene una lista de todos los carritos con sus productos.",
        tags: ["Carts"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Lista de carritos",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "data", type: "array", items: new OA\Items(ref: "#/components/schemas/Cart"))
                    ]
                )
            )
        ]
    )]
    public function index() {}

    #[OA\Post(
        path: "/api/carts",
        summary: "Crear carrito",
        description: "Crea un nuevo carrito para un usuario.",
        tags: ["Carts"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["user_id", "price"],
                properties: [
                    new OA\Property(property: "user_id", type: "integer", example: 1),
                    new OA\Property(property: "price", type: "number", format: "float", example: 0.00)
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Carrito creado",
                content: new OA\JsonContent(ref: "#/components/schemas/Cart")
            ),
            new OA\Response(response: 422, description: "Error de validación")
        ]
    )]
    public function store() {}

    #[OA\Get(
        path: "/api/carts/{id}",
        summary: "Obtener carrito",
        description: "Obtiene los detalles de un carrito específico.",
        tags: ["Carts"],
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
                description: "Detalles del carrito",
                content: new OA\JsonContent(ref: "#/components/schemas/Cart")
            ),
            new OA\Response(response: 404, description: "Carrito no encontrado")
        ]
    )]
    public function show() {}

    #[OA\Put(
        path: "/api/carts/{id}",
        summary: "Actualizar carrito",
        description: "Actualiza un carrito (requiere rol de admin).",
        tags: ["Carts"],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "integer")
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "user_id", type: "integer"),
                    new OA\Property(property: "price", type: "number", format: "float")
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Carrito actualizado",
                content: new OA\JsonContent(ref: "#/components/schemas/Cart")
            ),
            new OA\Response(response: 403, description: "No autorizado"),
            new OA\Response(response: 404, description: "Carrito no encontrado")
        ]
    )]
    public function update() {}

    #[OA\Delete(
        path: "/api/carts/{id}",
        summary: "Eliminar carrito",
        description: "Elimina un carrito (requiere rol de admin).",
        tags: ["Carts"],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(response: 200, description: "Carrito eliminado correctamente"),
            new OA\Response(response: 403, description: "No autorizado"),
            new OA\Response(response: 404, description: "Carrito no encontrado")
        ]
    )]
    public function destroy() {}
}
