<?php

namespace App\Services\RouteSearch;

use App\DTOs\RouteDetails;
use App\DTOs\RouteMapStation;
use App\DTOs\RouteTimelineStep;
use App\DTOs\RouteWalkSegment;
use App\Models\RouteStation;
use App\Services\DistanceCalculator;
use InvalidArgumentException;

class RouteDetailsBuilder
{
    private const WALKING_SPEED_MPM = 80;
    private const NO_WALK_THRESHOLD_METERS = 30;

    /**
     * @throws InvalidArgumentException إذا الـ token غير صالح أو المحطات غير موجودة
     */
    public function build(string $token, float $toLat, float $toLng): RouteDetails
    {
        [$routeId, $fromStationId, $toStationId] = $this->parseToken($token);

        // كل محطات الخط بالترتيب
        $allStations = RouteStation::where('route_id', $routeId)
            ->with(['station', 'route'])
            ->orderBy('order_index')
            ->get();

        $fromStation = $allStations->firstWhere('station_id', $fromStationId);
        $toStation   = $allStations->firstWhere('station_id', $toStationId);

        if (! $fromStation || ! $toStation) {
            throw new InvalidArgumentException('المحطات غير موجودة على هذا الخط');
        }

        // المحطات بين الانطلاق والنزول (شاملة)
        $segmentStations = $allStations
            ->filter(fn($rs) =>
                $rs->order_index >= $fromStation->order_index
                && $rs->order_index <= $toStation->order_index
            )
            ->values();

        $mapStations = $this->buildMapStations($segmentStations);

        $walkFromStation = $this->buildWalkFromStation($toStation, $toLat, $toLng);

        $timeline = $this->buildTimeline($fromStation, $toStation, $walkFromStation);

        return new RouteDetails(
            routeCode:        $fromStation->route->code,
            routeName:        $fromStation->route->name,
            mapStations:      $mapStations,
            timeline:         $timeline,
            walkFromStation:  $walkFromStation,
        );
    }

    /**
     * @return array{0: int, 1: int, 2: int}
     */
    private function parseToken(string $token): array
    {
        $parts = explode(':', $token);

        if (count($parts) !== 3 || ! ctype_digit($parts[0]) || ! ctype_digit($parts[1]) || ! ctype_digit($parts[2])) {
            throw new InvalidArgumentException('رمز الرحلة غير صالح');
        }

        return [(int) $parts[0], (int) $parts[1], (int) $parts[2]];
    }

    /**
     * @return RouteMapStation[]
     */
    private function buildMapStations($segmentStations): array
    {
        $first = $segmentStations->first();
        $last  = $segmentStations->last();

        return $segmentStations->map(function (RouteStation $rs) use ($first, $last) {
            $role = match (true) {
                $rs->id === $first->id => 'boarding',
                $rs->id === $last->id  => 'alighting',
                default                => 'intermediate',
            };

            return new RouteMapStation(
                id:   $rs->station->id,
                name: $rs->station->name,
                lat:  (float) $rs->station->lat,
                lng:  (float) $rs->station->lng,
                role: $role,
            );
        })->all();
    }

    private function buildWalkFromStation(RouteStation $toStation, float $toLat, float $toLng): ?RouteWalkSegment
    {
        $distance = DistanceCalculator::haversine(
            (float) $toStation->station->lat,
            (float) $toStation->station->lng,
            $toLat,
            $toLng,
        );

        if ($distance <= self::NO_WALK_THRESHOLD_METERS) {
            return RouteWalkSegment::none();
        }

        $minutes = (int) ceil($distance / self::WALKING_SPEED_MPM);

        return new RouteWalkSegment(
            required:       true,
            distanceMeters: $distance,
            minutes:        $minutes,
        );
    }

    /**
     * @return RouteTimelineStep[]
     */
    private function buildTimeline(
        RouteStation $fromStation,
        RouteStation $toStation,
        ?RouteWalkSegment $walkFromStation,
    ): array {
        $steps = [];

        // 1. موقعك الحالي
        $steps[] = new RouteTimelineStep(
            type:     'start',
            label:    'موقعك الحالي',
            subLabel: $fromStation->station->name,
        );

        // 2. انطلاق بالباص
        $steps[] = new RouteTimelineStep(
            type:     'board',
            label:    "انطلق بالباص خط {$fromStation->route->code}",
            subLabel: "اتجاه {$toStation->station->name}",
        );

        // 3. محطة النزول
        $steps[] = new RouteTimelineStep(
            type:     'alight',
            label:    "{$toStation->station->name} (محطة النزول)",
            subLabel: 'تنزل هنا',
        );

        // 4. مشي لوجهتك (فقط إذا مطلوب)
        if ($walkFromStation && $walkFromStation->required) {
            $steps[] = new RouteTimelineStep(
                type:            'walk',
                label:           'مشي إلى وجهتك',
                subLabel:        null,
                distanceMeters:  (int) round($walkFromStation->distanceMeters),
                durationMinutes: $walkFromStation->minutes,
            );
        }

        // 5. الوصول
        $steps[] = new RouteTimelineStep(
            type:     'end',
            label:    'الوصول إلى وجهتك',
            subLabel: 'تم الوصول',
        );

        return $steps;
    }
}