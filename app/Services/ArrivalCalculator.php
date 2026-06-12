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
    public function calculate(Bus $bus, RouteStation $targetStation): ?array
    {
        $location = $bus->location;

        if (!$location) {
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

        // 3. إذا الباص جاوز المحطة الهدف فعلياً (ترتيبه أكبر) → ما رايح يوصلها
        if ($nearestPassedStation && $nearestPassedStation->order_index >= $targetStation->order_index) {
            return null;
        }

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