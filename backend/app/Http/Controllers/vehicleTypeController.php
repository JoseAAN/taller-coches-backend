<?php

namespace App\Http\Controllers;

use App\Models\VehicleType;
use Illuminate\Http\Request;

class vehicleTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $vehicleTypes = VehicleType::all();

            return response()->json([
                'vehiclesTypes' => $vehicleTypes,
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
                'name' => 'required',
            ]);

            if (VehicleType::where('name', $request->name)->exists()) {
                return response()->json(['message' => 'Este tipo de vehículo ya existe'], 422);
            } else {
                $vehicleTypes = VehicleType::create($data);

                return response()->json(['message' => 'vehicleType add successfully'], 201);
            }
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Ha ocurrido un error',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show(string $id) {}

    public function update(Request $request)
    {
        try {
            $request->validate([
                'vehiclesTypesId' => 'required',
                'name' => 'required|string',
            ]);

            $vehicleType = VehicleType::find($request->vehiclesTypesId);

            if (! $vehicleType) {
                return response()->json([
                    'message' => 'Vehicle type not Found',
                ], 404);
            }

            $vehicleType->update(['name' => $request->name]);

            return response()->json($vehicleType);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Ha ocurrido un error',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(Request $request)
    {
        try {
            $request->validate([
                'vehiclesTypesId' => 'required',
            ]);

            $vehicleType = VehicleType::find($request->vehiclesTypesId);

            if (! $vehicleType) {
                return response()->json([
                    'message' => 'Vehicle type NotFound',
                ], 404);
            }

            $vehicleType->delete();

            return response()->json([
                'message' => 'Tipo de vehículo eliminado correctamente',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
