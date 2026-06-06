<?php

namespace App\Http\Controllers\GPS;

use App\Services\GPSService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class GPSController extends Controller
{
protected $gpsService;

    public function __construct(GPSService $gpsService)
    {
        $this->gpsService = $gpsService;
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'bus_id' => 'required|exists:buses,id',
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
            'speed' => 'nullable|numeric',
            'direction' => 'nullable|numeric',
            'is_online' => 'nullable|boolean',
        ]);

        $this->gpsService->updateLocation($validated);

        return response()->json(['success' => true]);
    }

    public function live()
    {
        return response()->json([
            'buses' => $this->gpsService->getLive()
        ]);
    }

    public function location($id)
    {
        $location = $this->gpsService->getBusLocation($id);

        if (!$location) {
            return response()->json(['error' => 'No location found'], 404);
        }

        return response()->json([
            'bus_id' => $id,
            'lat' => $location->lat,
            'lng' => $location->lng,
            'speed' => $location->speed,
            'direction' => $location->direction,
            'updated_at' => $location->updated_at,
        ]);
    }}
