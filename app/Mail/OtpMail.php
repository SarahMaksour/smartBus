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
        return $this->subject('كود إعادة تعيين كلمة المرور')
            ->html("<h1>كود التحقق: {$this->otp}</h1>");
    }
}