<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Route;
use App\Models\Station;

class FavoriteResource extends JsonResource
{
public function toArray($request): array
{
    $item = $this->favorable;

    return [
        'id'           => $this->id,
        'type'         => $item instanceof Route ? 'route' : ($item instanceof Station ? 'station' : null),
        'custom_label' => $this->custom_label,
        'created_at'   => $this->created_at,
        'item'         => $item ? $this->formatItem($item) : null,
    ];
}
 private function formatItem($item): array
{
    if ($item instanceof Route) {
        return [
            'id'   => $item->id,
            'code' => $item->code,
            'name' => $item->name,
        ];
    }

    if ($item instanceof Station) {
        return [
            'id'   => $item->id,
            'name' => $item->name,
            'lat'  => (float) $item->lat,
            'lng'  => (float) $item->lng,
        ];
    }

    return [];
}
}