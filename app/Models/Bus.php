<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bus extends Model
{
    protected $fillable = [
        'route_id', 'gps_device_id', 'plate_number',
        'type', 'capacity', 'status',
    ];

    public function route()
    {
        return $this->belongsTo(Route::class);
    }

    public function gpsDevice()
    {
        return $this->belongsTo(GpsDevice::class);
    }

    public function location()
    {
        return $this->hasOne(BusLocation::class)->latestOfMany();
    }

    public function locations()
    {
        return $this->hasMany(BusLocation::class);
    }

    public function gpsLogs()
    {
        return $this->hasMany(GpsLog::class);
    }
    public function favorites()
{
    return $this->morphMany(Favorite::class, 'favorable');
}
}