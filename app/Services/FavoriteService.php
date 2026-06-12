<?php

namespace App\Services;

use App\Models\Favorite;
use App\Models\Route;
use App\Models\Station;
use App\Models\User;

class FavoriteService
{
    public function toggle(User $user, string $type, int $id, ?string $label = null): array
    {
        $model = $this->resolveModel($type);

        if (! $model::where('id', $id)->exists()) {
            throw new \InvalidArgumentException('العنصر غير موجود');
        }

        $existing = Favorite::where('user_id', $user->id)
            ->where('favorable_type', $model)
            ->where('favorable_id', $id)
            ->first();

        if ($existing) {
            $existing->delete();
            return ['status' => 'removed'];
        }

        Favorite::create([
            'user_id'        => $user->id,
            'favorable_type' => $model,
            'favorable_id'   => $id,
            'custom_label'   => $label,
        ]);

        return ['status' => 'added'];
    }

    private function resolveModel(string $type): string
    {
        return match ($type) {
            'route'   => Route::class,
            'station' => Station::class,
        };
    }
}