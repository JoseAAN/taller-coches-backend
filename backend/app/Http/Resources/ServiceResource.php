<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceResource extends JsonResource
{
    /**
     * Transforma el recurso en un array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'price' => $this->price,
            'average_duration_mins' => $this->average_duration,
            'description' => $this->description,
            'show_on_home' => $this->show_on_home,
            'type' => [
                'id' => $this->service_type_id,
                'name' => $this->serviceType ? $this->serviceType->name : null,
            ],
        ];
    }
}
