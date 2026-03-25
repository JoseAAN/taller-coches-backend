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

            $serviceType = ServiceType::create($data);
            return response()->json($serviceType, 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Ha ocurrido un error',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function show(string $id){
        try {
            $serviceType = ServiceType::find($id);

            if (!$serviceType) {
                return response()->json([
                    'message' => 'Tipo de servicio no encontrado'
                ], 404);
            }

            return response()->json($serviceType, 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Ha ocurrido un error',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $serviceType = ServiceType::find($id);

            if (!$serviceType) {
                return response()->json([
                    'message' => 'Tipo de servicio no encontrado'
                ], 404);
            }

            $data = $request->validate([
                'name' => 'required|string|max:255'
            ]);

            $serviceType->update($data);

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
    public function destroy(Request $request, $id)
    {
        try {
            $serviceType = ServiceType::find($id);

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
