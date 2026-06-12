<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\FavoriteResource;
use App\Models\Favorite;
use App\Services\FavoriteService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FavoriteController extends Controller
{
    public function __construct(private FavoriteService $service) {}

    public function toggle(Request $request)
    {
        $request->validate([
            'type'         => ['required', Rule::in(['route', 'station'])],
            'id'           => ['required', 'integer'],
            'custom_label' => ['nullable', 'string', 'max:50'],
        ]);

        return response()->json(
            $this->service->toggle(
                $request->user(),
                $request->type,
                $request->id,
                $request->custom_label
            )
        );
    }

    public function index(Request $request)
    {
        $favorites = Favorite::with('favorable')
            ->where('user_id', $request->user()->id)
            ->latest()
            ->get();

        return FavoriteResource::collection($favorites);
    }
}