<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $otp,
    ) {}

    public function build()
    {
        return $this->subject('كود إعادة تعيين كلمة المرور - SmartBus')
            ->html("
                <div style='font-family: Arial; direction: rtl; text-align: right; padding: 20px;'>
                    <h2 style='color: #1D9E75;'>SmartBus</h2>
                    <p>كود التحقق الخاص بك:</p>
                    <h1 style='color: #4F46E5; letter-spacing: 10px;'>{$this->otp}</h1>
                    <p>صالح لمدة 10 دقائق فقط.</p>
                </div>
            ");
    }
}