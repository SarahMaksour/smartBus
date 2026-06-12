<?php

namespace App\Services\RouteSearch;

use App\Models\Station;
use App\Services\DistanceCalculator;
use Illuminate\Support\Collection;

class NearbyStationFinder
{
    // أقصى مسافة مشي معقولة للبحث عن محطة (متر)
    private const MAX_WALK_DISTANCE_METERS = 1200;

    // أقصى عدد محطات نرجعهم لكل نقطة
    private const MAX_STATIONS = 5;

    /**
     * يرجع أقرب محطات لنقطة معينة، مع المسافة لكل واحدة بالمتر
     *
     * @return Collection<int, array{station: Station, distance_meters: float}>
     */
    public function find(float $lat, float $lng): Collection
    {
        return Station::where('is_active', true)
            ->get()
            ->map(function (Station $station) use ($lat, $lng) {
                return [
                    'station'         => $station,
                    'distance_meters' => DistanceCalculator::haversine(
                        $lat, $lng,
                        (float) $station->lat, (float) $station->lng,
                    ),
                ];
            })
            ->filter(fn($item) => $item['distance_meters'] <= self::MAX_WALK_DISTANCE_METERS)
            ->sortBy('distance_meters')
            ->take(self::MAX_STATIONS)
            ->values();
    }
}