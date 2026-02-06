<?php

namespace App\Http\Docs;

use OpenApi\Attributes as OA;

class AppointmentControllerDocs
{
    #[OA\Get(
        path: "/api/appointments",
        summary: "Consultar disponibilidad de huecos",
        description: "Calcula los intervalos de tiempo libres para un servicio basándose en la jornada laboral (08:00-21:00).",
        tags: ["Citas"],
        parameters: [
            new OA\Parameter(name: "service_id", in: "query", required: true, schema: new OA\Schema(type: "integer")),
            new OA\Parameter(name: "date", in: "query", required: true, schema: new OA\Schema(type: "string", format: "date"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Lista de huecos disponibles")
        ]
    )]
    public function index() {}

    #[OA\Post(
        path: "/api/appointments",
        summary: "Registrar una nueva cita",
        tags: ["Citas"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["vehicle_id", "service_id", "date", "start_time"],
                properties: [
                    new OA\Property(property: "vehicle_id", type: "integer", example: 1),
                    new OA\Property(property: "service_id", type: "integer", example: 2),
                    new OA\Property(property: "date", type: "string", format: "date", example: "2026-05-20"),
                    new OA\Property(property: "start_time", type: "string", example: "10:00")
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Cita creada"),
            new OA\Response(response: 409, description: "Horario ocupado")
        ]
    )]
    public function store() {}

    #[OA\Get(
        path: "/api/appointments/{id}",
        summary: "Detalle de una cita",
        tags: ["Citas"],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Datos de la cita encontrados"),
            new OA\Response(response: 404, description: "Cita no encontrada")
        ]
    )]
    public function show() {}

    #[OA\Put(
        path: "/api/appointments/{id}",
        summary: "Actualizar cita",
        tags: ["Citas"],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "service_id", type: "integer"),
                    new OA\Property(property: "start_time", type: "string")
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Actualizado correctamente")
        ]
    )]
    public function update() {}

    #[OA\Delete(
        path: "/api/appointments/{id}",
        summary: "Eliminar cita",
        tags: ["Citas"],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Eliminado"),
            new OA\Response(response: 404, description: "No encontrado")
        ]
    )]
    public function destroy() {}
}
