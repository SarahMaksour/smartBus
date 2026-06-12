<?php

namespace App\Services\RouteSearch;

use App\DTOs\RouteBusSegment;
use App\DTOs\RouteSearchResult;
use App\DTOs\RouteWalkSegment;
use App\Services\DistanceCalculator;
use Illuminate\Support\Collection;

class RouteSearchService
{
    // سرعة مشي افتراضية: متر بالدقيقة (~ 4.8 كم/س)
    private const WALKING_SPEED_MPM = 80;

    // إذا المسافة أقل من هاد، نعتبر "بدون مشي مطلوب"
    private const NO_WALK_THRESHOLD_METERS = 30;

    // سرعة باص افتراضية للحساب (km/h) لو بدنا نحسب وقت تقديري بدون بيانات GPS لحظية
    private const DEFAULT_BUS_SPEED_KMH = 20;

    public function __construct(
        private readonly NearbyStationFinder $nearbyStationFinder,
        private readonly DirectTripFinder $directTripFinder,
    ) {}

    /**
     * @return RouteSearchResult[]
     */
    public function search(float $fromLat, float $fromLng, float $toLat, float $toLng): array
    {
        $startStations = $this->nearbyStationFinder->find($fromLat, $fromLng);
        $endStations   = $this->nearbyStationFinder->find($toLat, $toLng);

        $candidates = collect();

        foreach ($startStations as $start) {
            foreach ($endStations as $end) {
                $trips = $this->directTripFinder->find(
                    $start['station']->id,
                    $end['station']->id,
                );

                foreach ($trips as $trip) {
                    $candidates->push(
                        $this->buildResult($start, $end, $trip)
                    );
                }
            }
        }

        return $this->rankAndLabel($candidates);
    }

    /**
     * يبني RouteSearchResult واحد من معطيات مرشح
     */
    private function buildResult(array $start, array $end, array $trip): RouteSearchResult
    {
        $walkToStation = $this->buildWalkSegment($start['distance_meters']);
        $walkFromStation = $this->buildWalkSegment($end['distance_meters']);

        $busMinutes = $this->busMinutes($trip['distance_meters']);

        $bus = new RouteBusSegment(
            routeId:         $trip['route_station_from']->route_id,
            routeCode:       $trip['route_station_from']->route->code,
            routeName:       $trip['route_station_from']->route->name,
            fromStationId:   $trip['route_station_from']->station_id,
            fromStationName: $trip['route_station_from']->station->name,
            toStationId:     $trip['route_station_to']->station_id,
            toStationName:   $trip['route_station_to']->station->name,
            minutes:         $busMinutes,
            distanceMeters:  $trip['distance_meters'],
        );

        $totalMinutes = $walkToStation->minutes + $busMinutes + $walkFromStation->minutes;

        $totalDistance = $start['distance_meters']
            + $trip['distance_meters']
            + $end['distance_meters'];

        return new RouteSearchResult(
            token:               $this->generateToken($trip),
            label:               '', // بيتحدد بـ rankAndLabel
            totalMinutes:        $totalMinutes,
            totalDistanceMeters: $totalDistance,
            stopsCount:          $trip['stops_count'],
            walkToStation:       $walkToStation,
            walkFromStation:     $walkFromStation,
            busSegment:          $bus,
        );
    }

    private function buildWalkSegment(float $distanceMeters): RouteWalkSegment
    {
        if ($distanceMeters <= self::NO_WALK_THRESHOLD_METERS) {
            return RouteWalkSegment::none();
        }

        $minutes = (int) ceil($distanceMeters / self::WALKING_SPEED_MPM);

        return new RouteWalkSegment(
            required:        true,
            distanceMeters:  $distanceMeters,
            minutes:         $minutes,
        );
    }

    private function busMinutes(float $distanceMeters): int
    {
        $speedMetersPerMinute = (self::DEFAULT_BUS_SPEED_KMH * 1000) / 60;

        return (int) ceil($distanceMeters / $speedMetersPerMinute);
    }

    /**
     * يرتب النتائج ويعطي تصنيف "الأفضل" و "بديل سريع"
     *
     * المنطق:
     * - "best" = أقل total_minutes
     * - "fast_walk" = أقل مشي قبل الباص (walk_to_station)، إذا كان مختلف عن "best"
     *
     * @return RouteSearchResult[]
     */
    private function rankAndLabel(Collection $candidates): array
    {
        if ($candidates->isEmpty()) {
            return [];
        }

        // أزل التكرارات: نفس route + نفس محطتين
        $unique = $candidates->unique(fn($c) =>
            $c->busSegment->routeId . '-' .
            $c->busSegment->fromStationId . '-' .
            $c->busSegment->toStationId
        );

        $sortedByTotal = $unique->sortBy('totalMinutes')->values();
        $best = $sortedByTotal->first();

        $sortedByWalk = $unique->sortBy(fn($c) => $c->walkToStation->minutes)->values();
        $fastWalk = $sortedByWalk->first();

        $results = collect();

        $results->push($this->withLabel($best, 'best'));

        // أضف "بديل سريع" فقط إذا كان مختلف عن "الأفضل"
        if ($fastWalk->token !== $best->token) {
            $results->push($this->withLabel($fastWalk, 'fast_walk'));
        }

        // أضف لحد 3 نتائج كحد أقصى بدون تكرار
        foreach ($sortedByTotal as $candidate) {
            if ($results->count() >= 3) {
                break;
            }

            if (! $results->contains(fn($r) => $r->token === $candidate->token)) {
                $results->push($this->withLabel($candidate, 'alternative'));
            }
        }

        return $results->values()->all();
    }

    private function withLabel(RouteSearchResult $result, string $label): RouteSearchResult
    {
        return new RouteSearchResult(
            token:               $result->token,
            label:               $label,
            totalMinutes:        $result->totalMinutes,
            totalDistanceMeters: $result->totalDistanceMeters,
            stopsCount:          $result->stopsCount,
            walkToStation:       $result->walkToStation,
            walkFromStation:     $result->walkFromStation,
            busSegment:          $result->busSegment,
        );
    }

    /**
     * يبني token مؤقت يمثل هاي الرحلة، نستخدمه لاحقاً
     * لجيب تفاصيل المسار (endpoint التفاصيل)
     */
    private function generateToken(array $trip): string
    {
        return sprintf(
            '%d:%d:%d',
            $trip['route_station_from']->route_id,
            $trip['route_station_from']->station_id,
            $trip['route_station_to']->station_id,
        );
    }
}