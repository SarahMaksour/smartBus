<?php

namespace App\DTOs;

class RouteSearchResult
{
    public function __construct(
        public readonly string $token,
        public readonly string $label,
        public readonly int $totalMinutes,
        public readonly float $totalDistanceMeters,
        public readonly int $stopsCount,
        public readonly RouteWalkSegment $walkToStation,
        public readonly RouteWalkSegment $walkFromStation,
        public readonly RouteBusSegment $busSegment,
        public readonly ?NextBusInfo $nextBus = null,
    ) {}

    public function toArray(): array
    {
        return [
            'token'                 => $this->token,
            'label'                 => $this->label,
            'total_minutes'         => $this->totalMinutes,
            'total_distance_meters' => round($this->totalDistanceMeters, 1),
            'stops_count'           => $this->stopsCount,
            'walk_to_station'       => $this->walkToStation->toArray(),
            'walk_from_station'     => $this->walkFromStation->toArray(),
            'bus'                   => $this->busSegment->toArray(),
            'next_bus'              => $this->nextBus?->toArray(),
        ];
    }
}