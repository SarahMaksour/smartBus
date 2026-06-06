<?php

namespace App\Services;

use App\Repositories\BusRepository;

class BusService
{
    protected $busRepository;
    public function __construct(BusRepository $busRepository)
    {
        $this->busRepository = $busRepository;
    }
    public function getAllBuses()
    {
        return $this->busRepository->getAllBuses()->map(function ($bus) {
            return [
                'id'         => $bus->id,
                'bus_number' => $bus->bus_number,
                'route_id'   => $bus->route_id,
                'status'     => $bus->is_active ? 'online' : 'offline',
            ];
        });
    }
    public function getById($id)
    {
        $bus = $this->busRepository->findById($id);

        return [
            'id'         => $bus->id,
            'bus_number' => $bus->bus_number,
            'route_id'   => $bus->route_id,
            'status'     => $bus->is_active ? 'online' : 'offline',
            'capacity'   => $bus->capacity,
        ];
    }
}
