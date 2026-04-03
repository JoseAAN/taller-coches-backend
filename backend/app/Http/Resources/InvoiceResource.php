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
            'id'             => $this->id,
            'invoice_number' => $this->invoice_number,
            'invoiceNumber'  => $this->invoice_number,
            'total'          => $this->total,
            'cart_id'        => $this->cart_id,
            'user_id'        => $this->user_id,
            'user_name'      => $this->whenLoaded('user', fn() => $this->user->name),
            'created_at'     => $this->created_at?->format('d/m/Y H:i'),
            'updated_at'     => $this->updated_at?->format('d/m/Y H:i'),

            'items' => $this->whenLoaded('cart', function () {
                return $this->cart->items->map(function ($item) {

                    // Es un PRODUCTO
                    if ($item->itemProduct && $item->itemProduct->product) {
                        $product = $item->itemProduct->product;
                        return [
                            'type'       => 'product',
                            'id'         => $product->id,
                            'name'       => $product->name,
                            'categories' => $product->categories->pluck('name'),
                            'price'      => $item->price_at_time,
                            'quantity'   => $item->quantity,
                            'subtotal'   => $item->subtotal,
                        ];
                    }

                    // Es una CITA
                    if ($item->itemAppointment && $item->itemAppointment->appointment) {
                        $appointment = $item->itemAppointment->appointment;
                        return [
                            'type'             => 'appointment',
                            'id'               => $appointment->id,
                            'service'          => $appointment->service?->name,
                            'appointment_date' => $appointment->appointment_date?->format('d/m/Y H:i'),
                            'vehicle'          => $appointment->vehicle?->license_plate ?? $appointment->vehicle?->model,
                            'price'            => $item->price_at_time,
                            'quantity'         => $item->quantity,
                            'subtotal'         => $item->subtotal,
                        ];
                    }

                    // Fallback
                    return [
                        'type'     => 'unknown',
                        'price'    => $item->price_at_time,
                        'quantity' => $item->quantity,
                        'subtotal' => $item->subtotal,
                    ];
                });
            }),
        ];
    }
}
