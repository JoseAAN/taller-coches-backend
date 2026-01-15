<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Las credenciales son incorrectas.'],
            ]);
        }

        // Generar token manual
        $token = \Illuminate\Support\Str::random(60); 
        
        // Guardar token en base de datos
        $user->forceFill([
            'api_token' => $token, // Si se usa hash se debería hashear aquí
        ])->save();

        return response()->json([
            'message' => 'Login exitoso',
            'access_token' => $token, // Devolvemos el string puro
            'token_type' => 'Bearer',
            'user' => $user->load('role'),
        ]);
    }

    public function logout(Request $request)
    {
        // Invalidar token (Manual)
        // Como no usamos Sanctum, dependemos de que el middleware haya inyectado el user,
        // o buscamos al usuario por el token del header.
        
        $user = $request->user(); 
        
        if ($user) {
            $user->forceFill(['api_token' => null])->save();
        }

        return response()->json(['message' => 'Sesión cerrada correctamente']);
    }
}
