<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "Role",
    required: ["name"],
    title: "Rol",
    description: "Rol de usuario"
)]
class Role extends Model
{
    #[OA\Property(format: "int64", description: "ID del rol", example: 1)]
    private $id;

    #[OA\Property(description: "Nombre del rol", example: "admin")]
    private $name;

    protected $guarded = [];

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
