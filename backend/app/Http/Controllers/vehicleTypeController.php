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
        $vehicleTypes = VehicleType::all();
        return response()->json([
            'vehiclesTypes' => $vehicleTypes
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required'
        ]);

       if (VehicleType::where('name', $request->name)->exists()) {
            return response()->json(['message' => 'This type of vehicle already exists'], 422);
        }else{
            $vehicleTypes = VehicleType::create($data);
            return response()->json(['message' => 'Product add successfully']);
        }
        return response()->json(['message' => 'error']);
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
    public function update(Request $request)
    {
        $request->validate([
        'vehiclesTypesId' => 'required',
        'name' => 'required|string'
        ]);

        $vehicleType = VehicleType::find($request->vehiclesTypesId);
        if (!$vehicleType) {
            return response()->json([
                'message' => 'Vehicle type not Found'
            ], 404);
        }

        $vehicleType->update(['name' => $request->name]);

        return response()->json($vehicleType);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        $request->validate([
            'vehiclesTypesId' => 'required',
        ]);

        $vehicleType = VehicleType::find($request->vehiclesTypesId);

        if (!$vehicleType) {
            return response()->json([
                'message' => 'Vehicle type NotFound'
            ], 404);
        }

        $vehicleType->delete();

        return response()->json([
            'message' => 'Vehicle type delete'
        ]);
    }
}
