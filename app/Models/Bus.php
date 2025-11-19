<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Bus extends Model
{
 use HasFactory;

    protected $fillable = [
        'route_id',
        'number',
        'type',
        'capacity',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function route()
    {
        return $this->belongsTo(Route::class);
    }

    public function locations()
    {
        return $this->hasMany(BusLocations::class)->latest();
    }
}
