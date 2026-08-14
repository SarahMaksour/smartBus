<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\RouteDetailResource;
use App\Models\Route;
use Illuminate\Http\Request;

class RouteController extends Controller
{
    public function show(int $id)
    {
        $route = Route::where('is_active', true)
            ->with([
                'routeStations.station',
                'buses' => fn($q) => $q->where('status', 'active')
                                       ->with(['location', 'route.routeStations.station']),
            ])
            ->findOrFail($id);

        return new RouteDetailResource($route);
    }
}
