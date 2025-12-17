<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Factura extends Model
{
    protected $guarded = [];

    // Relación 1: Saber de qué operación viene (Polimórfica)
    public function facturable()
    {
        return $this->morphTo();
    }

    // Relación 2: Saber de quién es (Directa - ¡La que crea la línea!)
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}