<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Carbon;

class AuthController extends Controller
{
    const MAX_ATTEMPTS = 5;
    const LOCKOUT_MINUTES = 15;

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        // Usuario no encontrado → error genérico
        if (!$user) {
            throw ValidationException::withMessages([
                'email' => ['Las credenciales son incorrectas.'],
            ]);
        }

        // Bloqueado por el administrador
        if ($user->blocked) {
            return response()->json([
                'message' => 'Tu cuenta ha sido bloqueada por un administrador. Contacta con soporte para más información.',
                'code'    => 'ACCOUNT_BLOCKED',
            ], 403);
        }

        // Bloqueado temporalmente por intentos fallidos
        if ($user->unblock_time && Carbon::now()->lt($user->unblock_time)) {
            $minutosRestantes = (int) Carbon::now()->diffInMinutes($user->unblock_time, false) + 1;
            return response()->json([
                'message'  => "Cuenta bloqueada temporalmente. Inténtalo de nuevo en {$minutosRestantes} minuto(s).",
                'code'     => 'TOO_MANY_ATTEMPTS',
                'retry_in' => $minutosRestantes,
            ], 429);
        }

        // Contraseña incorrecta
        if (!Hash::check($request->password, $user->password)) {
            $user->login_attempts += 1;

            if ($user->login_attempts >= self::MAX_ATTEMPTS) {
                $user->unblock_time    = Carbon::now()->addMinutes(self::LOCKOUT_MINUTES);
                $user->login_attempts  = 0;
                $user->save();

                return response()->json([
                    'message'  => 'Has superado el número máximo de intentos. Cuenta bloqueada durante ' . self::LOCKOUT_MINUTES . ' minutos.',
                    'code'     => 'TOO_MANY_ATTEMPTS',
                    'retry_in' => self::LOCKOUT_MINUTES,
                ], 429);
            }

            $intentosRestantes = self::MAX_ATTEMPTS - $user->login_attempts;
            $user->save();

            throw ValidationException::withMessages([
                'email' => ["Las credenciales son incorrectas. Te quedan {$intentosRestantes} intento(s)."],
            ]);
        }

        // Login correcto → resetear contadores y generar token
        $token = \Illuminate\Support\Str::random(60);
        $user->forceFill([
            'api_token'     => $token,
            'login_attempts' => 0,
            'unblock_time'  => null,
        ])->save();

        return response()->json([
            'message'      => 'Login exitoso',
            'access_token' => $token,
            'token_type'   => 'Bearer',
            'user'         => $user->load('role'),
        ]);
    }

    public function logout(Request $request)
    {
        $user = $request->user();

        if ($user) {
            $user->forceFill(['api_token' => null])->save();
        }

        return response()->json(['message' => 'Sesión cerrada correctamente']);
    }
}
