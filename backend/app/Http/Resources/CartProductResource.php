<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'name'             => $this->name,
            'description'      => $this->description,
            'categories'       => $this->categories->pluck('name'),
            'quantity'         => $this->pivot->quantity,
            'priceInTime'      => $this->pivot->priceInTime,
            'totalPerProduct'  => $this->pivot->totalPerProduct,
            'stock'            => $this->stock,
        ];
    }
}
