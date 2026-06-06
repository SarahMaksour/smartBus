<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function updateNotifications(Request $request)
{
    $validated = $request->validate([
        'enable' => 'required|boolean',
    ]);

    $user = $request->user();

    $user->update([
        'notifications_enable' => $validated['enable']
    ]);

    return response()->json([
        'success' => true,
        'enable' => $user->notifications_enable
    ]);
}
}
