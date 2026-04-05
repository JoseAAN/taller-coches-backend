<?php

namespace App\Http\Resources;

use App\Models\ItemType;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ItemResource extends JsonResource
{
    private function formatServiceImages($service): array
    {
        if (!$service) {
            return [];
        }

        if ($service->relationLoaded('images') && $service->images->isNotEmpty()) {
            return $service->images->map(function ($img) {
                return [
                    'id' => $img->id,
                    'url' => $img->url,
                    'is_primary' => (bool) $img->is_primary,
                ];
            })->values()->all();
        }

        if ($service->image) {
            return [[
                'id' => null,
                'url' => $service->image,
                'is_primary' => true,
            ]];
        }

        return [];
    }

    private function resolvePrimaryImage(array $images): ?string
    {
        if (empty($images)) {
            return null;
        }

        $primary = collect($images)->firstWhere('is_primary', true) ?? $images[0];

        return $primary['url'] ?? null;
    }

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $target = $this->target;
        $service = $this->item_type_id == ItemType::SERVICE ? $target?->service : null;
        $serviceImages = $this->formatServiceImages($service);
        $detailImages = $this->item_type_id == ItemType::SERVICE ? $serviceImages : ($target?->images ?? []);

        if (!$target) {
            return [
                'id' => $this->id,
                'type' => $this->type->name ?? null,
                'quantity' => $this->quantity,
                'price_at_time' => $this->price_at_time,
                'subtotal' => $this->subtotal,
                'details' => null,
            ];
        }

        return [
            'id' => $this->id,
            'type' => $this->type->name,
            'quantity' => $this->quantity,
            'price_at_time' => (string) $this->price_at_time,
            'subtotal' => (string) $this->subtotal,
            'details' => [
                'id' => $target->id,
                'service_id' => $service?->id,
                'name' => $this->item_type_id == ItemType::PRODUCT ? $target->name : ($target->service->name ?? 'Servicio'),
                'description' => $this->item_type_id == ItemType::PRODUCT ? $target->description : ($target->service->description ?? ''),
                'stock' => $this->item_type_id == ItemType::PRODUCT ? $target->stock : null,
                'image' => $this->item_type_id == ItemType::SERVICE ? $this->resolvePrimaryImage($serviceImages) : null,
                'images' => $detailImages,
            ],
        ];
    }
}
