<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminNavigationItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'=> $this->id,
            'label' => $this->label,
            'icon' => $this->icon,
            'route' => $this->route,
            'is_active' => $this->is_active,
            'children' => AdminNavigationItemResource::collection($this->whenLoaded('children')),
            'order' => $this->order
        ];
    }
}
