<?php 

declare(strict_types=1);

namespace App\Traits;

trait Settings 
{
    public bool $isTwoFactorAuthenticationEnabled = true;
    public bool $sendEmailNotification = true;
    public bool $sendSmsNotification = true;
}