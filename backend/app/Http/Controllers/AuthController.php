<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    const MAX_ATTEMPTS = 5;
    const LOCKOUT_MINUTES = 15;
    const GOOGLE_STATE_COOKIE = 'google_oauth_state';

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
            'api_token'      => hash('sha256', $token),
            'login_attempts' => 0,
            'unblock_time'   => null,
        ])->save();

        $cookie = cookie('auth_token', $token, 60 * 24 * 30, null, null, env('APP_ENV') === 'production', true, false, 'Lax');

        return response()->json([
            'message' => 'Login exitoso',
            'user'    => $user->load('role'),
        ])->withCookie($cookie);
    }

    public function logout(Request $request)
    {
        $user = $request->user();

        if ($user) {
            $user->forceFill(['api_token' => null])->save();
        }

        $cookie = \Illuminate\Support\Facades\Cookie::forget('auth_token');

        return response()->json(['message' => 'Sesión cerrada correctamente'])->withCookie($cookie);
    }

    public function redirectGoogle()
    {
        $state = Str::random(40);
        $cookie = cookie(
            self::GOOGLE_STATE_COOKIE,
            $state,
            10,
            null,
            null,
            env('APP_ENV') === 'production',
            true,
            false,
            'Lax'
        );

        return Socialite::driver('google')
            ->stateless()
            ->with(['state' => $state])
            ->redirect()
            ->withCookie($cookie);
    }

    public function callbackGoogle()
    {
        $expectedState = request()->cookie(self::GOOGLE_STATE_COOKIE);
        $receivedState = request()->query('state');
        $forgetStateCookie = Cookie::forget(self::GOOGLE_STATE_COOKIE);

        if (!$expectedState || !$receivedState || !hash_equals($expectedState, $receivedState)) {
            return $this->redirectToFrontend('google_invalid_state')->withCookie($forgetStateCookie);
        }

        try {
            $user_google = Socialite::driver('google')->stateless()->user();
        } catch (\Exception $e) {
            return $this->redirectToFrontend('google_auth_failed')->withCookie($forgetStateCookie);
        }

        if (!$user_google->email) {
            return $this->redirectToFrontend('google_email_missing')->withCookie($forgetStateCookie);
        }

        $user = User::where('email', $user_google->email)->first();

        if ($user) {
            if ($user->blocked) {
                return $this->redirectToFrontend('account_blocked')->withCookie($forgetStateCookie);
            }

            if ($user->unblock_time && Carbon::now()->lt($user->unblock_time)) {
                return $this->redirectToFrontend('too_many_attempts')->withCookie($forgetStateCookie);
            }

            if ($user->google_id && $user->google_id !== $user_google->id) {
                return $this->redirectToFrontend('google_account_mismatch')->withCookie($forgetStateCookie);
            }

            // Si el usuario ya existe y no tenía Google enlazado, lo vinculamos a esta identidad.
            if (!$user->google_id) {
                $user->update(['google_id' => $user_google->id]);
            }
        } else {
            $clientRole = Role::where('name', 'client')->first();

            if (!$clientRole) {
                return $this->redirectToFrontend('role_not_configured')->withCookie($forgetStateCookie);
            }

            $user = User::create([
                'email'     => $user_google->email,
                'name'      => $user_google->name,
                'google_id' => $user_google->id,
                'role_id'   => $clientRole->id,
            ]);
        }

        // Generamos el token de la misma forma que en el método login() para la API
        $token = Str::random(60);
        $user->forceFill([
            'api_token'      => hash('sha256', $token),
            'login_attempts' => 0,
            'unblock_time'   => null,
        ])->save();

        $cookie = cookie('auth_token', $token, 60 * 24 * 30, null, null, env('APP_ENV') === 'production', true, false, 'Lax');

        // Redirigimos al frontend solo con la Cookie (HttpOnly) por seguridad, manteniendo la URL completamente limpia
        return $this->redirectToFrontend(null, ['google' => 'success'])
            ->withCookie($cookie)
            ->withCookie($forgetStateCookie);
    }

    private function redirectToFrontend(?string $error = null, array $params = [])
    {
        if ($error) {
            $params['error'] = $error;
        }

        $query = http_build_query($params);
        $url = trim(env('FRONTEND_URL', 'http://localhost:5173'), '/') . '/login';

        if ($query !== '') {
            $url .= '?' . $query;
        }

        return redirect($url);
    }
}
