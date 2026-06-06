<?php
namespace App\Repositories\Contracts;

use Illuminate\Support\Collection;

interface StationRepositoryInterface
{
    public function findNearby(float $lat, float $lng, int $radius): Collection;
}