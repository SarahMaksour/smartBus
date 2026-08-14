<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class RouteDetailResource extends JsonResource
{
    public function toArray($request): array
    {
        $activeBuses = $this->buses->where('status', 'active');

        return [
            'id'                  => $this->id,
            'code'                => $this->code,
            'name'                => $this->name,
            'direction'           => $this->direction,
            'stations_count'      => $this->routeStations->count(),
            'active_buses_count'  => $activeBuses->count(),
            'avg_duration_min'    => $this->getAvgDuration(),
            'stations'            => RouteStationResource::collection(
                $this->routeStations->sortBy('order_index')
            ),
            'active_buses'        => ActiveBusResource::collection($activeBuses),
        ];
    }

    private function getAvgDuration(): int
    {
        $last = $this->routeStations->sortBy('order_index')->last();
        return $last ? (int) $last->estimated_time_from_start : 0;
    }
}