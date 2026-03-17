<?php

namespace App\Http\Resources;

use App\Http\Resources\ProductsResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartInvoiceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'InvoiceId' => $this->id,
            'invoiceNumber' => $this->invoice_number,
            'total' => $this->total,
            'cartId' => $this->cart_id,
            'products' => $this->whenLoaded('cart', function () {
                return $this->cart->items->map(function ($item) {
                    $target = $item->target;
                    return [
                        'id' => $target?->id,
                        'name' => $target?->name ?? 'Producto desconocido',
                        'price' => $item->price_at_time,
                        'categories' => ($item->item_type_id == \App\Models\ItemType::PRODUCT && $target)
                                        ? $target->categories->pluck('name') 
                                        : [$item->type->name],
                        'quantity' => $item->quantity,
                    ];
                });
            }),
        ];
    }
}
