<?php
namespace App\Repositories\Contracts;

use Illuminate\Support\Collection;

interface BusRepositoryInterface
{
    public function getActiveWithLocation(): Collection;
}
