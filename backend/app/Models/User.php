<?php
namespace App\Models;

// ... otros use ...
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "User",
    required: ["name", "email", "password"],
    title: "Usuario",
    description: "Modelo de usuario del sistema"
)]
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    #[OA\Property(format: "int64", description: "ID del usuario", example: 1)]
    private $id;

    #[OA\Property(description: "Nombre del usuario", example: "Juan Perez")]
    private $name;

    #[OA\Property(format: "email", description: "Correo electrónico", example: "juan@example.com")]
    private $email;

    #[OA\Property(format: "int64", description: "ID del rol", example: 2)]
    private $role_id;

    #[OA\Property(description: "Token de API (solo lectura)", example: "d83j...")]
    private $api_token;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'name',
        'email',
        'role_id',
        'password',
        'api_token',
        'dni',
        'phone',
        'address',
        'blocked',
        'login_attempts',
        'unblock_time',
    ];

    // Relación con Rol
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function orders() {
        return $this->hasMany(Order::class);
    }

    public function appointments() {
        return $this->hasMany(Appointment::class);
    }
}