<?php
namespace App\Services;

use App\Models\Bus;
use App\Models\RouteStation;

class ArrivalCalculator
{
    // أقل سرعة معقولة لتجنب القسمة على صفر أو أرقام ضخمة
    private const MIN_SPEED_KMH = 5.0;

    // لو سرعة الباص فعلياً 0 (واقف)، نفترض هاي السرعة الافتراضية
    private const DEFAULT_SPEED_KMH = 20.0;

    /**
     * يحسب وقت الوصول بالدقائق ومسافة الباص عن المحطة المحددة
     *
     * @return array{distance_meters: float, minutes_away: int}|null
     */
    private function calculateBearing(
    float $lat1,
    float $lng1,
    float $lat2,
    float $lng2
): float {

    $lat1 = deg2rad($lat1);
    $lat2 = deg2rad($lat2);

    $deltaLng = deg2rad($lng2 - $lng1);

    $y = sin($deltaLng) * cos($lat2);

    $x =
        cos($lat1) * sin($lat2)
        - sin($lat1) * cos($lat2) * cos($deltaLng);

    $bearing = rad2deg(atan2($y, $x));

    return fmod(($bearing + 360), 360);
}

private function isMovingTowardStation(
    Bus $bus,
    RouteStation $station
): bool {

    $location = $bus->location;

    if (!$location || $location->heading === null) {
        return true;
    }

    $targetBearing = $this->calculateBearing(
        (float) $location->lat,
        (float) $location->lng,
        (float) $station->station->lat,
        (float) $station->station->lng
    );

    $heading = (float) $location->heading;

    $difference = abs($heading - $targetBearing);

    if ($difference > 180) {
        $difference = 360 - $difference;
    }

    return $difference <= 90;
}
    public function calculate(Bus $bus, RouteStation $targetStation): ?array
    {
        $location = $bus->location;
if (!$location) {
    return null;
}

if (!$this->isMovingTowardStation($bus, $targetStation)) {
    return null;
}

        // 1. المسافة المباشرة من الباص للمحطة الهدف (خط مستقيم)
        $directDistance = DistanceCalculator::haversine(
            (float) $location->lat,
            (float) $location->lng,
            (float) $targetStation->station->lat,
            (float) $targetStation->station->lng,
        );

        // 2. لاقي أقرب محطة سابقة للباص على نفس الخط (لتقدير موقعه على المسار)
        $nearestPassedStation = $this->findNearestStationOnRoute($bus, $targetStation);

        

        // 4. احسب المسافة المتبقية على المسار باستخدام distance_from_start المخزنة
        $routeDistance = null;
        if ($nearestPassedStation) {
            $routeDistance = $targetStation->distance_from_start - $nearestPassedStation->distance_from_start;
        }

        // استخدم مسافة المسار لو موجودة، وإلا المسافة المباشرة
        $distanceMeters = $routeDistance !== null && $routeDistance > 0
            ? $routeDistance
            : $directDistance;

        // 5. السرعة الحالية للباص (km/h)، مع حد أدنى منطقي
        $speedKmh = (float) $location->speed;
        if ($speedKmh <= 0) {
            $speedKmh = self::DEFAULT_SPEED_KMH;
        }
        $speedKmh = max($speedKmh, self::MIN_SPEED_KMH);

        // 6. حول السرعة لمتر/دقيقة واحسب الوقت
        $speedMetersPerMinute = ($speedKmh * 1000) / 60;
        $minutesAway = (int) ceil($distanceMeters / $speedMetersPerMinute);

        return [
            'distance_meters' => round($distanceMeters, 1),
            'minutes_away'    => $minutesAway,
        ];
    }

    /**
     * يلاقي أقرب محطة على الخط لموقع الباص الحالي
     * (المحطة اللي الباص أقرب لها جغرافياً من بين محطات خطه)
     */
    private function findNearestStationOnRoute(Bus $bus, RouteStation $targetStation): ?RouteStation
    {
        $location = $bus->location;
        if (!$location) {
            return null;
        }

        $routeStations = RouteStation::where('route_id', $targetStation->route_id)
            ->with('station')
            ->orderBy('order_index')
            ->get();

        $nearest = null;
        $minDistance = PHP_FLOAT_MAX;

        foreach ($routeStations as $rs) {
            $distance = DistanceCalculator::haversine(
                (float) $location->lat,
                (float) $location->lng,
                (float) $rs->station->lat,
                (float) $rs->station->lng,
            );

            if ($distance < $minDistance) {
                $minDistance = $distance;
                $nearest = $rs;
            }
        }

        return $nearest;
    }
}