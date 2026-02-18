<?php

namespace App\Http\Docs;

use OpenApi\Attributes as OA;

class ServiceInvoiceControllerDocs
{
    #[OA\Get(
        path: "/api/service-invoices",
        summary: "Listar facturas de servicios (Público)",
        description: "Obtiene una lista de facturas de servicios. Permite buscar por número de factura.",
        tags: ["ServiceInvoices"],
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
                        new OA\Property(property: "data", type: "array", items: new OA\Items(ref: "#/components/schemas/ServiceInvoice"))
                    ]
                )
            )
        ]
    )]
    public function index() {}

    #[OA\Post(
        path: "/api/v1/service-invoices",
        summary: "Crear factura de servicio",
        description: "Crea una nueva factura de servicio (requiere rol de admin).",
        tags: ["ServiceInvoices"],
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["total", "user_id", "appointment_id"],
                properties: [
                    new OA\Property(property: "total", type: "number", format: "float", example: 85.50),
                    new OA\Property(property: "user_id", type: "integer", example: 1),
                    new OA\Property(property: "appointment_id", type: "integer", example: 10)
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Factura creada",
                content: new OA\JsonContent(ref: "#/components/schemas/ServiceInvoice")
            ),
            new OA\Response(response: 403, description: "No autorizado"),
            new OA\Response(response: 422, description: "Error de validación")
        ]
    )]
    public function store() {}

    #[OA\Get(
        path: "/api/service-invoices/{id}",
        summary: "Obtener factura de servicio",
        description: "Obtiene los detalles de una factura de servicio específica.",
        tags: ["ServiceInvoices"],
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
                content: new OA\JsonContent(ref: "#/components/schemas/ServiceInvoice")
            ),
            new OA\Response(response: 404, description: "Factura no encontrada")
        ]
    )]
    public function show() {}

    #[OA\Put(
        path: "/api/v1/service-invoices/{id}",
        summary: "Actualizar factura de servicio",
        description: "Actualiza una factura de servicio (requiere rol de admin).",
        tags: ["ServiceInvoices"],
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
                    new OA\Property(property: "user_id", type: "integer"),
                    new OA\Property(property: "appointment_id", type: "integer")
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Factura actualizada",
                content: new OA\JsonContent(ref: "#/components/schemas/ServiceInvoice")
            ),
            new OA\Response(response: 403, description: "No autorizado"),
            new OA\Response(response: 404, description: "Factura no encontrada")
        ]
    )]
    public function update() {}

    #[OA\Delete(
        path: "/api/v1/service-invoices/{id}",
        summary: "Eliminar factura de servicio",
        description: "Elimina una factura de servicio (requiere rol de admin).",
        tags: ["ServiceInvoices"],
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
