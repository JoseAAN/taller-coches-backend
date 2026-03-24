<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\StoreClienteRequest;

class UserController extends Controller
{
    /**
     * Listar todos los usuarios con su rol.
     */
    public function index()
    {
        $users = User::with('role')->get();

        return response()->json([
            'data' => $users,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request)
    {
        $userData = $request->validated();
        
        // Asignar rol de Cliente por defecto
        $clientRole = \App\Models\Role::where('name', 'client')->first();
        if ($clientRole) {
            $userData['role_id'] = $clientRole->id;
        }

        // Hashear contraseña
        $userData['password'] = Hash::make($userData['password']);
        
        // Crear usuario
        $user = User::create($userData);

        // Generar token manual
        $token = \Illuminate\Support\Str::random(60);
        $user->forceFill(['api_token' => $token])->save();

        return response()->json([
            'message' => 'Usuario registrado exitosamente',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user->load('role'),
        ], 201);
    }

    /**
     * Mostrar un usuario específico por ID.
     */
    public function show(string $id)
    {
        $user = User::with('role')->find($id);

        if (!$user) {
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }

        return response()->json(['data' => $user]);
    }

    /**
     * Actualizar un usuario existente.
     */
    public function update(Request $request, string $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }

        $validatedData = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|max:255|unique:users,email,' . $id,
            'password' => 'sometimes|string|min:8',
            'role_id' => 'sometimes|exists:roles,id',
        ]);

        // Hashear la contraseña si se envía una nueva
        if (isset($validatedData['password'])) {
            $validatedData['password'] = Hash::make($validatedData['password']);
        }

        $user->update($validatedData);

        return response()->json([
            'message' => 'Usuario actualizado correctamente',
            'data' => $user->load('role'),
        ]);
    }

    /**
     * Eliminar un usuario.
     */
    public function destroy(string $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }

        $user->delete();

        return response()->json(['message' => 'Usuario eliminado correctamente']);
    }

    /**
     * Bloquear o desbloquear un usuario.
     */
    public function toggleBlock(string $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }

        $user->blocked = !$user->blocked;
        $user->save();

        $estado = $user->blocked ? 'bloqueado' : 'desbloqueado';

        return response()->json([
            'message' => "Usuario {$estado} correctamente",
            'data'    => $user->load('role'),
        ]);
    }

    public function checkAccessToken(Request $request)
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json(['message' => 'Token no proporcionado'], 401);
        }

        $user = User::where('api_token', $token)->first();

        if (!$user) {
            return response()->json(['message' => 'Token inválido'], 401);
        }

        return response()->json([
            'message' => 'Token válido',
            'user' => $user->load('role'),
        ]);
    }
}
