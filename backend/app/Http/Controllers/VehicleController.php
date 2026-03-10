<?php

namespace App\Http\Controllers;

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
        try{
            $data = $request->validate([
                'name' => 'required',
            ]);

            if(Vehicle::where('name', $request->name)->exists()){
                return response()->json(['message'=> 'Este vehículo ya existe'], 422);
            } else {
                $vehicle = Vehicle::create($data);

                return response()->json(['message'=> 'Vehículo añadido correctamente'], 201);
            }

            } catch(\Exception $e){
                return response()->json([
                    'message'=> 'Ha ocurrido un error',
                    'error' => $e->getMessage(),
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
            $request->validate([
                'id'=>'required',
                'license_plate'=>'required'
            ]);

            $vehicle = Vehicle::find($request->id);

            if (! $vehicle) {
                return response()->json([
                    'message' => 'Vehículo no encontrado',
                ], 404);
            }
            $vehicle->update(['license_plate' => $request->license_plate]);
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
    public function destroy(string $id)
    {
        try {
            $vehicle = Vehicle::find($id);

            if (!$vehicle) {
                return response()->json([
                    'message' => 'Vehículo no encontrado',
                ], 404);
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
