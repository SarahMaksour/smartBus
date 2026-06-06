<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Station extends Model
{
    protected $fillable = [
        'name', 'lat', 'lng', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'lat' => 'decimal:7',
        'lng' => 'decimal:7',
    ];

    public function routes()
    {
        return $this->belongsToMany(Route::class, 'route_stations')
                    ->withPivot('order_index', 'estimated_time_from_start', 'distance_from_start');
    }

    public function routeStations()
    {
        return $this->hasMany(RouteStation::class);
    }
    public function favorites()
{
    return $this->morphMany(Favorite::class, 'favorable');
}
}