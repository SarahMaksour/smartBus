<?php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\Contracts\StationRepositoryInterface;
use App\Repositories\Contracts\BusRepositoryInterface;
use App\Repositories\StationRepository;
use App\Repositories\BusRepository;
use App\Services\Contracts\HomeServiceInterface;
use App\Services\HomeService;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Repositories
        $this->app->bind(StationRepositoryInterface::class, StationRepository::class);
        $this->app->bind(BusRepositoryInterface::class, BusRepository::class);

        // Services
        $this->app->bind(HomeServiceInterface::class, HomeService::class);
    }
}