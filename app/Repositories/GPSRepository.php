<?php
namespace App\Repositories;

use App\Models\BusLocations;
use GuzzleHttp\Promise\Create;

class GPSRepository{
    public function saveLocation($data){
        return BusLocations::create(
            [
            'bus_id' => $data['bus_id'],
            'lat' => $data['lat'],
            'lng' => $data['lng'],
            'speed' => $data['speed'] ?? 0,
            'direction' => $data['direction'] ?? 0,
            'is_online' => $data['is_online'] ?? true,
        ]
            );
    }
    public function getBusLatestLocation($busId)
    {
        return BusLocations::where('bus_id', $busId)
            ->latest()
            ->first();
    }
    public function getAllLatestLocations()
    {
        return BusLocations::select('bus_id', 'lat', 'lng', 'speed', 'direction', 'created_at')
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('bus_id');
           
    }
}