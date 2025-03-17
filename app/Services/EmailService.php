<?php

namespace App\Services;

use Illuminate\Support\Facades\Mail;
use Exception;

class EmailService
{
    protected $smtpConfig;

    public function __construct()
    {
        $this->smtpConfig = [
            'host' => env("HOST_SMTP"),
            'port' => env("PORT_SMTP"),
            'username' => env("USERNAME_SMTP"),
            'password' => env("PASSWORD_SMTP"),
            'encryption' => env("ENCRYPTION_SMTP"),
        ];
    }

    /**
     * Send an email with a personalized message and app link using the email template.
     *
     * @param string $toEmail
     * @param string $templateName
     * @param array $templateData

     * @return bool
     */
    public function sendEmailWithTemplate($toEmail, $templateName , $templateData)
    {
        try {
            // Set the mail configuration dynamically using the class property
            config([
                'mail.mailers.smtp.host' => $this->smtpConfig['host'],
                'mail.mailers.smtp.port' => $this->smtpConfig['port'],
                'mail.mailers.smtp.username' => $this->smtpConfig['username'],
                'mail.mailers.smtp.password' => $this->smtpConfig['password'],
                'mail.mailers.smtp.encryption' => $this->smtpConfig['encryption'],
            ]);

            Mail::send($templateName, $templateData, function ($message) use ($toEmail) {
                $message->to($toEmail)
                        ->subject('Complétez votre inscription chez Stockfri')
                        ->from($this->smtpConfig['username']);
            });

            return true;
        } catch (Exception $e) {
            // Log the exception or handle it as per your needs
            return false;
        }
    }
}
