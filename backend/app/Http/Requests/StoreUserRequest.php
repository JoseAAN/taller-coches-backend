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
        ];
        //Nota: he eliminado el campo role_id ya que no es necesario, el rol se asigna por defecto al cliente
        //Nota2: el campo de "confirmed" espera un campo llamado "password_confirmation". 
    }
}
