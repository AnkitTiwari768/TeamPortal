<?php

declare(strict_types=1);

namespace App\Domain\EmailTemplate;

use App\Domain\EmailTemplate\EmailTemplate;
use App\Services\PHPMailerService;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Exception;

class EmailTemplateService
{
    public function send(
        string $templateKey,
        string $toEmail,
        array $data,
        array $attachments = []
    ): void {
        $template = EmailTemplate::where('template_key', $templateKey)
            ->where('is_active', true)
            ->first();

        if (!$template) {
            throw new Exception('Email template not found or inactive.');
        }

        $this->validateVariables($template, $data);

        $subject = $this->replaceVariables($template->subject, $data);
        $body    = $this->replaceVariables($template->body, $data);

        app(PHPMailerService::class)->sendEmail($toEmail, $subject, $body, null, $attachments);
    }

    protected function replaceVariables(string $content, array $data): string
    {
        foreach ($data as $key => $value) {
            $content = str_replace(
                '{{' . $key . '}}',
                e((string) $value),
                $content
            );
        }

        return $content;
    }

    protected function validateVariables(EmailTemplate $template, array $data): void
    {
        if (!$template->variables) {
            return;
        }
        // dd($template->variables);
        foreach ($template->variables as $key => $type) {
            if (!array_key_exists($key, $data)) {
                throw new Exception("Missing email variable: {$key}");
            }
        }
    }
}
