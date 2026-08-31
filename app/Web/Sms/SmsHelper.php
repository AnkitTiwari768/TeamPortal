<?php

declare(strict_types=1);

namespace App\Web\Sms;

class SmsHelper
{
    public static function populateTemplate(string $template, array $variables, int $expectedCount): string
    {
        // Match all placeholders like {#var#}, {#Var#}, {#VAR#}, etc.
        preg_match_all('/\{#var#\}/i', $template, $matches);

        $placeholderCount = count($matches[0]);

        if ($placeholderCount !== $expectedCount) {
            throw new \InvalidArgumentException("Mismatch: expected $expectedCount variables, found $placeholderCount placeholders.");
        }

        if (count($variables) !== $expectedCount) {
            throw new \InvalidArgumentException("Mismatch: expected $expectedCount replacement values, received " . count($variables));
        }

        // Replace placeholders in order
        $index = 0;
        $final = preg_replace_callback('/\{#var#\}/i', function () use (&$variables, &$index) {
            return $variables[$index++];
        }, $template);

        return $final;
    }
}
