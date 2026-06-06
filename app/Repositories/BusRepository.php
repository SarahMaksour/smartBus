<?php
namespace App\Repositories;

use App\Models\Bus;
use App\Repositories\Contracts\BusRepositoryInterface;
use Illuminate\Support\Collection;

class BusRepository implements BusRepositoryInterface
{
    public function __construct(private readonly Bus $model) {}

    public function getActiveWithLocation(): Collection
    {
        return $this->model
            ->query()
            ->where('status', 'active')
            ->whereHas('location', fn($q) => $q->where('is_online', true))
            ->with([
                'location',
                'route:id,code,name',
            ])
            ->get();
    }
}