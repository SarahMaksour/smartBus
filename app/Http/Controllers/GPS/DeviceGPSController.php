<?php

namespace App\Http\Controllers\GPS;

use App\Http\Controllers\Controller;
use App\Models\Bus;
use App\Services\GPSService;
use Illuminate\Http\Request;

class DeviceGPSController extends Controller
{
    protected $gpsService;

    public function __construct(GPSService $gpsService)
    {
        $this->gpsService = $gpsService;
    }

    public function receive(Request $request)
    {
        // ناخد البيانات مثل ما هي (كأنها من جهاز)
        $data = $request->all();

     $bus = Bus::where('imei', $data['imei'])->first();
        $this->gpsService->updateLocation([
            'bus_id' => $bus->id,
            'lat'       => $data['lat'] ?? 0,
            'lng'       => $data['lng'] ?? 0,
            'speed'     => $data['speed'] ?? 0,
            'direction' => $data['direction'] ?? 0,
            'is_online' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Location received from device'
        ]);
    }
}