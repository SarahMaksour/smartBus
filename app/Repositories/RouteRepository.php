<?php
namespace App\Repositories;

use App\Models\Route;

class RouteRepository{
    public function getAllRoutes(){
        return Route::select('id','name','is_active')->get();
    }
    public function getById($id){
        return Route::with('stops','paths')->findOrFail($id);
    }
    public function getStops($id){
        return Route::findOrFail($id)->stops()->orderBy('order_index')->get();
    }
    public function getPath($id)
    {
        return Route::findOrFail($id)->paths()->orderBy('order_index')->get();
    }
}