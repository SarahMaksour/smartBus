<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Route extends Model
{
    protected $fillable = [
        'code', 'name', 'direction', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function stations()
    {
        return $this->belongsToMany(Station::class, 'route_stations')
                    ->withPivot('order_index', 'estimated_time_from_start', 'distance_from_start')
                    ->orderBy('route_stations.order_index');
    }

    public function routeStations()
    {
        return $this->hasMany(RouteStation::class)->orderBy('order_index');
    }

    public function buses()
    {
        return $this->hasMany(Bus::class);
    }

    public function paths()
    {
        return $this->hasMany(RoutePath::class)->orderBy('order_index');
    }
    public function favorites()
{
    return $this->morphMany(Favorite::class, 'favorable');
}
}