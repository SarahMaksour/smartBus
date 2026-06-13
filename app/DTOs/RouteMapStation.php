<?php

namespace App\DTOs;

class RouteMapStation
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly float $lat,
        public readonly float $lng,
        public readonly string $role, // "boarding" | "intermediate" | "alighting"
    ) {}

    public function toArray(): array
    {
        return [
            'id'   => $this->id,
            'name' => $this->name,
            'lat'  => $this->lat,
            'lng'  => $this->lng,
            'role' => $this->role,
        ];
    }
}