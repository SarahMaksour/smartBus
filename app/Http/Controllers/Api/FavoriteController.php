<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Favorite;
use App\Services\FavoriteService;

use Illuminate\Http\Request;

class FavoriteController extends Controller
{
public function __construct(private FavoriteService $service) {}

    public function toggle(Request $request)
    {
        $request->validate([
            'type' => 'required',
            'id' => 'required',
            'custom_label' => 'nullable'
        ]);

        return response()->json(
            $this->service->toggle(
                auth()->user(),
                $request->type,
                $request->id,
                $request->custom_label
            )
        );
    }

    public function index()
    {
        return Favorite::with('favorable')
            ->where('user_id', auth()->id())
            ->latest()
            ->get();
    }
}
