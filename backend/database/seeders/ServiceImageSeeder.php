<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Image;
use App\Models\Service;

class ServiceImageSeeder extends Seeder
{
    public function run(): void
    {
        // Relacionamos el ID del servicio con el nombre de su foto
        $imagenes = [
            1 => 'LavadoAManoBasico.png',
            2 => 'LavadoPremiumConCera.png',
            3 => 'AspiradoYLimpiezaDeSalpicadero.png',
            4 => 'LimpiezaIntegraDeTapiceria.png',
            5 => 'PulidoDeFaros.png',
            6 => 'TratamientoCeramico.png',
        ];

        // Recorremos el array y actualizamos cada servicio si existe
        foreach ($imagenes as $id => $imageName) {
            $service = Service::find($id);

            if ($service) {
                $service->update(['image' => $imageName]);

                $image = Image::firstOrCreate(
                    ['url' => $imageName],
                    ['is_primary' => true]
                );

                if (!$service->images()->where('images.id', $image->id)->exists()) {
                    $service->images()->attach($image->id);
                }
            }
        }
    }
}
