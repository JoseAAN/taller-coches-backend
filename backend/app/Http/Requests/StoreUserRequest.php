<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        //TODO: Habría que aplicar la lógica para saber si el usuario puede ejecutar este endpoint
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'], // confirmed expects password_confirmation field
            'captcha' => ['required', 'captcha'],
        ];
        //Nota: he eliminado el campo role_id ya que no es necesario, el rol se asigna por defecto al cliente
        //Nota2: el campo de "confirmed" espera un campo llamado "password_confirmation". 
        //Nota3: se añadió la validación del captcha
    }

    public function messages(): array
{
    return [
        'name.required'     => 'El nombre es obligatorio.',
        'name.string'       => 'El nombre debe ser un texto válido.',
        'name.max'          => 'El nombre no puede tener más de 255 caracteres.',
        
        'email.required'    => 'El correo electrónico es obligatorio.',
        'email.email'       => 'Debes introducir un formato de email válido.',
        'email.max'         => 'El email es demasiado largo.',
        'email.unique'      => 'Este correo electrónico ya está registrado.',
        
        'password.required'  => 'La contraseña es obligatoria.',
        'password.min'       => 'La contraseña debe tener al menos 8 caracteres.',
        'password.confirmed' => 'Las contraseñas no coinciden.',
        
        'captcha.required'   => 'Es necesario completar el captcha de seguridad.',
        'captcha.captcha'    => 'La validación del captcha ha fallado, inténtalo de nuevo.',
        // Si tu paquete usa la regla 'recaptcha', cambia la línea anterior por:
        // 'captcha.recaptcha' => 'La validación de Google indica que eres un robot.',
    ];
}
}
