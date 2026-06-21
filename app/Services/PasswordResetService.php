<?php

namespace App\Services;

use App\Models\PasswordResetOtp;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;

class PasswordResetService
{
    public function sendOtp(string $email): array
    {
        $user = User::where('email', $email)->first();

        if (! $user) {
            return ['found' => false];
        }

        PasswordResetOtp::where('email', $email)->delete();

        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        PasswordResetOtp::create([
            'email'      => $email,
            'otp'        => $otp,
            'is_used'    => false,
            'expires_at' => now()->addMinutes(10),
        ]);

        $this->sendViaApi($email, $otp);

        return ['found' => true];
    }

    private function sendViaApi(string $email, string $otp): void
    {
        $token   = config('services.mailtrap.token');
        $inboxId = config('services.mailtrap.inbox_id');

        Http::withToken($token)
            ->post("https://sandbox.api.mailtrap.io/api/send/{$inboxId}", [
                'from'    => ['email' => 'noreply@smartbus.com', 'name' => 'SmartBus'],
                'to'      => [['email' => $email]],
                'subject' => 'كود إعادة تعيين كلمة المرور - SmartBus',
                'html'    => "<h1>كود التحقق: {$otp}</h1><p>صالح لمدة 10 دقائق.</p>",
            ]);
    }

    public function verifyOtp(string $email, string $otp): bool
    {
        $record = PasswordResetOtp::where('email', $email)
            ->where('is_used', false)
            ->latest()
            ->first();

        if (! $record) return false;

        return $record->isValid($otp);
    }

    public function resetPassword(string $email, string $otp, string $newPassword): bool
    {
        $record = PasswordResetOtp::where('email', $email)
            ->where('is_used', false)
            ->latest()
            ->first();

        if (! $record || ! $record->isValid($otp)) return false;

        $record->update(['is_used' => true]);

        User::where('email', $email)->update([
            'password' => Hash::make($newPassword),
        ]);

        return true;
    }
}