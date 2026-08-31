<?php

declare(strict_types=1);

namespace App\Domain\ForgotUdyam;

use App\Domain\EmailTemplate\EmailTemplateService;
use Illuminate\Support\Facades\DB;

class ForgotUdyamAction
{
    public function execute(string $email, string $mobile)
    {
        $msme = DB::table('team_msme_schemes')
            ->where('email', $email)
            ->where('mobile', $mobile)
            ->first();

        if ($msme) {
            app(EmailTemplateService::class)->send(
                templateKey: 'msme-udyam-number-issued',
                toEmail: $email,
                data: [
                    'name' => $msme->entrepreneur_name,
                    'udyam_number' => $msme->udyam_no,
                ]
            );
        }
    }
}
