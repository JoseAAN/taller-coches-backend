<?php

namespace App\Http\Docs;

use OpenApi\Attributes as OA;

class ProductInvoiceControllerDocs
{
    #[OA\Get(
        path: "/api/product-invoices",
        summary: "Listar facturas de productos (Público)",
        description: "Obtiene una lista de facturas de productos. Permite buscar por número de factura.",
        tags: ["ProductInvoices"],
        parameters: [
            new OA\Parameter(
                name: "search",
                in: "query",
                description: "Buscar por número de factura",
                required: false,
                schema: new OA\Schema(type: "string")
            ),
            new OA\Parameter(
                name: "sort_by",
                in: "query",
                description: "Ordenar por campo",
                required: false,
                schema: new OA\Schema(type: "string")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Lista de facturas",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "data", type: "array", items: new OA\Items(ref: "#/components/schemas/ProductInvoice"))
                    ]
                )
            )
        ]
    )]
    public function index() {}

    #[OA\Post(
        path: "/api/v1/product-invoices",
        summary: "Crear factura de producto",
        description: "Crea una nueva factura de producto (requiere auth).",
        tags: ["ProductInvoices"],
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["total", "cart_id"],
                properties: [
                    new OA\Property(property: "total", type: "number", format: "float", example: 150.00),
                    new OA\Property(property: "cart_id", type: "integer", example: 1)
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Factura creada",
                content: new OA\JsonContent(ref: "#/components/schemas/ProductInvoice")
            ),
            new OA\Response(response: 422, description: "Error de validación")
        ]
    )]
    public function store() {}

    #[OA\Get(
        path: "/api/product-invoices/{id}",
        summary: "Obtener factura de producto",
        description: "Obtiene los detalles de una factura de producto específica.",
        tags: ["ProductInvoices"],
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
                description: "Detalles de la factura",
                content: new OA\JsonContent(ref: "#/components/schemas/ProductInvoice")
            ),
            new OA\Response(response: 404, description: "Factura no encontrada")
        ]
    )]
    public function show() {}

    #[OA\Put(
        path: "/api/v1/product-invoices/{id}",
        summary: "Actualizar factura de producto",
        description: "Actualiza una factura de producto (requiere rol de admin).",
        tags: ["ProductInvoices"],
        security: [["bearerAuth" => []]],
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
                    new OA\Property(property: "invoice_number", type: "string"),
                    new OA\Property(property: "total", type: "number", format: "float"),
                    new OA\Property(property: "cart_id", type: "integer")
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Factura actualizada",
                content: new OA\JsonContent(ref: "#/components/schemas/ProductInvoice")
            ),
            new OA\Response(response: 403, description: "No autorizado"),
            new OA\Response(response: 404, description: "Factura no encontrada")
        ]
    )]
    public function update() {}

    #[OA\Delete(
        path: "/api/v1/product-invoices/{id}",
        summary: "Eliminar factura de producto",
        description: "Elimina una factura de producto (requiere rol de admin).",
        tags: ["ProductInvoices"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(response: 200, description: "Factura eliminada correctamente"),
            new OA\Response(response: 403, description: "No autorizado"),
            new OA\Response(response: 404, description: "Factura no encontrada")
        ]
    )]
    public function destroy() {}
}
