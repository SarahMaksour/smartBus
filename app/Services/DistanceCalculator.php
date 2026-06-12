<?php
namespace App\Services;

class DistanceCalculator
{
    private const EARTH_RADIUS_METERS = 6371000;

    /**
     * يحسب المسافة بالمتر بين نقطتين بالخط المستقيم
     */
    public static function haversine(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $latFrom = deg2rad($lat1);
        $lngFrom = deg2rad($lng1);
        $latTo   = deg2rad($lat2);
        $lngTo   = deg2rad($lng2);

        $latDelta = $latTo - $latFrom;
        $lngDelta = $lngTo - $lngFrom;

        $a = sin($latDelta / 2) ** 2 +
             cos($latFrom) * cos($latTo) * sin($lngDelta / 2) ** 2;

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return self::EARTH_RADIUS_METERS * $c;
    }
}