<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RouteStationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
 return [
            'id'    => $this->station->id,
            'name'  => $this->station->name,
            'order' => $this->order_index,
            'lat'   => (float) $this->station->lat,
            'lng'   => (float) $this->station->lng,
        ];    }
}
