<?php

namespace App\Http\Docs;

use OpenApi\Attributes as OA;

class ServiceControllerDocs
{
    #[OA\Get(
        path: "/api/services",
        summary: "Listar servicios",
        description: "Obtiene una lista paginada de servicios. Permite filtrar por tipo y buscar por nombre/descripción.",
        tags: ["Servicios"],
        parameters: [
            new OA\Parameter(
                name: "service_type_id",
                in: "query",
                description: "Filtrar por ID de tipo de servicio",
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
                description: "Lista de servicios",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "data", type: "array", items: new OA\Items(ref: "#/components/schemas/Service")),
                        new OA\Property(property: "links", type: "object"),
                        new OA\Property(property: "meta", type: "object")
                    ]
                )
            )
        ]
    )]
    public function index() {}

    #[OA\Post(
        path: "/api/services",
        summary: "Crear servicio",
        description: "Crea un nuevo servicio en el sistema.",
        tags: ["Servicios"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["name", "price", "service_type_id"],
                properties: [
                    new OA\Property(property: "name", type: "string", example: "Alineación y Balanceo"),
                    new OA\Property(property: "price", type: "number", format: "float", example: 35.00),
                    new OA\Property(property: "average_duration", type: "integer", example: 60),
                    new OA\Property(property: "description", type: "string", example: "Servicio completo de alineación"),
                    new OA\Property(property: "service_type_id", type: "integer", example: 1)
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Servicio creado exitosamente",
                content: new OA\JsonContent(ref: "#/components/schemas/Service")
            ),
            new OA\Response(response: 422, description: "Error de validación")
        ]
    )]
    public function store() {}

    #[OA\Get(
        path: "/api/services/{id}",
        summary: "Obtener servicio",
        description: "Obtiene los detalles de un servicio específico.",
        tags: ["Servicios"],
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
                description: "Detalles del servicio",
                content: new OA\JsonContent(ref: "#/components/schemas/Service")
            ),
            new OA\Response(response: 404, description: "Servicio no encontrado")
        ]
    )]
    public function show() {}

    #[OA\Put(
        path: "/api/services/{id}",
        summary: "Actualizar servicio",
        description: "Actualiza los datos de un servicio existente.",
        tags: ["Servicios"],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "integer")
            )
        ],
        requestBody: new OA\RequestBody(
            description: "Datos del servicio a actualizar",
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "name", type: "string"),
                    new OA\Property(property: "price", type: "number", format: "float"),
                    new OA\Property(property: "average_duration", type: "integer"),
                    new OA\Property(property: "description", type: "string"),
                    new OA\Property(property: "service_type_id", type: "integer")
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Servicio actualizado",
                content: new OA\JsonContent(ref: "#/components/schemas/Service")
            ),
            new OA\Response(response: 404, description: "Servicio no encontrado"),
            new OA\Response(response: 422, description: "Error de validación")
        ]
    )]
    public function update() {}

    #[OA\Delete(
        path: "/api/services/{id}",
        summary: "Eliminar servicio",
        description: "Elimina un servicio del sistema.",
        tags: ["Servicios"],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(response: 200, description: "Servicio eliminado correctamente"),
            new OA\Response(response: 404, description: "Servicio no encontrado")
        ]
    )]
    public function destroy() {}

    #[OA\Patch(
        path: "/api/services/{id}/toggle-home",
        summary: "Alternar mostrar en inicio",
        description: "Activa o desactiva la visualización de un servicio en la página de inicio. Máximo 3 servicios permitidos en inicio.",
        tags: ["Servicios"],
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
                required: ["show_on_home"],
                properties: [
                    new OA\Property(property: "show_on_home", type: "boolean", example: true)
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Estado actualizado",
                content: new OA\JsonContent(ref: "#/components/schemas/Service")
            ),
            new OA\Response(response: 404, description: "Servicio no encontrado"),
            new OA\Response(
                response: 422,
                description: "Límite de servicios en inicio alcanzado",
                content: new OA\JsonContent(
                    properties: [new OA\Property(property: "message", type: "string", example: "Límite alcanzado")]
                )
            )
        ]
    )]
    public function toggleShowOnHome() {}

    #[OA\Get(
        path: "/api/home-services",
        summary: "Servicios destacados",
        description: "Obtiene la lista de servicios marcados para mostrar en la página de inicio.",
        tags: ["Servicios"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Lista de servicios destacados",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "data", type: "array", items: new OA\Items(ref: "#/components/schemas/Service"))
                    ]
                )
            )
        ]
    )]
    public function getHomeServices() {}
}
