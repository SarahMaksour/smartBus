<?php

namespace App\DTOs;

class RouteTimelineStep
{
    public function __construct(
        public readonly string $type,        // "start" | "board" | "alight" | "walk" | "end"
        public readonly string $label,       // النص المعروض
        public readonly ?string $subLabel,    // نص ثانوي (مثل "اتجاه باب النيرب")
        public readonly ?int $distanceMeters = null,
        public readonly ?int $durationMinutes = null,
    ) {}

    public function toArray(): array
    {
        return [
            'type'             => $this->type,
            'label'            => $this->label,
            'sub_label'        => $this->subLabel,
            'distance_meters'  => $this->distanceMeters,
            'duration_minutes' => $this->durationMinutes,
        ];
    }
}