<?php
namespace App\Repositories;

use App\Models\Station;
use App\Repositories\Contracts\StationRepositoryInterface;
use Illuminate\Support\Collection;

class StationRepository implements StationRepositoryInterface
{
    public function __construct(private readonly Station $model) {}

    public function findNearby(float $lat, float $lng, int $radius): Collection
    {
        return $this->model
            ->query()
            ->where('is_active', true)
            ->selectRaw("
                *,
                ROUND(
                    6371000 * acos(
                        cos(radians(?)) * cos(radians(lat)) *
                        cos(radians(lng) - radians(?)) +
                        sin(radians(?)) * sin(radians(lat))
                    )
                ) AS distance_meters
            ", [$lat, $lng, $lat])
            ->having('distance_meters', '<=', $radius)
            ->orderBy('distance_meters')
            ->limit(10)
            ->with(['routes' => fn($q) => $q->where('is_active', true)
                                            ->select('routes.id', 'routes.code', 'routes.name')])
            ->get();
    }
}