<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoutePath extends Model
{
    protected $fillable = [
        'route_id', 'lat', 'lng', 'order_index',
    ];

    public function route()
    {
        return $this->belongsTo(Route::class);
    }
}