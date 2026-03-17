<?php

namespace App\Http\Resources;

use App\Models\ItemType;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $target = $this->target;

        return [
            'id' => $this->id,
            'type' => $this->type->name,
            'quantity' => $this->quantity,
            'price_at_time' => $this->price_at_time,
            'subtotal' => $this->subtotal,
            'details' => [
                'id' => $target->id,
                'name' => $target->name,
                'description' => $target->description,
                // Si es un producto, añadimos stock e imágenes
                'stock' => $this->item_type_id == ItemType::PRODUCT ? $target->stock : null,
            ],
        ];
    }
}
