<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RoutePaths extends Model
{
   use HasFactory;

    protected $fillable = [
        'route_id',
        'lat',
        'lng',
        'order_index',
    ];

    public function route()
    {
        return $this->belongsTo(Route::class);
    }
}
