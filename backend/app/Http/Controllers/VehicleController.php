<?php

namespace App\Http\Controllers;

use App\Http\Resources\VehiclesResource;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class VehicleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try{


        $vehicles = Vehicle::all();

        return response()->json([
            'vehicles' => $vehicles,
        ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error ocurred',
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
            'license_plate'   => 'required|string|max:20|unique:vehicles,license_plate',
            'brand'           => 'required|string|max:100',
            'model'           => 'required|string|max:100',
            'color'           => 'required|string|max:50',
            'vehicle_type_id' => 'required|exists:vehicle_types,id',
        ]);

        $data['user_id'] = $request->user()->id;

        $vehicle = Vehicle::create($data);

        // ✅ Carga la relación para que el resource pueda acceder a vehicleType
        $vehicle->load('vehicleType');

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
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
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
                    'message' => 'Vehicle not Found',
                ], 404);
            }
            $vehicle->update(['license_plate' => $request->license_plate]);
            return response()->json($vehicle);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred',
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
                    'message' => 'Vehicle not Found',
                ], 404);
            }

            $vehicle->delete();

            return response()->json([
                'message' => 'Vehicle deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
