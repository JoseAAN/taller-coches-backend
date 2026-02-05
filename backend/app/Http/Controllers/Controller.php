<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

#[OA\Info(title: "API Taller", version: "1.0.0")]
#[OA\Server(url: 'http://localhost:8000', description: 'Servidor Local')]

abstract class Controller
{
    //
}
