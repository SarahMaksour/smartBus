<?php

namespace App\Http\Controllers\Route;

use App\Http\Controllers\Controller;
use App\Services\RouteService;
use Illuminate\Http\Request;

class RouteController extends Controller
{
    protected $routeService;
    public function __construct(RouteService $routeService)
    {
        $this->routeService=$routeService;
    }
    public function index()
    {
        return response()->json([
            'routes' => $this->routeService->getAllRoutes()
        ]);
    }
    // GET /api/routes/{id}
    public function show($id)
    {
        return response()->json(
            $this->routeService->getRouteDetails($id)
        );
    }

    // GET /api/routes/{id}/stops
    public function stops($id)
    {
        return response()->json([
            'stops' => $this->routeService->getRouteStops($id)
        ]);
    }

    // GET /api/routes/{id}/path
    public function path($id)
    {
        return response()->json([
            'path' => $this->routeService->getRoutePath($id)
        ]);
    }
}
