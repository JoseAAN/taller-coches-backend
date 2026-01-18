<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Service;
use App\Models\ServiceType;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Crear Tipos de Servicio
        $exterior = ServiceType::create(['name' => 'Limpieza Exterior']);
        $interior = ServiceType::create(['name' => 'Limpieza Interior']);
        $detail = ServiceType::create(['name' => 'Detallado y Tratamientos']);

        // 2. Crear Servicios (Vinculados a los tipos)
        
        // --- Exterior ---
        Service::create([
            'name' => 'Lavado a Mano Básico',
            'description' => 'Lavado exterior con champú pH neutro, secado manual y limpieza de llantas superficial.',
            'price' => 15.00,
            'average_duration' => 30, // minutos
            'service_type_id' => $exterior->id,
        ]);

        Service::create([
            'name' => 'Lavado Premium con Cera',
            'description' => 'Incluye lavado a mano, descontaminación férrica de llantas y aplicación de cera líquida para brillo y protección.',
            'price' => 35.50,
            'average_duration' => 60,
            'service_type_id' => $exterior->id,
        ]);

        // --- Interior ---
        Service::create([
            'name' => 'Aspirado y Limpieza de Salpicadero',
            'description' => 'Aspirado completo (suelos, alfombrillas, asientos) y limpieza de polvo en salpicadero y plásticos.',
            'price' => 20.00,
            'average_duration' => 45,
            'service_type_id' => $interior->id,
        ]);

        Service::create([
            'name' => 'Limpieza Integral de Tapicería',
            'description' => 'Limpieza profunda de asientos y moquetas con máquina de inyección-stracción para eliminar manchas y olores.',
            'price' => 80.00,
            'average_duration' => 120,
            'service_type_id' => $interior->id,
        ]);

        // --- Detallado / Especial ---
        Service::create([
            'name' => 'Pulido de Faros',
            'description' => 'Restauración de transparencia en faros delanteros, eliminando el tono amarillento y mejorando la visibilidad.',
            'price' => 45.00,
            'average_duration' => 90,
            'service_type_id' => $detail->id,
        ]);

        Service::create([
            'name' => 'Tratamiento Cerámico (Coating)',
            'description' => 'Protección de pintura de larga duración (hasta 2 años), repelente al agua y suciedad. Requiere lavado previo.',
            'price' => 250.00,
            'average_duration' => 240, // 4 horas
            'service_type_id' => $detail->id,
        ]);
    }
}
