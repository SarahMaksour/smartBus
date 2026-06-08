<?php
namespace App\Services;

use App\Repositories\Contracts\RouteRepositoryInterface;
use App\Services\Contracts\BusServiceInterface;
use Illuminate\Support\Collection;

class BusService implements BusServiceInterface
{
    public function __construct(
        private readonly RouteRepositoryInterface $routeRepository,
    ) {}

    public function getAllLines(?string $search): Collection
    {
        return $this->routeRepository->getAllActive($search);
    }
}