<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceResource extends JsonResource
{
    private function formatImages(): array
    {
        if ($this->relationLoaded('images') && $this->images->isNotEmpty()) {
            return $this->images->map(function ($img) {
                return [
                    'id' => $img->id,
                    'url' => $img->url,
                    'is_primary' => (bool) $img->is_primary,
                ];
            })->values()->all();
        }

        if ($this->image) {
            return [[
                'id' => null,
                'url' => $this->image,
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
     * Transforma el recurso en un array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $images = $this->formatImages();

        return [
            'id' => $this->id,
            'name' => $this->name,
            'price' => $this->price,
            'average_duration_mins' => $this->average_duration,
            'description' => $this->description,
            'show_on_home' => $this->show_on_home,
            'image' => $this->resolvePrimaryImage($images),
            'images' => $images,
            'type' => [
                'id' => $this->service_type_id,
                'name' => $this->serviceType ? $this->serviceType->name : null,
            ],
        ];
    }
}
