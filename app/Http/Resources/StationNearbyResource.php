<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StationNearbyResource extends JsonResource
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
            'lat'              => (float) $this->lat,
            'lng'              => (float) $this->lng,
            'distance_meters'  => (int) $this->distance_meters,
            'minutes_away'     => (int) $this->minutes_away,
            'routes'           => $this->whenLoaded('routes', fn() =>
                $this->routes->map(fn($r) => [
                    'id'   => $r->id,
                    'code' => $r->code,
                    'name' => $r->name,
                ])
            ),
        ];
    }
}
