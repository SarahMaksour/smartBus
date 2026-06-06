<?php
namespace App\Services;

use App\Repositories\Contracts\StationRepositoryInterface;
use App\Repositories\Contracts\BusRepositoryInterface;
use App\Services\Contracts\HomeServiceInterface;
use Illuminate\Support\Collection;

class HomeService implements HomeServiceInterface
{
    private const WALKING_SPEED_MPS = 80; // متر بالدقيقة

    public function __construct(
        private readonly StationRepositoryInterface $stationRepository,
        private readonly BusRepositoryInterface $busRepository,
    ) {}

    public function getNearbyStations(float $lat, float $lng, int $radius): Collection
    {
        return $this->stationRepository
            ->findNearby($lat, $lng, $radius)
            ->map(function ($station) {
                $station->minutes_away = (int) ceil(
                    $station->distance_meters / self::WALKING_SPEED_MPS
                );
                return $station;
            });
    }

    public function getMapData(float $lat, float $lng, int $radius): array
    {
        return [
            'stations' => $this->stationRepository->findNearby($lat, $lng, $radius),
            'buses'    => $this->busRepository->getActiveWithLocation(),
        ];
    }
}