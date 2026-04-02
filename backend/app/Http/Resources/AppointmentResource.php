<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AppointmentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'vehicle' => new VehiclesResource($this->whenLoaded('vehicle')),
            'service' => new ServiceResource($this->whenLoaded('service')),
            'appointment_date' => $this->appointment_date,
            'end_time' => $this->end_time,
            'final_price' => $this->final_price,
        ];
    }
}
