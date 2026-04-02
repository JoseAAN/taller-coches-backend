<?php

namespace App\Http\Controllers;

use App\Http\Resources\VehiclesResource;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class VehicleController extends Controller
{
    /**
     * Listar vehículos.
     * - Admin: ve todos los vehículos.
     * - Usuario autenticado: ve solo sus propios vehículos.
     */
    public function index(Request $request)
    {
        try {
            $user = $request->user();

            if ($user->role->name === 'admin') {
                $vehicles = Vehicle::with(['vehicleType', 'user'])->get();
            } else {
                $vehicles = Vehicle::with(['vehicleType'])
                    ->where('user_id', $user->id)
                    ->get();
            }

            return response()->json([
                'data' => $vehicles,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Ha ocurrido un error',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
{
    try {
        $data = $request->validate([
            'license_plate'   => 'required|string|max:20',
            'brand'           => 'required|string|max:100',
            'model'           => 'required|string|max:100',
            'color'           => 'required|string|max:50',
            'vehicle_type_id' => 'required|exists:vehicle_types,id',
            'user_id'         => 'sometimes|nullable|exists:users,id'
        ]);

        $user = $request->user();

        // Si es admin y envía user_id, usa ese. Si no, usa el del admin o cliente actual.
        if ($user->role->name === 'admin' && $request->has('user_id') && $request->filled('user_id')) {
            $data['user_id'] = $request->input('user_id');
        } else {
            $data['user_id'] = $user->id;
        }

        $vehicle = Vehicle::create($data);

        // Carga la relación para que el resource pueda acceder a vehicleType y user
        $vehicle->load(['vehicleType', 'user']);

        return response()->json([
            'success' => true,
            'message' => 'Vehículo añadido correctamente',
            'vehicle' => new VehiclesResource($vehicle) 
        ], 201);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error al crear el vehículo',
            'error'   => $e->getMessage()
        ], 500);
    }
}

    /**
     * Mostrar un vehículo específico por ID.
     * - Admin: puede ver cualquier vehículo.
     * - Usuario autenticado: solo puede ver sus propios vehículos.
     */
    public function show(Request $request, string $id)
    {
        try {
            $vehicle = Vehicle::with(['vehicleType', 'user'])->find($id);

            if (!$vehicle) {
                return response()->json([
                    'message' => 'Vehículo no encontrado',
                ], 404);
            }

            $user = $request->user();
            if ($user->role->name !== 'admin' && $vehicle->user_id !== $user->id) {
                return response()->json([
                    'message' => 'No tienes permiso para ver este vehículo',
                ], 403);
            }

            return response()->json(['data' => $vehicle]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Ha ocurrido un error',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $vehicle = Vehicle::find($id);

            if (! $vehicle) {
                return response()->json([
                    'message' => 'Vehículo no encontrado',
                ], 404);
            }

            $user = $request->user();
            if ($user->role->name !== 'admin' && $vehicle->user_id !== $user->id) {
                return response()->json([
                    'message' => 'No tienes permiso para actualizar este vehículo',
                ], 403);
            }

            $data = $request->validate([
                'license_plate'   => 'sometimes|required|string|max:20',
                'brand'           => 'sometimes|required|string|max:100',
                'model'           => 'sometimes|required|string|max:100',
                'color'           => 'sometimes|required|string|max:50',
                'vehicle_type_id' => 'sometimes|required|exists:vehicle_types,id',
                'user_id'         => 'sometimes|nullable|exists:users,id'
            ]);

            if ($user->role->name === 'admin' && $request->has('user_id') && $request->filled('user_id')) {
                $data['user_id'] = $request->input('user_id');
            } else {
                // If not admin, strictly ignore the user_id that might be in $data implicitly
                unset($data['user_id']); 
            }

            $vehicle->update($data);
            $vehicle->load(['vehicleType', 'user']);

            return response()->json($vehicle);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Ha ocurrido un error',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        try {
            $vehicle = Vehicle::find($id);

            if (!$vehicle) {
                return response()->json([
                    'message' => 'Vehículo no encontrado',
                ], 404);
            }

            $user = $request->user();
            if ($user->role->name !== 'admin' && $vehicle->user_id !== $user->id) {
                return response()->json([
                    'message' => 'No tienes permiso para eliminar este vehículo',
                ], 403);
            }

            $vehicle->delete();

            return response()->json([
                'message' => 'Vehículo eliminado correctamente',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Ha ocurrido un error',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
