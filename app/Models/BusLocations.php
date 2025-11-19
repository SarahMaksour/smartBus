<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BusLocations extends Model
{
    use HasFactory;

    protected $fillable = [
        'bus_id',
        'lat',
        'lng',
        'speed',
        'direction',
        'is_online',
    ];

    protected $casts = [
        'is_online' => 'boolean',
        'lat' => 'decimal:7',
        'lng' => 'decimal:7',
        'speed' => 'decimal:2',
        'direction' => 'decimal:2',
    ];

    public function bus()
    {
        return $this->belongsTo(Bus::class);
    }
}
