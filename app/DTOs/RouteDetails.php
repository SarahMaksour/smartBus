<?php

namespace App\DTOs;

class RouteDetails
{
    public function __construct(
        public readonly string $routeCode,
        public readonly string $routeName,
        public readonly array $mapStations,   // RouteMapStation[]
        public readonly array $timeline,      // RouteTimelineStep[]
        public readonly ?RouteWalkSegment $walkFromStation,
    ) {}

    public function toArray(): array
    {
        return [
            'route_code'        => $this->routeCode,
            'route_name'        => $this->routeName,
            'map_stations'      => array_map(fn($s) => $s->toArray(), $this->mapStations),
            'timeline'          => array_map(fn($t) => $t->toArray(), $this->timeline),
            'walk_from_station' => $this->walkFromStation?->toArray(),
        ];
    }
}