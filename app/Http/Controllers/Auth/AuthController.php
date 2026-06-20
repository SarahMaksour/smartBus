<?php

namespace App\Http\Controllers\Auth;
use App\Services\PasswordResetService;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function __construct(
    private readonly PasswordResetService $passwordResetService,
) {}
   public function register(Request $request)
{
    $validated = $request->validate([
        'first_name' => 'required|string|max:255',
        'last_name' => 'required|string|max:255',
        'email' => 'required|string|email|unique:users,email',
        'password' => 'required|string|min:8|confirmed',
    ]);

    $user = User::create([
        'first_name' => $validated['first_name'],
        'last_name'=>$validated['last_name'],
        'email' => $validated['email'],
        'password' => Hash::make($validated['password']),
    ]);

    $token = $user->createToken('auth_token')->plainTextToken;

  return response()->json([
        'success' => true,
        'token' => $token,
        'user' => [
            'id' => $user->id,
            'first_name' => $user->first_name,
             'last_name' => $user->last_name,
            'email' => $user->email,
            'is_admin' => $user->hasRole('admin')
        ]
    ], 201);
}
public function login(Request $request)
{
    $validated = $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    if (!Auth::attempt($validated)) {
        return response()->json([
            'success' => false,
            'message' => 'Invalid credentials'
        ], 401);
    }

    $user = Auth::user();

    $token = $user->createToken('auth_token')->plainTextToken;

    return response()->json([
        'success' => true,
        'token' => $token,
        'user' => [
            'id' => $user->id,
            'first_name' => $user->first_name,
             'last_name' => $user->last_name,
            'email' => $user->email,
            'is_admin' => $user->hasRole('admin')
        ]
    ], 200);
}
public function logout(Request $request)
{
 
    $request->user()->currentAccessToken()->delete();

    return response()->json([
        'success' => true,
        'message' => 'logout successfully'
    ]);
}
public function update(Request $request)
{
    $user = $request->user();

    $validated = $request->validate([
        'first_name' => 'required|string|max:255',
        'last_name' => 'required|string|max:255',
        'email' => 'nullable|email|unique:users,email,' . $user->id,
    ]);

    $user->update($validated);

    return response()->json([
        'success' => true,
        'user' => [
            'id' => $user->id,
           'first_name' => $user->first_name,
             'last_name' => $user->last_name,
            'email' => $user->email,
        ]
    ]);
}
public function changePassword(Request $request)
{
    $user = $request->user();

    $validated = $request->validate([
        'current_password' => 'required',
        'new_password' => 'required|min:8|confirmed',
    ]);


    if (!Hash::check($validated['current_password'], $user->password)) {
        return response()->json([
            'success' => false,
            'message' => 'Current password is incorrect'
        ], 400);
    }

    $user->update([
        'password' => Hash::make($validated['new_password'])
    ]);

    return response()->json([
        'success' => true,
        'message' => 'password changes successfully'
    ]);
}
public function saveFcmToken(Request $request)
{
    $request->validate([
        'fcm_token' => 'required|string'
    ]);

    $user = $request->user();

    $user->update([
        'fcm_token' => $request->fcm_token
    ]);

    return response()->json([
        'success' => true
    ]);
}
public function forgotPassword(Request $request)
{
    $request->validate([
        'email' => ['required', 'email'],
    ]);

    $result = $this->passwordResetService->sendOtp($request->email);

    if (! $result['found']) {
        return response()->json([
            'message' => 'الإيميل غير مسجل',
        ], 404);
    }

    return response()->json([
        'message' => 'تم إرسال كود التحقق',
        'otp'     => $result['otp'],
    ]);
}

public function verifyOtp(Request $request)
{
    $request->validate([
        'email' => ['required', 'email'],
        'otp'   => ['required', 'string', 'size:6'],
    ]);

    $valid = $this->passwordResetService->verifyOtp(
        $request->email,
        $request->otp,
    );

    if (! $valid) {
        return response()->json([
            'message' => 'الكود غير صحيح أو منتهي الصلاحية',
        ], 422);
    }

    return response()->json([
        'message' => 'الكود صحيح',
    ]);
}

public function resetPassword(Request $request)
{
    $request->validate([
        'email'                 => ['required', 'email'],
        'otp'                   => ['required', 'string', 'size:6'],
        'password'              => ['required', 'min:8', 'confirmed'],
        'password_confirmation' => ['required'],
    ]);

    $reset = $this->passwordResetService->resetPassword(
        $request->email,
        $request->otp,
        $request->password,
    );

    if (! $reset) {
        return response()->json([
            'message' => 'الكود غير صحيح أو منتهي الصلاحية',
        ], 422);
    }

    return response()->json([
        'message' => 'تم تغيير كلمة المرور بنجاح',
    ]);
}
}