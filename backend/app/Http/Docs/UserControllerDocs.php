<?php

namespace App\Http\Docs;

use OpenApi\Attributes as OA;

class UserControllerDocs
{
    #[OA\Post(
        path: "/api/v1/users",
        summary: "Registrar usuario",
        description: "Registra un nuevo usuario en el sistema con rol de cliente.",
        tags: ["Usuarios"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["name", "email", "password"],
                properties: [
                    new OA\Property(property: "name", type: "string", example: "Maria Rodriguez"),
                    new OA\Property(property: "email", type: "string", format: "email", example: "maria@example.com"),
                    new OA\Property(property: "password", type: "string", example: "password123")
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Usuario registrado exitosamente",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Usuario registrado exitosamente"),
                        new OA\Property(property: "access_token", type: "string", example: "d83j..."),
                        new OA\Property(property: "token_type", type: "string", example: "Bearer"),
                        new OA\Property(property: "user", ref: "#/components/schemas/User")
                    ]
                )
            ),
            new OA\Response(response: 422, description: "Error de validación")
        ]
    )]
    public function store() {}
}
