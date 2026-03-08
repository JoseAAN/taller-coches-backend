<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\VehiclesTypeResource;
class VehiclesResource extends JsonResource
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
            'license_plate' => $this->license_plate,
            'color' => $this->color,
            'model' => $this->model,
            'brand' => $this->brand,
            'vehicle_type' => new VehiclesTypeResource($this->whenLoaded('vehicleType'))
        ];
    }
}
