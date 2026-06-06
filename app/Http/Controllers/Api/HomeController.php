<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\NearbyRequest;
use App\Http\Resources\StationNearbyResource;
use App\Http\Resources\BusMapResource;
use App\Services\Contracts\HomeServiceInterface;

class HomeController extends Controller
{
    public function __construct(
        private readonly HomeServiceInterface $homeService,
    ) {}

    public function nearbyStations(NearbyRequest $request)
    {
        $stations = $this->homeService->getNearbyStations(
            lat:    $request->float('lat'),
            lng:    $request->float('lng'),
            radius: $request->radius(),
        );

        return StationNearbyResource::collection($stations);
    }

    public function mapData(NearbyRequest $request)
    {
        $data = $this->homeService->getMapData(
            lat:    $request->float('lat'),
            lng:    $request->float('lng'),
            radius: $request->radius(),
        );

        return response()->json([
            'stations' => StationNearbyResource::collection($data['stations']),
            'buses'    => BusMapResource::collection($data['buses']),
        ]);
    }
}