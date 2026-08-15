<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Services\DistanceCalculator;
use App\Models\RouteStation;

class BusTrackingResource extends JsonResource
{
    public function toArray($request): array
    {
        $location = $this->location;
        $nextStation = $this->getNextStation();
        $etaToEnd = $this->getEtaToLastStation();

        return [
            'id'           => $this->id,
            'plate_number' => $this->plate_number,
            'lat'          => (float) optional($location)->lat,
            'lng'          => (float) optional($location)->lng,
            'speed'        => (float) optional($location)->speed,
            'heading'      => (float) optional($location)->heading,
            'last_updated' => optional($location)->recorded_at,
            'route'        => [
                'id'   => $this->route->id,
                'code' => $this->route->code,
                'name' => $this->route->name,
            ],
            'next_station'  => $nextStation,
            'eta_to_end_min' => $etaToEnd,
            'path'          => $this->getRoutePath(),
        ];
    }

    private function getNextStation(): ?array
    {
        $location = $this->location;
        if (! $location) return null;

        $stations = $this->route->routeStations
            ->sortBy('order_index');

        // لاقي أقرب محطة للباص
        $nearest = null;
        $minDistance = PHP_FLOAT_MAX;
        $nearestOrder = 0;

        foreach ($stations as $rs) {
            $distance = DistanceCalculator::haversine(
                (float) $location->lat, (float) $location->lng,
                (float) $rs->station->lat, (float) $rs->station->lng,
            );

            if ($distance < $minDistance) {
                $minDistance = $distance;
                $nearest = $rs;
                $nearestOrder = $rs->order_index;
            }
        }

        // المحطة القادمة = الأقرب + 1
        $nextStation = $stations->firstWhere('order_index', $nearestOrder + 1);

        if (! $nextStation) return null;

        // حساب وقت الوصول للمحطة القادمة
        $distanceToNext = DistanceCalculator::haversine(
            (float) $location->lat, (float) $location->lng,
            (float) $nextStation->station->lat, (float) $nextStation->station->lng,
        );

        $speed = (float) $location->speed;
        $etaMinutes = $speed > 0
            ? (int) ceil($distanceToNext / (($speed * 1000) / 60))
            : null;

        return [
            'id'          => $nextStation->station->id,
            'name'        => $nextStation->station->name,
            'lat'         => (float) $nextStation->station->lat,
            'lng'         => (float) $nextStation->station->lng,
            'eta_minutes' => $etaMinutes,
        ];
    }

    private function getEtaToLastStation(): ?int
    {
        $location = $this->location;
        if (! $location || (float) $location->speed <= 0) return null;

        $lastStation = $this->route->routeStations
            ->sortBy('order_index')
            ->last();

        if (! $lastStation) return null;

        $distance = DistanceCalculator::haversine(
            (float) $location->lat, (float) $location->lng,
            (float) $lastStation->station->lat, (float) $lastStation->station->lng,
        );

        $speed = (float) $location->speed;
        return (int) ceil($distance / (($speed * 1000) / 60));
    }

    private function getRoutePath(): array
    {
        return $this->route->paths
            ->sortBy('order_index')
            ->map(fn($p) => [
                'lat' => (float) $p->lat,
                'lng' => (float) $p->lng,
            ])
            ->values()
            ->all();
    }
}