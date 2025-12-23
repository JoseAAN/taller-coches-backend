<?php
namespace App\Models;

// ... otros use ...
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    // ¡ESTO ES LO QUE TE FALTA O ESTÁ INCOMPLETO!
    protected $fillable = [
        'name',
        'email',
        'password',
        'id',
    ];

    // ... aquí abajo siguen tus funciones pedidos() y citas() ...
    public function pedidos() {
        return $this->hasMany(Pedido::class);
    }

    public function citas() {
        return $this->hasMany(Cita::class);
    }
}