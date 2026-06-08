<?php
namespace App\Providers;

use App\Repositories\BusRepository;
use App\Repositories\Contracts\BusRepositoryInterface;
use App\Repositories\Contracts\RouteRepositoryInterface;
use App\Repositories\Contracts\StationRepositoryInterface;
use App\Repositories\RouteRepository;
use App\Repositories\StationRepository;
use App\Services\BusService;
use App\Services\Contracts\BusServiceInterface;
use App\Services\Contracts\HomeServiceInterface;
use App\Services\HomeService;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Repositories
        $this->app->bind(StationRepositoryInterface::class, StationRepository::class);
        $this->app->bind(BusRepositoryInterface::class, BusRepository::class);
        $this->app->bind(RouteRepositoryInterface::class, RouteRepository::class);
        // Services
        $this->app->bind(HomeServiceInterface::class, HomeService::class);
        $this->app->bind(BusServiceInterface::class, BusService::class);
    }
}