<?php

namespace App\Services\RouteSearch;

use App\Models\RouteStation;
use Illuminate\Support\Collection;

class DirectTripFinder
{
    /**
     * يلاقي كل الرحلات المباشرة (بخط واحد) بين محطتين
     *
     * منطق "مباشر": لازم يكون في route_id واحد عنده الاتنين
     * المحطات، وترتيب محطة الانطلاق (order_index) أقل من ترتيب
     * محطة النزول — يعني الباص بيمشي بهالاتجاه فعلاً.
     *
     * @return Collection<int, array{
     *     route_station_from: RouteStation,
     *     route_station_to: RouteStation,
     *     stops_count: int,
     *     distance_meters: float
     * }>
     */
    public function find(int $fromStationId, int $toStationId): Collection
    {
        // كل صفوف route_stations اللي فيها المحطة الأولى
        $fromRows = RouteStation::where('station_id', $fromStationId)
            ->with('route')
            ->get();

        // كل صفوف route_stations اللي فيها المحطة الثانية
        $toRows = RouteStation::where('station_id', $toStationId)
            ->with('route')
            ->get();

        $results = collect();

        foreach ($fromRows as $fromRow) {
            foreach ($toRows as $toRow) {
                // لازم يكونوا على نفس الخط
                if ($fromRow->route_id !== $toRow->route_id) {
                    continue;
                }

                // لازم اتجاه الباص صحيح: الانطلاق قبل النزول بالترتيب
                if ($fromRow->order_index >= $toRow->order_index) {
                    continue;
                }

                // لازم الخط نشط
                if (! $fromRow->route->is_active) {
                    continue;
                }

                $results->push([
                    'route_station_from' => $fromRow,
                    'route_station_to'   => $toRow,
                    'stops_count'        => $toRow->order_index - $fromRow->order_index,
                    'distance_meters'    => $toRow->distance_from_start - $fromRow->distance_from_start,
                ]);
            }
        }

        return $results;
    }
}