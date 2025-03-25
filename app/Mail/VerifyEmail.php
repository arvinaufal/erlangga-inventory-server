<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class VerifyEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $encryptedEmail;
    public $frontendUrl;

    /**
     * Create a new message instance.
     *
     * @param string $encryptedEmail
     * @param string $frontendUrl
     */
    public function __construct($encryptedEmail, $frontendUrl)
    {
        $this->encryptedEmail = $encryptedEmail;
        $this->frontendUrl = $frontendUrl;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Verify Your Email Address')
                    ->view('email.verify_email'); // Blade template untuk email
    }
}