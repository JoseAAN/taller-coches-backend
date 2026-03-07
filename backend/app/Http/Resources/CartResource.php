<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\ProductsResource;

class CartResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        //Se podría personalizar para que el json quede más completo, de momento lo dejo con los campos propios de la BD
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'price' => $this->price,
            'items' => ProductsResource::collection($this->whenLoaded('products')),
        ];
    }
}
