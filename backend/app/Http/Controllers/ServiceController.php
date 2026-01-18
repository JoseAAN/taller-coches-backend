<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;
use App\Http\Resources\ServiceResource;
use App\Http\Resources\ServiceCollection;

class ServiceController extends Controller
{
    /**
     * Muestra un listado de los recursos.
     */
    public function index(Request $request)
    {
        $query = Service::with('serviceType');

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
        ]);

        $service = Service::create($validatedData);

        return new ServiceResource($service->load('serviceType'));
    }

    /**
     * Muestra el recurso especificado.
     */
    public function show(Service $service)
    {
        return new ServiceResource($service->load('serviceType'));
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
        ]);

        $service->update($validatedData);

        return new ServiceResource($service->load('serviceType'));
    }

    /**
     * Elimina el recurso especificado de la base de datos.
     */
    public function destroy(Service $service)
    {
        $service->delete();
        return response()->json(['message' => 'Servicio eliminado correctamente']);
    }
}
