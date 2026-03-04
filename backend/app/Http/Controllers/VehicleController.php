<?php

namespace App\Http\Controllers;

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
        try{
            $data = $request->validate([
                'name' => 'required',
            ]);

            if(Vehicle::where('name', $request->name)->exists()){
                return response()->json(['message'=> 'This Vehicle already exists'], 422);
            } else {
                $vehicle = Vehicle::create($data);

                return response()->json(['message'=> 'Vehicle added succesfully'], 201);
            }

            } catch(\Exception $e){
                return response()->json([
                    'message'=> 'An error ocurred',
                    'error' => $e->getMessage(),
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
