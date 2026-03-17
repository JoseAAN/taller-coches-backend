<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use App\Models\Vehicle;
use App\Models\VehicleType;
use App\Models\Service;
use App\Models\Appointment;
use App\Models\Cart;
use App\Models\Invoice;
use Illuminate\Support\Carbon;

class DemoUserSeeder extends Seeder
{
    /**
     * Crea un usuario de demostración con todos los datos relacionados utilizando el nuevo sistema de factura unificada.
     */
    public function run(): void
    {
        $clientRole = Role::where('name', 'client')->first();

        // Crear usuario
        $demoUser = User::create([
            'name' => 'Carlos García López',
            'email' => 'demo@demo.com',
            'password' => bcrypt('12345678'),
            'dni' => '12345678A',
            'phone' => '612345678',
            'address' => 'Calle Mayor 15, 2ºB, Madrid',
            'role_id' => $clientRole->id,
        ]);

        // Crear 2 vehículos para el usuario
        $vehicleTypes = VehicleType::all();

        $vehiculo1 = Vehicle::create([
            'license_plate' => '1234ABC',
            'brand' => 'Seat',
            'model' => 'León',
            'color' => 'Gris',
            'vehicle_type_id' => $vehicleTypes->where('name', 'Turismo')->first()?->id ?? $vehicleTypes->first()->id,
            'user_id' => $demoUser->id,
        ]);

        $vehiculo2 = Vehicle::create([
            'license_plate' => '5678DEF',
            'brand' => 'Volkswagen',
            'model' => 'Tiguan',
            'color' => 'Blanco',
            'vehicle_type_id' => $vehicleTypes->where('name', 'SUV')->first()?->id ?? $vehicleTypes->last()->id,
            'user_id' => $demoUser->id,
        ]);

        // Buscamos los servicios que vamos a usar
        $serviceLavado = Service::where('name', 'Lavado a Mano Básico')->first();
        $servicePulido = Service::where('name', 'Pulido de Faros')->first();

        // Cita pasada
        $startPasada = Carbon::now()->subDays(5)->setTime(10, 0);
        $citaPasada = Appointment::create([
            'vehicle_id' => $vehiculo1->id,
            'service_id' => $serviceLavado->id,
            'appointment_date' => $startPasada,
            'end_time' => $startPasada->copy()->addMinutes($serviceLavado->average_duration),
            'final_price' => $serviceLavado->price,
        ]);

        // Cita futura
        $startFutura = Carbon::now()->addDays(3)->setTime(16, 0);
        Appointment::create([
            'vehicle_id' => $vehiculo2->id,
            'service_id' => $servicePulido->id,
            'appointment_date' => $startFutura,
            'end_time' => $startFutura->copy()->addMinutes($servicePulido->average_duration),
            'final_price' => $servicePulido->price,
        ]);

        // Factura de compra (usando un carrito existente del CartSeeder)
        $existingCart = Cart::whereHas('items')->first();
        if ($existingCart) {
            Invoice::factory()->create([
                'total' => $existingCart->price,
                'cart_id' => $existingCart->id,
                'user_id' => $demoUser->id,
            ]);
        }

        // Factura de servicio directo (asociada a la cita pasada)
        Invoice::factory()->create([
            'total' => $serviceLavado->price,
            'appointment_id' => $citaPasada->id,
            'user_id' => $demoUser->id,
        ]);
    }
}
