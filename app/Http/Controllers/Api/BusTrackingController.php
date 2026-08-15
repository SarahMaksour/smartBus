<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BusTrackingResource;
use App\Models\Bus;

class BusTrackingController extends Controller
{
    public function track(int $id)
    {
        $bus = Bus::where('status', 'active')
            ->with([
                'location',
                'route.routeStations.station',
                'route.paths',
            ])
            ->findOrFail($id);

        return new BusTrackingResource($bus);
    }
}