<?php

namespace App\DTOs;

class RouteWalkSegment
{
    public function __construct(
        public readonly bool $required,      // في مشي أصلاً؟
        public readonly float $distanceMeters,
        public readonly int $minutes,
    ) {}

    public static function none(): self
    {
        return new self(required: false, distanceMeters: 0, minutes: 0);
    }

    public function toArray(): array
    {
        return [
            'required'        => $this->required,
            'distance_meters' => round($this->distanceMeters, 1),
            'minutes'         => $this->minutes,
        ];
    }
}