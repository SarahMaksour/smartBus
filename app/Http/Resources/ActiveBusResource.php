<?php

namespace App\Http\Resources;

use App\Services\DistanceCalculator;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ActiveBusResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
  public function toArray($request): array
    {
        return [
            'id'              => $this->id,
            'plate_number'    => $this->plate_number,
            'lat'             => (float) optional($this->location)->lat,
            'lng'             => (float) optional($this->location)->lng,
            'speed'           => (float) optional($this->location)->speed,
            'heading'         => (float) optional($this->location)->heading,
            'nearest_station' => $this->getNearestStation(),
            'last_updated'    => optional($this->location)->recorded_at,
        ];
    }

    private function getNearestStation(): ?array
    {
        $location = $this->location;
        if (! $location) return null;

        $nearest     = null;
        $minDistance = PHP_FLOAT_MAX;

        foreach ($this->route->routeStations as $rs) {
            $distance = DistanceCalculator::haversine(
                (float) $location->lat, (float) $location->lng,
                (float) $rs->station->lat, (float) $rs->station->lng,
            );

            if ($distance < $minDistance) {
                $minDistance = $distance;
                $nearest = [
                    'id'   => $rs->station->id,
                    'name' => $rs->station->name,
                ];
            }
        }

        return $nearest;
    }
}
