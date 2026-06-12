<?php
namespace App\Http\Resources;

use App\Models\RouteStation;
use App\Services\ArrivalCalculator;
use Illuminate\Http\Resources\Json\JsonResource;

class BusLineResource extends JsonResource
{
    public function toArray($request): array
    {
        $activeBuses = $this->buses->where('status', 'active');

        return [
            'id'            => $this->id,
            'code'          => $this->code,
            'name'          => $this->name,
            'direction'     => $this->direction,
            'is_active'     => $this->is_active,
            'buses_count'   => $activeBuses->count(),
            'next_arrival'  => $this->getNextArrival($activeBuses),
            'status'        => $this->getLineStatus($activeBuses),
        ];
    }

     private function getNearestStationForUser(
    float $userLat,
    float $userLng
): ?RouteStation {

    return RouteStation::where('route_id', $this->id)
        ->with('station')
        ->get()
        ->sortBy(function ($routeStation) use ($userLat, $userLng) {

            return \App\Services\DistanceCalculator::haversine(
                $userLat,
                $userLng,
                $routeStation->station->lat,
                $routeStation->station->lng
            );
        })
        ->first();
}
    /**
     * أقل وقت وصول من كل الباصات الشغالة على الخط للمحطة الأولى
     */
    private function getNextArrival($activeBuses): ?int
    {
        if ($activeBuses->isEmpty()) {
            return null;
        }

        // أول محطة بالخط (نقطة الانطلاق)
      $userLat = $this->user_lat;
$userLng = $this->user_lng;

$nearestStation = $this->getNearestStationForUser(
    $userLat,
    $userLng
);

if (!$nearestStation) {
    return null;
}

      $calculator = new ArrivalCalculator();

$times = $activeBuses
    ->map(fn ($bus) => $calculator->calculate(
        $bus,
        $nearestStation
    ))
    ->filter()
    ->pluck('minutes_away');

return $times->min();
    }

    /**
     * حالة الخط حسب متوسط سرعة الباصات الشغالة
     */
    private function getLineStatus($activeBuses): string
    {
        if ($activeBuses->isEmpty()) {
            return 'unavailable';
        }

        $avgSpeed = $activeBuses
            ->map(fn($bus) => optional($bus->location)->speed ?? 0)
            ->average();

        if ($avgSpeed >= 30) return 'available';
        if ($avgSpeed >= 15) return 'moderate';
        return 'delayed';
    }
}