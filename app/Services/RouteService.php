<?php
namespace App\Services;

use App\Repositories\RouteRepository;

class RouteService{
    protected $routeRepository;
     
    public function __construct(RouteRepository $routeRepository)
    {
    $this->routeRepository=$routeRepository;
    }
    public function getAllRoutes(){
        return $this->routeRepository->getAllRoutes();
    }
    public function getRouteDetails($id)
    {
        return $this->routeRepository->getById($id);
    }

    public function getRouteStops($id)
    {
        return $this->routeRepository->getStops($id);
    }

    public function getRoutePath($id)
    {
        return $this->routeRepository->getPath($id);
    }
}