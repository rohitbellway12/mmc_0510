<?php

namespace Modules\UserManagement\Emails;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class OTPMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    protected string $otp;

    public function __construct($otp)
    {
        $this->otp = $otp;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $businessName = business_config('business_name', 'business_information')?->live_values ?? 'MMC';
        return $this->subject("{$this->otp} is your {$businessName} verification code")
            ->view('usermanagement::mail-templates.otp-sent', ['otp' => $this->otp]);
    }
}
