<?php

namespace App\Services\RouteSearch;

use App\DTOs\NextBusInfo;
use App\Models\Bus;
use App\Models\RouteStation;
use App\Services\ArrivalCalculator;

class NextBusFinder
{
    public function __construct(
        private readonly ArrivalCalculator $arrivalCalculator,
    ) {}

    /**
     * يلاقي أقرب باص شغال على خط معين، رايح يصل لمحطة معينة
     *
     * بيرجع null لو:
     * - ما في باصات شغالة على هاد الخط
     * - أو كل الباصات الشغالة فاتت المحطة
     * - أو الباص موجود بس بدون سرعة فعلية (speed <= 0)
     */
    public function find(int $routeId, RouteStation $targetStation): ?NextBusInfo
    {
        $activeBuses = Bus::where('route_id', $routeId)
            ->where('status', 'active')
            ->with('location')
            ->get();

        if ($activeBuses->isEmpty()) {
            return null;
        }

        $best = null;

        foreach ($activeBuses as $bus) {
            $location = $bus->location;

            // لازم يكون عنده موقع وسرعة فعلية أكبر من صفر
            if (! $location || (float) $location->speed <= 0) {
                continue;
            }

            $result = $this->arrivalCalculator->calculate($bus, $targetStation);

            if ($result === null) {
                continue;
            }

            if ($best === null || $result['minutes_away'] < $best['result']['minutes_away']) {
                $best = [
                    'bus'    => $bus,
                    'result' => $result,
                ];
            }
        }

        if ($best === null) {
            return null;
        }

        return new NextBusInfo(
            busId:          $best['bus']->id,
            plateNumber:    $best['bus']->plate_number,
            etaMinutes:     $best['result']['minutes_away'],
            distanceMeters: $best['result']['distance_meters'],
            speedKmh:       (float) $best['bus']->location->speed,
        );
    }
}