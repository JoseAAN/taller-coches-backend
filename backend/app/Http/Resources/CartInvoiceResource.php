<?php

namespace App\Http\Resources;

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
            'cartId' => $this->cart_id
        ];
    }
}
