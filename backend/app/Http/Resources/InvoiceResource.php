<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceResource extends JsonResource
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
            'invoice_number' => $this->invoice_number,
            // Soporte para frontend viejo (checkout-success esperaba invoice_number en snake_case pero luego lo busqué por camelCase en perfil)
            'invoiceNumber' => $this->invoice_number, 
            'total' => $this->total,
            'cart_id' => $this->cart_id,
            'appointment_id' => $this->appointment_id,
            'user_id' => $this->user_id,
            'created_at' => $this->created_at?->format('d/m/Y H:i'),
            'updated_at' => $this->updated_at?->format('d/m/Y H:i'),
            
            // Detalles del carrito (si existe)
            'cart' => new CartResource($this->whenLoaded('cart')),
            
            // Detalles de la cita (si existe)
            'appointment' => new AppointmentResource($this->whenLoaded('appointment')),
            
            // Para compatibilidad con el perfil ("products")
            'products' => $this->whenLoaded('cart', function () {
                return $this->cart->items->map(function ($item) {
                    $target = $item->target;
                    return [
                        'id' => $target?->id,
                        'name' => $target?->name ?? 'Concepto desconocido',
                        'price' => $item->price_at_time,
                        'categories' => ($item->item_type_id == \App\Models\ItemType::PRODUCT && $target)
                                        ? $target->categories->pluck('name') 
                                        : [$item->type->name],
                        'quantity' => $item->quantity,
                        'subtotal' => $item->subtotal,
                    ];
                });
            }),
        ];
    }
}
