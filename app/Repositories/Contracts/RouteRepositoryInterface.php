<?php
namespace App\Repositories\Contracts;

use Illuminate\Support\Collection;

interface RouteRepositoryInterface
{
    public function getAllActive(?string $search): Collection;
}