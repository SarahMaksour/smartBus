<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BusMapResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'plate'       => $this->plate_number,
            'heading'     => (float) optional($this->location)->heading,
            'speed'       => (float) optional($this->location)->speed,
            'lat'         => (float) optional($this->location)->lat,
            'lng'         => (float) optional($this->location)->lng,
            'route'       => $this->whenLoaded('route', fn() => [
                'id'   => $this->route->id,
                'code' => $this->route->code,
                'name' => $this->route->name,
            ]),
        ];
    }
}
