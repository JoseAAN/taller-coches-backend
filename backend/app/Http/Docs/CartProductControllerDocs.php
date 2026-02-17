<?php

namespace App\Http\Docs;

use OpenApi\Attributes as OA;

class CartProductControllerDocs
{
    #[OA\Get(
        path: "/api/cart-products",
        summary: "Listar productos de carritos",
        description: "Obtiene una lista paginada de productos en carritos.",
        tags: ["CartProducts"],
        parameters: [
            new OA\Parameter(
                name: "sort_by",
                in: "query",
                description: "Ordenar por campo (created_at, priceInTime, totalPerProduct, etc)",
                required: false,
                schema: new OA\Schema(type: "string")
            ),
            new OA\Parameter(
                name: "sort_order",
                in: "query",
                description: "Orden (asc, desc)",
                required: false,
                schema: new OA\Schema(type: "string", enum: ["asc", "desc"])
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Lista de productos en carritos",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "data", type: "array", items: new OA\Items(ref: "#/components/schemas/CartProduct")),
                        new OA\Property(property: "links", type: "object"),
                        new OA\Property(property: "meta", type: "object")
                    ]
                )
            )
        ]
    )]
    public function index() {}

    #[OA\Post(
        path: "/api/cart-products",
        summary: "Agregar producto al carrito",
        description: "Agrega un producto a un carrito existente.",
        tags: ["CartProducts"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["cart_id", "product_id", "quantity", "priceInTime"],
                properties: [
                    new OA\Property(property: "cart_id", type: "integer", example: 1),
                    new OA\Property(property: "product_id", type: "integer", example: 5),
                    new OA\Property(property: "quantity", type: "integer", example: 2),
                    new OA\Property(property: "priceInTime", type: "number", format: "float", example: 50.00)
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Producto agregado",
                content: new OA\JsonContent(ref: "#/components/schemas/CartProduct")
            ),
            new OA\Response(response: 422, description: "Error de validación")
        ]
    )]
    public function store() {}

    #[OA\Get(
        path: "/api/cart-products/{id}",
        summary: "Obtener producto de carrito",
        description: "Obtiene los detalles de un producto en un carrito.",
        tags: ["CartProducts"],
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
                description: "Detalles del producto en carrito",
                content: new OA\JsonContent(ref: "#/components/schemas/CartProduct")
            ),
            new OA\Response(response: 404, description: "No encontrado")
        ]
    )]
    public function show() {}

    #[OA\Put(
        path: "/api/cart-products/{id}",
        summary: "Actualizar producto de carrito",
        description: "Actualiza un producto en un carrito (requiere rol de admin).",
        tags: ["CartProducts"],
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
                    new OA\Property(property: "quantity", type: "integer"),
                    new OA\Property(property: "priceInTime", type: "number", format: "float")
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Actualizado correctamente",
                content: new OA\JsonContent(ref: "#/components/schemas/CartProduct")
            ),
            new OA\Response(response: 403, description: "No autorizado"),
            new OA\Response(response: 404, description: "No encontrado")
        ]
    )]
    public function update() {}

    #[OA\Delete(
        path: "/api/cart-products/{id}",
        summary: "Eliminar producto de carrito",
        description: "Elimina un producto de un carrito (requiere rol de admin).",
        tags: ["CartProducts"],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(response: 204, description: "Eliminado correctamente"),
            new OA\Response(response: 403, description: "No autorizado"),
            new OA\Response(response: 404, description: "No encontrado")
        ]
    )]
    public function destroy() {}
}
