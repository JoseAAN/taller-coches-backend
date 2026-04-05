<?php

namespace App\Http\Controllers;

use App\Http\Resources\VehiclesResource;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class VehicleController extends Controller
{
    private function serverErrorResponse(string $message = 'Ha ocurrido un error')
    {
        return response()->json([
            'message' => $message,
        ], 500);
    }

    /**
     * Listar vehiculos.
     * - Admin: ve todos los vehiculos.
     * - Usuario autenticado: ve solo sus propios vehiculos.
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
            return $this->serverErrorResponse();
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'license_plate' => 'required|string|max:20',
                'brand' => 'required|string|max:100',
                'model' => 'required|string|max:100',
                'color' => 'required|string|max:50',
                'vehicle_type_id' => 'required|exists:vehicle_types,id',
                'user_id' => 'sometimes|nullable|exists:users,id',
            ]);

            $user = $request->user();

            if ($user->role->name === 'admin' && $request->filled('user_id')) {
                $data['user_id'] = $request->input('user_id');
            } else {
                $data['user_id'] = $user->id;
            }

            $vehicle = Vehicle::create($data);
            $vehicle->load(['vehicleType', 'user']);

            return response()->json([
                'success' => true,
                'message' => 'Vehiculo anadido correctamente',
                'vehicle' => new VehiclesResource($vehicle),
            ], 201);
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Error al crear el vehiculo');
        }
    }

    /**
     * Mostrar un vehiculo especifico por ID.
     * - Admin: puede ver cualquier vehiculo.
     * - Usuario autenticado: solo puede ver sus propios vehiculos.
     */
    public function show(Request $request, string $id)
    {
        try {
            $vehicle = Vehicle::with(['vehicleType', 'user'])->find($id);

            if (!$vehicle) {
                return response()->json([
                    'message' => 'Vehiculo no encontrado',
                ], 404);
            }

            $user = $request->user();
            if ($user->role->name !== 'admin' && $vehicle->user_id !== $user->id) {
                return response()->json([
                    'message' => 'No tienes permiso para ver este vehiculo',
                ], 403);
            }

            return response()->json([
                'data' => $vehicle,
            ]);
        } catch (\Exception $e) {
            return $this->serverErrorResponse();
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $vehicle = Vehicle::find($id);

            if (!$vehicle) {
                return response()->json([
                    'message' => 'Vehiculo no encontrado',
                ], 404);
            }

            $data = $request->validate([
                'license_plate' => 'sometimes|required|string|max:20',
                'brand' => 'sometimes|required|string|max:100',
                'model' => 'sometimes|required|string|max:100',
                'color' => 'sometimes|required|string|max:50',
                'vehicle_type_id' => 'sometimes|required|exists:vehicle_types,id',
                'user_id' => 'sometimes|nullable|exists:users,id',
            ]);

            if ($request->filled('user_id')) {
                $data['user_id'] = $request->input('user_id');
            }

            $vehicle->update($data);
            $vehicle->load(['vehicleType', 'user']);

            return response()->json([
                'message' => 'Vehiculo actualizado correctamente',
                'data' => $vehicle,
            ]);
        } catch (\Exception $e) {
            return $this->serverErrorResponse();
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
                    'message' => 'Vehiculo no encontrado',
                ], 404);
            }

            $user = $request->user();
            if ($user->role->name !== 'admin' && $vehicle->user_id !== $user->id) {
                return response()->json([
                    'message' => 'No tienes permiso para eliminar este vehiculo',
                ], 403);
            }

            $vehicle->delete();

            return response()->json([
                'message' => 'Vehiculo eliminado correctamente',
            ]);
        } catch (\Exception $e) {
            return $this->serverErrorResponse();
        }
    }
}
