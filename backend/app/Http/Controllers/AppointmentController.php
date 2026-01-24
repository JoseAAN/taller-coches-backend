<?php

namespace App\Http\Controllers;

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
            'date' => 'required|date_format:Y-m-d',
        ]);

        //Horario laboral
        $timeStart= 8;
        $endTime = 21;

        //Horas ocupadas
        $hoursBusy = Appointment::whereDate('appointment_date', $request->date)
                            ->get()
                            ->map(function ($cita) {
                                // hora en formato H:i (ej:09:00)
                                return $cita->appointment_date->format('H:i');
                            })
                            ->toArray();

            for ($i = $timeStart; $i < $endTime; $i++) {
            // Creamos la hora ejemplo 08:00
            $timeString = Carbon::createFromTime($i, 0)->format('H:i');

            // Verificamos si esta hora está en la lista de ocupadas
            $isBusy = in_array($timeString, $hoursBusy);

            $Appointment[] = [
                'time' => $timeString,
                'status' => $isBusy ? 'ocupado' : 'disponible', //Esto es para usarlo en la vista
                'display' => $i . ':00 - ' . ($i+1) . ':00'
            ];
        }

        return response()->json([
            'date' =>$request->date,
            'CitesDay' => $Appointment
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    //Todo: Falta añadir dependiendo del servicio y del tipo de coche que sea tendra un precio u otro
    public function store(Request $request)
    {
        $validateCite = $request->validate([
            'vehicle_id' => 'required',
            'service_id' => 'required',
            'date' => 'required|date_format:Y-m-d',
            'time' => 'required|date_format:H:i'// Ej: 10:00
        ]);

        //Obtenemos la duracion del servicio
        $service = Service::findOrFail($request->service_id);
        $minutes = $service->average_duration;
        $servicePrice = $service->price;

        //Obtenemos cuando empieza y cuando deberia de acabar el servicio
        $startTime = Carbon::parse($request->date . ' ' . $request->time);
        $endTime   = $startTime->copy()->addMinutes($minutes);


        //Verificamos si ya hay una cita que choque con ese intervalo de tiempo, es decir como cada servicio tiene un tiempo estipulado diferente hay que comprobar todos los espacios de tiempo
        $conflict = Appointment::where(function ($query) use ($startTime, $endTime) {
        $query->whereBetween('appointment_date', [$startTime, $endTime])
              ->orWhereBetween('end_time', [$startTime, $endTime])
              ->orWhere(function ($q) use ($startTime, $endTime) {
                  $q->where('appointment_date', '<', $startTime)
                    ->where('end_time', '>', $endTime);
              });
        })->exists();

        if ($conflict) {
            return response()->json(['error' => 'Ese hueco ya no está disponible para la duración de este servicio'], 409);
        }

        $cite = Appointment::create([
        'vehicle_id'       => $validateCite ['vehicle_id'],
        'service_id'       => $validateCite ['service_id'],
        'appointment_date' => $startTime,
        'end_time'         => $endTime,
        'final_price'      => $precioFinal
        ]);

        return response()->json(
            $cita,
            ['message' => 'Su cita fue agendada'],
            201
        );
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
    public function destroy(string $id)
    {
        //
    }
}
