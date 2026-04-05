<?php

namespace App\Http\Controllers;

use App\Models\Image;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\ServiceResource;
use App\Http\Resources\ServiceCollection;

class ServiceController extends Controller
{
    /**
     * Muestra un listado de los recursos.
     */
    public function index(Request $request)
    {
        $query = Service::with('serviceType', 'images');

        // Filtrar por Tipo de Servicio
        if ($request->has('service_type_id')) {
            $query->where('service_type_id', $request->service_type_id);
        }

        // Buscar por nombre o descripción
        if ($request->has('search')) {
            $searchTerm = '%' . $request->search . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', $searchTerm)
                  ->orWhere('description', 'like', $searchTerm);
            });
        }

        $services = $query->paginate(10);

        return new ServiceCollection($services);
    }

    /**
     * Almacena un recurso recién creado en la base de datos.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'average_duration' => 'nullable|integer|min:0',
            'description' => 'nullable|string',
            'service_type_id' => 'required|exists:service_types,id',
            'image_url' => 'nullable|string|max:255',
        ]);

        $service = DB::transaction(function () use ($validatedData) {
            $service = Service::create(collect($validatedData)->except('image_url')->all());
            $this->syncPrimaryImage($service, $validatedData['image_url'] ?? null);

            return $service;
        });

        return new ServiceResource($service->load('serviceType', 'images'));
    }

    /**
     * Muestra el recurso especificado.
     */
    public function show(Service $service)
    {
        return new ServiceResource($service->load('serviceType', 'images'));
    }

    /**
     * Actualiza el recurso especificado en la base de datos.
     */
    public function update(Request $request, Service $service)
    {
        $validatedData = $request->validate([
            'name' => 'sometimes|string|max:255',
            'price' => 'sometimes|numeric|min:0',
            'average_duration' => 'nullable|integer|min:0',
            'description' => 'nullable|string',
            'service_type_id' => 'sometimes|exists:service_types,id',
            'image_url' => 'nullable|string|max:255',
        ]);

        DB::transaction(function () use ($service, $validatedData) {
            $service->update(collect($validatedData)->except('image_url')->all());

            if (array_key_exists('image_url', $validatedData)) {
                $this->syncPrimaryImage($service, $validatedData['image_url']);
            }
        });

        return new ServiceResource($service->load('serviceType', 'images'));
    }

    /**
     * Elimina el recurso especificado de la base de datos.
     */
    public function destroy(Service $service)
    {
        $service->delete();
        return response()->json(['message' => 'Servicio eliminado correctamente']);
    }

    /**
     * Alterna el estado de "mostrar en home" para un servicio.
     */
    public function toggleShowOnHome(Service $service, Request $request)
    {
        $limit = 3;
        // Verificamos el límite para que no podamos mostrar más de 3 servicios en home
        if (!$service->show_on_home) {
            $count = Service::where('show_on_home', true)->count();
            if ($count >= $limit) {
                return response()->json([
                    'message' => 'Límite alcanzado'
                ], 422);
            }
        }

        if (!$service) {
            return response()->json(['message' => 'Servicio no encontrado'], 404);
        }

        $service->show_on_home = $request->show_on_home;
        $service->save();

        return new ServiceResource($service->load('serviceType', 'images'));
    }

    public function getHomeServices()
    {
        $services = Service::where('show_on_home', true)->with('serviceType', 'images')->get();
        return new ServiceCollection($services);
    }

    private function syncPrimaryImage(Service $service, ?string $imageUrl): void
    {
        $now = Carbon::now();
        $currentPrimary = $service->images()->where('is_primary', true)->first();

        if (!$imageUrl) {
            $service->image = null;
            $service->save();

            if ($currentPrimary) {
                $service->images()->detach($currentPrimary->id);
            }

            return;
        }

        $image = Image::firstOrCreate(
            ['url' => $imageUrl],
            ['is_primary' => true]
        );

        if (!$service->images()->where('images.id', $image->id)->exists()) {
            $service->images()->attach($image->id, [
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        if ($currentPrimary && $currentPrimary->id !== $image->id) {
            $service->images()->detach($currentPrimary->id);
        }

        $service->image = $imageUrl;
        $service->save();
    }
}
