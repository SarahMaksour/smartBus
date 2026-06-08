<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BusLineResource extends JsonResource
{
    public function toArray($request): array
    {
        $activeBuses = $this->buses->where('status', 'active');

        return [
            'id'            => $this->id,
            'code'          => $this->code,
            'name'          => $this->name,
            'is_active'     => $this->is_active,
            'buses_count'   => $activeBuses->count(),
            'next_arrival'  => $this->getNextArrival($activeBuses),
            'status'        => $this->getLineStatus($activeBuses),
        ];
    }

    private function getNextArrival($activeBuses): ?int
    {
        // بترجع أقل وقت وصول بالدقائق من كل الباصات الشغالة
        return $activeBuses
            ->map(fn($bus) => optional($bus->location)->speed > 0 ? 5 : 10)
            ->min();
    }

    private function getLineStatus($activeBuses): string
    {
        if ($activeBuses->isEmpty()) {
            return 'unavailable';
        }

        $avgSpeed = $activeBuses
            ->map(fn($bus) => optional($bus->location)->speed ?? 0)
            ->average();

        if ($avgSpeed >= 30) return 'available';
        if ($avgSpeed >= 15) return 'moderate';
        return 'delayed';
    }
}