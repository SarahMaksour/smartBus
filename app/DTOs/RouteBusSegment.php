<?php

namespace App\DTOs;

class RouteBusSegment
{
    public function __construct(
        public readonly int $routeId,
        public readonly string $routeCode,
        public readonly string $routeName,
        public readonly int $fromStationId,
        public readonly string $fromStationName,
        public readonly int $toStationId,
        public readonly string $toStationName,
        public readonly int $minutes,
        public readonly float $distanceMeters,
    ) {}

    public function toArray(): array
    {
        return [
            'route_id'          => $this->routeId,
            'route_code'        => $this->routeCode,
            'route_name'        => $this->routeName,
            'from_station_id'   => $this->fromStationId,
            'from_station_name' => $this->fromStationName,
            'to_station_id'     => $this->toStationId,
            'to_station_name'   => $this->toStationName,
            'minutes'           => $this->minutes,
            'distance_meters'   => round($this->distanceMeters, 1),
        ];
    }
}