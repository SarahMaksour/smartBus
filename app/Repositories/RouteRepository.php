<?php
namespace App\Repositories;

use App\Models\Route;
use App\Repositories\Contracts\RouteRepositoryInterface;
use Illuminate\Support\Collection;

class RouteRepository implements RouteRepositoryInterface
{
    public function __construct(private readonly Route $model) {}

    public function getAllActive(?string $search): Collection
    {
        return $this->model
            ->query()
            ->where('is_active', true)
            ->when($search, fn($q) =>
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
            )
            ->with([
                'buses' => fn($q) => $q->where('status', 'active')
                                       ->with('location'),
            ])
            ->orderBy('code')
            ->get();
    }
}