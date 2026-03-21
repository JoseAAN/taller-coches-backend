<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Resources\AppointmentResource;
use App\Http\Resources\InvoiceResource;
use App\Http\Resources\UserResource;
use App\Http\Resources\VehiclesResource;
use App\Models\Appointment;
use App\Models\Cart;
use App\Models\Invoice;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request)
    {
        $userId = $request->user()->id;


        $user = User::with('role')->find($userId);
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario no encontrado.'
            ], 404);
        }
       
        //if($user->role->name == 'client'){
            $vehicles = Vehicle::with('vehicleType')->where('user_id', $user->id)->get();
            
            // Modelo Factura
            $Invoice = Invoice::with([
                'cart.items.itemProduct.product.categories',
                'cart.items.itemAppointment.appointment.vehicle',
                'cart.items.itemAppointment.appointment.service',
                'cart.items.itemAppointment.appointment.user',
            ])
            ->where('user_id', $userId)
            ->get();

            $appointments = Appointment::whereHas('vehicle', function($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->where('appointment_date', '>=', now())
            ->with('vehicle', 'service')
            ->get();

           return response()->json([
            'success' => true,
            'User' => new UserResource($user),
            'Invoices' => InvoiceResource::collection($Invoice),
            'vehicles' => VehiclesResource::collection($vehicles),
            'Appointments' => AppointmentResource::collection($appointments)
        ]);
      /*   }else{
            return response()->json([
                'success' => false,
                'message' => 'No tienes permisos.'
            ], 404);
        } */


    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
{
    $user = $request->user();

    $validatedData = $request->validate([
        'name'    => 'sometimes|string|max:255',
        'email'   => 'sometimes|email|unique:users,email,' . $user->id,
        'dni'     => 'sometimes|nullable|string|max:20|unique:users,dni,' . $user->id,
        'phone'   => 'sometimes|nullable|string|max:20',
        'address' => 'sometimes|nullable|string|max:255',
    ]);

    $user->update($validatedData);

    return response()->json([
        'success' => true,
        'message' => 'Perfil actualizado correctamente.',
        'User'    => new UserResource($user)
    ]);
}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
