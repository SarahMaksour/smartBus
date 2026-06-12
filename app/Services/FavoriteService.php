<?php

namespace App\Services;

use App\Models\Favorite;
use App\Models\User;

class FavoriteService
{
    public function toggle(User $user, string $type, int $id, ?string $label = null)
    {
        $model = $this->resolveModel($type);

        $existing = Favorite::where('user_id', $user->id)
            ->where('favorable_type', $model)
            ->where('favorable_id', $id)
            ->first();

        if ($existing) {
            $existing->delete();
            return ['status' => 'removed'];
        }

        Favorite::create([
            'user_id' => $user->id,
            'favorable_type' => $model,
            'favorable_id' => $id,
            'custom_label' => $label
        ]);

        return ['status' => 'added'];
    }

    private function resolveModel($type)
    {
        return match ($type) {
            'route' => \App\Models\Route::class,
            'station' => \App\Models\Station::class,
            default => throw new \Exception("Invalid type")
        };
    }
}
