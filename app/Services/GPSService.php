<?php

namespace App\Services;

use App\Models\BusLocations;
use App\Repositories\GPSRepository;

class GPSService
{
    protected $gpsRepo;

    public function __construct(GPSRepository $gpsRepo)
    {
        $this->gpsRepo = $gpsRepo;
    }

    public function updateLocation($data)
    {
        return $this->gpsRepo->saveLocation($data);
    }

  public function getLive()
{
    return BusLocations::select('bus_id', 'lat', 'lng', 'speed', 'direction')
        ->whereIn('id', function ($query) {
            $query->selectRaw('MAX(id)')
                  ->from('bus_locations')
                  ->groupBy('bus_id');
        })
        ->get();
}

    public function getBusLocation($busId)
    {
        return $this->gpsRepo->getBusLatestLocation($busId);
    }
}
