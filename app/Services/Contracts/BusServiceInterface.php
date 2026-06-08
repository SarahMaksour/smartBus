<?php
namespace App\Services\Contracts;

use Illuminate\Support\Collection;

interface BusServiceInterface
{
    public function getAllLines(?string $search): Collection;
}