<?php

namespace App\Http\Controllers;

use App\Models\ServiceType;
use Illuminate\Http\Request;

class ServiceTypeController extends Controller
{
    private function serverErrorResponse(string $message = 'Ha ocurrido un error')
    {
        return response()->json([
            'message' => $message,
        ], 500);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $serviceTypes = ServiceType::all();

            return response()->json([
                'serviceType' => $serviceTypes,
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
                'name' => 'required',
            ]);

            if (ServiceType::where('name', $request->name)->exists()) {
                return response()->json(['message' => 'Este tipo de servicio ya existe'], 422);
            }

            $serviceType = ServiceType::create($data);

            return response()->json($serviceType, 201);
        } catch (\Exception $e) {
            return $this->serverErrorResponse();
        }
    }

    public function show(string $id)
    {
        try {
            $serviceType = ServiceType::find($id);

            if (!$serviceType) {
                return response()->json([
                    'message' => 'Tipo de servicio no encontrado',
                ], 404);
            }

            return response()->json($serviceType, 200);
        } catch (\Exception $e) {
            return $this->serverErrorResponse();
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $serviceType = ServiceType::find($id);

            if (!$serviceType) {
                return response()->json([
                    'message' => 'Tipo de servicio no encontrado',
                ], 404);
            }

            $data = $request->validate([
                'name' => 'required|string|max:255',
            ]);

            $serviceType->update($data);

            return response()->json($serviceType, 200);
        } catch (\Exception $e) {
            return $this->serverErrorResponse();
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
                    'message' => 'Tipo de servicio no encontrado',
                ], 404);
            }

            $serviceType->delete();

            return response()->json([
                'message' => 'Tipo de servicio eliminado correctamente',
            ], 200);
        } catch (\Exception $e) {
            return $this->serverErrorResponse();
        }
    }
}
