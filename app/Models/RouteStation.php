<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RouteStation extends Model
{
    protected $fillable = [
        'route_id', 'station_id', 'order_index',
        'estimated_time_from_start', 'distance_from_start',
    ];

    public function route()
    {
        return $this->belongsTo(Route::class);
    }

    public function station()
    {
        return $this->belongsTo(Station::class);
    }
}