<?php

namespace App\DTOs;

class NextBusInfo
{
    public function __construct(
        public readonly int $busId,
        public readonly string $plateNumber,
        public readonly int $etaMinutes,
        public readonly float $distanceMeters,
        public readonly float $speedKmh, // جديد — رح نحتاجه لحساب رحلة الباص
    ) {}

    public function toArray(): array
    {
        return [
            'bus_id'          => $this->busId,
            'plate_number'    => $this->plateNumber,
            'eta_minutes'     => $this->etaMinutes,
            'distance_meters' => round($this->distanceMeters, 1),
            'speed_kmh'       => round($this->speedKmh, 1),
        ];
    }
}