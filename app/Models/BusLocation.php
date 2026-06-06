<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusLocation extends Model
{
    protected $fillable = [
        'bus_id', 'lat', 'lng', 'speed', 'heading', 'is_online', 'recorded_at',
    ];

    protected $casts = [
        'is_online' => 'boolean',
        'recorded_at' => 'datetime',
    ];

    public function bus()
    {
        return $this->belongsTo(Bus::class);
    }
}