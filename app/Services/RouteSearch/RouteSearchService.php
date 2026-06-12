<?php

namespace App\Services\RouteSearch;

use App\DTOs\RouteBusSegment;
use App\DTOs\RouteSearchResult;
use App\DTOs\RouteWalkSegment;
use Illuminate\Support\Collection;

class RouteSearchService
{
    // سرعة مشي افتراضية: متر بالدقيقة (~ 4.8 كم/س)
    // هاد معيار لمشي الإنسان، لا علاقة له بـ GPS الباص
    private const WALKING_SPEED_MPM = 80;

    // إذا المسافة أقل من هاد، نعتبر "بدون مشي مطلوب"
    private const NO_WALK_THRESHOLD_METERS = 30;

    public function __construct(
        private readonly NearbyStationFinder $nearbyStationFinder,
        private readonly DirectTripFinder $directTripFinder,
        private readonly NextBusFinder $nextBusFinder,
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
                    $result = $this->buildResult($start, $end, $trip);

                    // إذا ما رجع شي (ما في باص فعلي بسرعة حقيقية) → استثني هاد الخط
                    if ($result !== null) {
                        $candidates->push($result);
                    }
                }
            }
        }

        return $this->rankAndLabel($candidates);
    }

    /**
     * يبني RouteSearchResult واحد من معطيات مرشح
     *
     * يرجع null إذا ما في باص شغال فعلياً على هاد الخط بسرعة حقيقية،
     * لأنه بدون سرعة فعلية ما عنا أي بيانات حقيقية نحسب عليها وقت الرحلة
     */
    private function buildResult(array $start, array $end, array $trip): ?RouteSearchResult
    {
        $fromStation = $trip['route_station_from'];
        $toStation   = $trip['route_station_to'];

        // 1. لازم نلاقي باص فعلي شغال رايح لمحطة الانطلاق
        $nextBus = $this->nextBusFinder->find(
            routeId:       $fromStation->route_id,
            targetStation: $fromStation,
        );

        // 2. بدون باص حقيقي بسرعة حقيقية، ما نقدر نحسب رحلة واقعية → استثني
        if ($nextBus === null) {
            return null;
        }

        // 3. وقت رحلة الباص بين المحطتين = المسافة ÷ سرعة الباص الفعلية
        $busMinutes = $this->busMinutes(
            distanceMeters: $trip['distance_meters'],
            speedKmh:       $nextBus->speedKmh,
        );

        // 4. مشي المستخدم (يبقى بالمعيار العالمي 80م/د)
        $walkToStation   = $this->buildWalkSegment($start['distance_meters']);
        $walkFromStation = $this->buildWalkSegment($end['distance_meters']);

        $bus = new RouteBusSegment(
            routeId:         $fromStation->route_id,
            routeCode:       $fromStation->route->code,
            routeName:       $fromStation->route->name,
            fromStationId:   $fromStation->station_id,
            fromStationName: $fromStation->station->name,
            toStationId:     $toStation->station_id,
            toStationName:   $toStation->station->name,
            minutes:         $busMinutes,
            distanceMeters:  $trip['distance_meters'],
        );

        // 5. الوقت الكلي = مشي + انتظار الباص الفعلي + رحلة الباص + مشي
        $totalMinutes = $walkToStation->minutes
            + $nextBus->etaMinutes
            + $busMinutes
            + $walkFromStation->minutes;

        $totalDistance = $start['distance_meters']
            + $trip['distance_meters']
            + $end['distance_meters'];

        return new RouteSearchResult(
            token:               $this->generateToken($trip),
            label:               '',
            totalMinutes:        $totalMinutes,
            totalDistanceMeters: $totalDistance,
            stopsCount:          $trip['stops_count'],
            walkToStation:       $walkToStation,
            walkFromStation:     $walkFromStation,
            busSegment:          $bus,
            nextBus:             $nextBus,
        );
    }

    private function buildWalkSegment(float $distanceMeters): RouteWalkSegment
    {
        if ($distanceMeters <= self::NO_WALK_THRESHOLD_METERS) {
            return RouteWalkSegment::none();
        }

        $minutes = (int) ceil($distanceMeters / self::WALKING_SPEED_MPM);

        return new RouteWalkSegment(
            required:       true,
            distanceMeters: $distanceMeters,
            minutes:        $minutes,
        );
    }

    /**
     * يحسب وقت رحلة الباص بين المحطتين باستخدام سرعته الفعلية الحالية من GPS
     */
    private function busMinutes(float $distanceMeters, float $speedKmh): int
    {
        $speedMetersPerMinute = ($speedKmh * 1000) / 60;

        return (int) ceil($distanceMeters / $speedMetersPerMinute);
    }

    /**
     * يرتب النتائج ويعطي تصنيف "الأفضل" و "بديل سريع"
     *
     * @return RouteSearchResult[]
     */
    private function rankAndLabel(Collection $candidates): array
    {
        if ($candidates->isEmpty()) {
            return [];
        }

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

        if ($fastWalk->token !== $best->token) {
            $results->push($this->withLabel($fastWalk, 'fast_walk'));
        }

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
            nextBus:             $result->nextBus,
        );
    }

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