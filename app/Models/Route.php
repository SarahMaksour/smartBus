<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Route extends Model
{
   use HasFactory;
    protected $fillable = [
       'code',
       'name',
       'is_active',
       'start_lat',
       'start_lng',
       'end_lat',
       'end_lng'
    ];
protected $casts = ['is_active'=>'boolean'];
    public function stops(){
        return $this->hasMany(Stop::class);
    }
    public function buses(){
        return $this->hasMany(Bus::class);
    }
    public function paths()
    {
        return $this->hasMany(RoutePaths::class)->orderBy('order_index');
    }
}
