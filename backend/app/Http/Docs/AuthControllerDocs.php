<?php

namespace App\Http\Docs;

use OpenApi\Attributes as OA;

class AuthControllerDocs
{
    #[OA\Post(
        path: "/api/login",
        summary: "Iniciar sesión",
        description: "Autentica al usuario y devuelve un token de acceso.",
        tags: ["Auth"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["email", "password"],
                properties: [
                    new OA\Property(property: "email", type: "string", format: "email", example: "juan@example.com"),
                    new OA\Property(property: "password", type: "string", example: "password123")
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Login exitoso",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Login exitoso"),
                        new OA\Property(property: "access_token", type: "string", example: "d83j..."),
                        new OA\Property(property: "token_type", type: "string", example: "Bearer"),
                        new OA\Property(property: "user", ref: "#/components/schemas/User")
                    ]
                )
            ),
            new OA\Response(response: 422, description: "Credenciales incorrectas")
        ]
    )]
    public function login() {}

    #[OA\Post(
        path: "/api/logout",
        summary: "Cerrar sesión",
        description: "Invalida el token de acceso actual.",
        tags: ["Auth"],
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: "Sesión cerrada correctamente",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Sesión cerrada correctamente")
                    ]
                )
            ),
            new OA\Response(response: 401, description: "No autenticado")
        ]
    )]
    public function logout() {}

    #[OA\Get(
        path: "/api/user",
        summary: "Obtener usuario autenticado",
        description: "Devuelve la información del usuario actual junto con su rol.",
        tags: ["Auth"],
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: "Información del usuario",
                content: new OA\JsonContent(ref: "#/components/schemas/User")
            ),
            new OA\Response(response: 401, description: "No autenticado")
        ]
    )]
    public function me() {}
}
