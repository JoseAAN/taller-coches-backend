<?php

namespace App\Http\Docs;

use OpenApi\Attributes as OA;

class CartInvoiceControllerDocs
{
    #[OA\Get(
        path: "/api/v1/cartinvoice",
        summary: "Listar facturas de carrito",
        description: "Obtiene una lista de todas las facturas de carritos.",
        tags: ["Invoices"],
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: "Lista de facturas",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "data", type: "array", items: new OA\Items(ref: "#/components/schemas/CartInvoice"))
                    ]
                )
            )
        ]
    )]
    public function index() {}

    #[OA\Get(
        path: "/api/v1/cartinvoice/{id}",
        summary: "Obtener factura de carrito",
        description: "Obtiene los detalles de una factura específica.",
        tags: ["Invoices"],
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
            new OA\Response(
                response: 200,
                description: "Detalles de la factura",
                content: new OA\JsonContent(ref: "#/components/schemas/CartInvoice")
            ),
            new OA\Response(response: 404, description: "Factura no encontrada")
        ]
    )]
    public function show() {}
}
