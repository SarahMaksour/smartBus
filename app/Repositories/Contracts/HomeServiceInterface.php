<?php
namespace App\Services\Contracts;

use Illuminate\Support\Collection;

interface HomeServiceInterface
{
    public function getNearbyStations(float $lat, float $lng, int $radius): Collection;
    public function getMapData(float $lat, float $lng, int $radius): array;
}