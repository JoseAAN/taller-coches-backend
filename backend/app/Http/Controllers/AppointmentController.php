<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Service;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Citas", description: "Gestión de citas y disponibilidad del taller")]
class AppointmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    #[OA\Get(
        path: "/api/appointments",
        summary: "Consultar disponibilidad de huecos",
        description: "Calcula los intervalos de tiempo libres para un servicio basándose en su duración y las citas ya existentes.",
        tags: ["Citas"],
        parameters: [
            new OA\Parameter(
                name: "service_id",
                in: "query",
                description: "ID del servicio para obtener su duración media",
                required: true,
                schema: new OA\Schema(type: "integer")
            ),
            new OA\Parameter(
                name: "date",
                in: "query",
                description: "Fecha a consultar (formato YYYY-MM-DD)",
                required: true,
                schema: new OA\Schema(type: "string", format: "date")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Lista de huecos disponibles",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "date", type: "string", example: "2026-05-15"),
                        new OA\Property(property: "service_duration", type: "integer", example: 45),
                        new OA\Property(property: "available_slots", type: "array", items: new OA\Items(
                            properties: [
                                new OA\Property(property: "start", type: "string", example: "09:00"),
                                new OA\Property(property: "end", type: "string", example: "09:45")
                            ]
                        ))
                    ]
                )
            ),
            new OA\Response(response: 422, description: "Error de validación en los parámetros")
        ]
    )]
    public function index(Request $request)
    {
        $request->validate([
            'service_id' => 'required',
            'date' => 'required|date_format:Y-m-d',
        ]);

        $service = Service::findOrFail($request->service_id);
        $durationMinutes = $service->average_duration;

        $workStart = Carbon::parse($request->date . ' 08:00:00');
        $workEnd = Carbon::parse($request->date . ' 21:00:00');
        $stepMinutes = 10;

        $appointments = Appointment::whereDate('appointment_date', $request->date)
            ->get()
            ->map(function ($appointment) {
                return [
                    'start' => Carbon::parse($appointment->appointment_date),
                    'end' => Carbon::parse($appointment->end_time),
                ];
            });

        $availableSlots = [];
        $current = $workStart;

        while ($current->copy()->addMinutes($durationMinutes)->lte($workEnd)) {
            $slotStart = $current->copy();
            $slotEnd = $current->copy()->addMinutes($durationMinutes);

            $overlaps = false;
            foreach ($appointments as $appointment) {
                if ($slotStart->lt($appointment['end']) && $slotEnd->gt($appointment['start'])) {
                    $overlaps = true;
                    break;
                }
            }

            if (! $overlaps) {
                $availableSlots[] = [
                    'start' => $slotStart->format('H:i'),
                    'end' => $slotEnd->format('H:i'),
                ];
            }
            $current->addMinutes($stepMinutes);
        }

        return response()->json([
            'date' => $request->date,
            'service_duration' => $durationMinutes,
            'available_slots' => $availableSlots,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    #[OA\Post(
        path: "/api/appointments",
        summary: "Registrar una nueva cita",
        tags: ["Citas"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["vehicle_id", "service_id", "date", "start_time"],
                properties: [
                    new OA\Property(property: "vehicle_id", type: "integer", example: 1),
                    new OA\Property(property: "service_id", type: "integer", example: 3),
                    new OA\Property(property: "date", type: "string", format: "date", example: "2026-05-15"),
                    new OA\Property(property: "start_time", type: "string", example: "10:30")
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Cita creada correctamente"),
            new OA\Response(response: 409, description: "El horario ya está ocupado"),
            new OA\Response(response: 500, description: "Error interno")
        ]
    )]
    public function store(Request $request)
    {
        $request->validate([
            'vehicle_id' => 'required',
            'service_id' => 'required',
            'date' => 'required|date_format:Y-m-d',
            'start_time' => 'required|date_format:H:i',
        ]);

        try {
            $service = Service::findOrFail($request->service_id);
            $durationMinutes = $service->average_duration;

            $start = Carbon::parse($request->date . ' ' . $request->start_time);
            $end = $start->copy()->addMinutes($durationMinutes);

            $exists = Appointment::where(function ($q) use ($start, $end) {
                $q->where('appointment_date', '<', $end)
                    ->where('end_time', '>', $start);
            })->exists();

            if ($exists) {
                return response()->json(['message' => 'Ese horario ya no está disponible'], 409);
            }

            $appointment = Appointment::create([
                'vehicle_id' => $request->vehicle_id,
                'service_id' => $service->id,
                'appointment_date' => $start,
                'end_time' => $end,
                'final_price' => $service->price,
            ]);

            return response()->json([
                'message' => 'Cita creada correctamente',
                'appointment' => $appointment,
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error inesperado al crear la cita'], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    #[OA\Get(
        path: "/api/appointments/{id}",
        summary: "Obtener detalle de una cita específica",
        tags: ["Citas"],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Detalle de la cita"),
            new OA\Response(response: 404, description: "Cita no encontrada")
        ]
    )]
    public function show(int $appointmentId)
    {
        try {
            $appointment = Appointment::with([
                'vehicle.vehicleType',
                'vehicle.user',
            ])->findOrFail($appointmentId);

            return response()->json([
                'appointmentId' => $appointment->id,
                'license_plate' => $appointment->vehicle->license_plate,
                'vehicleType' => $appointment->vehicle->vehicleType->name,
                'user_name' => $appointment->vehicle->user->name,
                'appointment_start' => $appointment->appointment_date,
                'appointment_finish' => $appointment->end_time,
                'price' => $appointment->final_price,
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'No se pudo encontrar la cita'], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    #[OA\Put(
        path: "/api/appointments/{id}",
        summary: "Actualizar una cita",
        tags: ["Citas"],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "vehicle_id", type: "integer"),
                    new OA\Property(property: "service_id", type: "integer"),
                    new OA\Property(property: "date", type: "string", format: "date"),
                    new OA\Property(property: "start_time", type: "string")
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Cita actualizada"),
            new OA\Response(response: 409, description: "Conflicto de horario"),
            new OA\Response(response: 422, description: "Horario fuera de jornada laboral")
        ]
    )]
    public function update(Request $request, int $appointmentId)
    {
        $request->validate([
            'vehicle_id' => 'required',
            'service_id' => 'required',
            'date' => 'required|date_format:Y-m-d',
            'start_time' => 'required|date_format:H:i',
        ]);

        try {
            $appointment = Appointment::findOrFail($appointmentId);
            $service = Service::findOrFail($request->service_id);

            $start = Carbon::parse($request->date . ' ' . $request->start_time);
            $end = $start->copy()->addMinutes($service->average_duration);

            $workStart = Carbon::parse($request->date . ' 08:00:00');
            $workEnd = Carbon::parse($request->date . ' 21:00:00');

            if ($start->lt($workStart) || $end->gt($workEnd)) {
                return response()->json(['message' => 'El horario debe estar entre las 08:00 y las 21:00'], 422);
            }

            $exists = Appointment::where('id', '!=', $appointmentId)
                ->where('appointment_date', '<', $end)
                ->where('end_time', '>', $start)
                ->exists();

            if ($exists) {
                return response()->json(['message' => 'El nuevo horario ya está ocupado'], 409);
            }

            $appointment->update([
                'vehicle_id' => $request->vehicle_id,
                'service_id' => $service->id,
                'appointment_date' => $start,
                'end_time' => $end,
                'final_price' => $service->price,
            ]);

            return response()->json([
                'message' => 'Cita actualizada con éxito',
                'appointment' => $appointment,
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'No se pudo actualizar la cita'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    #[OA\Delete(
        path: "/api/appointments/{id}",
        summary: "Eliminar una cita del sistema",
        tags: ["Citas"],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Cita eliminada correctamente"),
            new OA\Response(response: 404, description: "Cita no encontrada")
        ]
    )]
    public function destroy(int $appointmentId)
    {
        try {
            $appointment = Appointment::findOrFail($appointmentId);
            $appointment->delete();

            return response()->json(['message' => 'Éxito'], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'ID de cita no encontrado'], 404);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error general'], 500);
        }
    }
}
