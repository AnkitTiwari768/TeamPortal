<?php 

declare(strict_types=1);

namespace App\Traits;

use Mail;

trait Mailer
{
    public function sendMail(
        string $subject, 
        array|string $recipients, 
        string $templatePath, 
        array $templateData,
        ?string $cc = null,
        ?string $from = null,
        ?string $fromEmail = null
    )
    {
        $cc = $cc ?? 'uneecopsteam@gmail.com';
        $from = $from ?? 'FFO';
        $fromEmail = $fromEmail ?? 'no-reply@ffo.gov.in';
        
        return Mail::send($templatePath, $templateData, function($message) use ($recipients, $subject, $cc, $from, $fromEmail) {
            $message->to($recipients)
                ->cc($cc)
                ->subject($subject)
                ->from($fromEmail, $from);
        });		
    }
}
