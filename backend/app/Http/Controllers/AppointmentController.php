<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class AppointmentController extends Controller
{
    /**
     *
     *
     * Display a listing of the resource.
     */

    public function index(Request $request)
    {
        $request->validate([
            'service_id' => 'required',
            'date' => 'required|date_format:Y-m-d',
        ]);

        //Duracion del Servicio Solicitado
        $service = Service::findOrFail($request->service_id);
        $durationMinutes = $service->average_duration;

        //Cogemos el Dia solicitado y desde las 8am hasta las 9pm
        $workStart = Carbon::parse($request->date . ' 08:00:00');
        $workEnd   = Carbon::parse($request->date . ' 21:00:00');

        //Intervalo de tiempo por si se atrasa un servicio
        $stepMinutes = 10;

        //Calculamos las horas que tenemos ocupadas en ese dia
        $appointments = Appointment::whereDate('appointment_date', $request->date)
            ->get()
            ->map(function ($appointment) {
                return [
                    'start' => Carbon::parse($appointment->appointment_date),
                    'end'   => Carbon::parse($appointment->end_time),
                ];
            });

        $availableSlots = [];

        //Inicio de la jornada laboral
        $current = $workStart;

        // Mientras el servicio es decir (inicio + duración del servicio)
        // no se pase de la hora de cierre 21:00
        while ($current->copy()->addMinutes($durationMinutes)->lte($workEnd)) {

            //La hora que de inicio del servicio y la hora que acabaria el servicio
            $slotStart = $current->copy();
            $slotEnd   = $current->copy()->addMinutes($durationMinutes);

            $overlaps = false;
            //Recorremos todas las citas de ese dia y comprobemoas si hay citas que choquen con el intervalo es decir
            //si hay citas que empiecen antes de que acabe el servicio o que acaben despues de que empiece el servicio
            foreach ($appointments as $appointment) {
                if ($slotStart->lt($appointment['end']) && $slotEnd->gt($appointment['start'])
                ) {
                    $overlaps = true;
                    break;
                }
            }

            if (! $overlaps) {
                //Guardamos los huecos disponibles
                $availableSlots[] = [
                'start' => $slotStart->format('H:i'),
                'end'   => $slotEnd->format('H:i'),
                ];
            }

            //Sumo 10 minutos para comprobar el siguiente hueco
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
    public function store(Request $request)
    {
        $validateCite = $request->validate([
            'vehicle_id' => 'required',
            'service_id' => 'required',
            'date' => 'required|date_format:Y-m-d',
            'start_time' => 'required|date_format:H:i'// Ej: 10:00
        ]);

        //Obtenemos la duracion del servicio
        $service = Service::findOrFail($request->service_id);
        $durationMinutes = $service->average_duration;

        // Construimos DateTime completos el dia y la hora de inicio
        $start = Carbon::parse(
            $request->appointment_date . ' ' . $request->start_time
        );

        $end = $start->copy()->addMinutes($durationMinutes);
        // Comprobamos solapamiento
        $exists = Appointment::where(function ($q) use ($start, $end) {
            $q->where('appointment_date', '<', $end)
            ->where('end_time', '>', $start);
        })->exists();

        if ($exists) {
            return response()->json([
                'message' => 'Ese horario ya no está disponible'
            ], 409);
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
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Appointment $appointment)
    {
        $appointment->delete();
        return response()->json([
            'message' => 'Cita eliminada con éxito'
        ]);
    }
}
