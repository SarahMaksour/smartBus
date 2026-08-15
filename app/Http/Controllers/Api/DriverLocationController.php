<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\DriverLocationRequest;
use App\Models\BusLocation;
use App\Models\GpsLog;
use Illuminate\Http\Request;

class DriverLocationController extends Controller
{
    public function startTrip(Request $request)
    {
        $bus = $request->user()->assignedBus;

        if (! $bus) {
            return response()->json([
                'message' => 'No bus assigned to this driver.',
            ], 404);
        }

        $bus->update([
            'status' => 'active',
        ]);

        return response()->json([
            'message' => 'Trip started successfully.',
            'bus_id' => $bus->id,
        ]);
    }

    public function store(DriverLocationRequest $request)
    {
        $bus = $request->user()->assignedBus;

        if (! $bus) {
            return response()->json([
                'message' => 'No bus assigned to this driver.',
            ], 404);
        }

        $data = $request->validated();
        $now = now();

        BusLocation::updateOrCreate(
            ['bus_id' => $bus->id],
            [
                'lat' => $data['lat'],
                'lng' => $data['lng'],
                'speed' => $data['speed'] ?? 0,
                'heading' => $data['heading'] ?? 0,
                'is_online' => true,
                'recorded_at' => $now,
            ]
        );

        GpsLog::create([
            'bus_id' => $bus->id,
            'lat' => $data['lat'],
            'lng' => $data['lng'],
            'speed' => $data['speed'] ?? 0,
            'heading' => $data['heading'] ?? 0,
            'is_online' => true,
            'recorded_at' => $now,
        ]);

        return response()->json([
            'message' => 'Location updated successfully.',
        ]);
    }

    public function endTrip(Request $request)
    {
        $bus = $request->user()->assignedBus;

        if (! $bus) {
            return response()->json([
                'message' => 'No bus assigned to this driver.',
            ], 404);
        }

        $bus->update([
            'status' => 'stopped',
        ]);

        BusLocation::where('bus_id', $bus->id)->update([
            'is_online' => false,
            'recorded_at' => now(),
        ]);

        return response()->json([
            'message' => 'Trip ended successfully.',
        ]);
    }
}