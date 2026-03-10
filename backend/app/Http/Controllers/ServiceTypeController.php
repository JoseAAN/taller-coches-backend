<?php

namespace App\Http\Controllers;

use App\Models\ServiceType;
use Illuminate\Http\Request;

class ServiceTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try{
            $vehicleTypes = ServiceType::all();
            return response()->json([
                'serviceType' => $vehicleTypes
            ]);
        }catch (\Exception $e) {
            return response()->json([
                'message' => 'Ha ocurrido un error',
                'error'   => $e->getMessage()
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
                'name' => 'required'
            ]);

            if (ServiceType::where('name', $request->name)->exists()) {
                return response()->json(['message' => 'Este tipo de servicio ya existe'], 422);
            }

            $ServiceType = ServiceType::create($data);
            return response()->json(['message' => 'ServiceType add successfully'], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Ha ocurrido un error',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function show(string $id){}

    public function update(Request $request)
    {
        try {
            $request->validate([
                'serviceTypeId' => 'required',
                'name'          => 'required|string'
            ]);

            $serviceType = ServiceType::find($request->serviceTypeId);

            if (!$serviceType) {
                return response()->json([
                    'message' => 'Tipo de servicio no encontrado'
                ], 404);
            }

            $serviceType->update(['name' => $request->name]);

            return response()->json($serviceType, 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Ha ocurrido un error',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        try {
            $request->validate([
                'serviceTypeId' => 'required',
            ]);

            $serviceType = ServiceType::find($request->serviceTypeId);

            if (!$serviceType) {
                return response()->json([
                    'message' => 'Tipo de servicio no encontrado'
                ], 404);
            }

            $serviceType->delete();

            return response()->json([
                'message' => 'Tipo de servicio eliminado correctamente'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Ha ocurrido un error',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}
