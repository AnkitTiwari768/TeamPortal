<?php

declare(strict_types=1);

namespace App\Domain\LogViewer;

class LogParser
{
    /**
     * Monolog entry matching regex
     */
    private const LOG_HEADER_REGEX = '/^\[(\d{4}-\d{2}-\d{2}[T ]\d{2}:\d{2}:\d{2}(?:\.\d+)?(?:[\+-]\d{2}:?\d{2}|Z)?)\]\s+([\w\.-]+)\.([a-zA-Z]+):\s*(.*)$/s';

    /**
     * Map levels to Bootstrap badge classes
     */
    private const LEVEL_CLASSES = [
        'EMERGENCY' => 'bg-dark text-white',
        'ALERT'     => 'bg-danger text-white',
        'CRITICAL'  => 'bg-danger text-white',
        'ERROR'     => 'bg-danger text-white',
        'WARNING'   => 'bg-warning text-dark',
        'NOTICE'    => 'bg-info text-white',
        'INFO'      => 'bg-primary text-white',
        'DEBUG'     => 'bg-secondary text-white',
    ];

    /**
     * Parse a multiline log entry string into a structured array.
     *
     * @return array{
     *   id: string,
     *   timestamp: string,
     *   date: string,
     *   time: string,
     *   environment: string,
     *   level: string,
     *   level_class: string,
     *   message: string,
     *   full_message: string,
     *   stack_trace: string|null,
     *   context: array|null,
     *   raw: string
     * }
     */
    public function parse(string $rawEntry): array
    {
        $rawEntry = trim($rawEntry);
        $id = md5($rawEntry);

        if (preg_match(self::LOG_HEADER_REGEX, $rawEntry, $matches)) {
            $timestamp = $matches[1];
            $environment = $matches[2];
            $level = strtoupper($matches[3]);
            $body = $matches[4];

            $date = substr($timestamp, 0, 10);
            $time = strlen($timestamp) >= 19 ? substr($timestamp, 11, 8) : '';

            $bodyLines = explode("\n", $body, 2);
            $firstLine = trim($bodyLines[0]);
            $extraContent = isset($bodyLines[1]) ? trim($bodyLines[1]) : '';

            $context = $this->extractContext($firstLine) ?? $this->extractContext($body);

            return [
                'id' => $id,
                'timestamp' => $timestamp,
                'date' => $date,
                'time' => $time,
                'environment' => $environment,
                'level' => $level,
                'level_class' => self::LEVEL_CLASSES[$level] ?? 'bg-secondary text-white',
                'message' => $firstLine,
                'full_message' => $body,
                'stack_trace' => $extraContent !== '' ? $extraContent : null,
                'context' => $context,
                'raw' => $rawEntry,
            ];
        }

        return [
            'id' => $id,
            'timestamp' => 'N/A',
            'date' => '',
            'time' => '',
            'environment' => 'unknown',
            'level' => 'UNKNOWN',
            'level_class' => 'bg-secondary text-white',
            'message' => mb_substr($rawEntry, 0, 200),
            'full_message' => $rawEntry,
            'stack_trace' => null,
            'context' => null,
            'raw' => $rawEntry,
        ];
    }

    /**
     * Safely attempt to extract JSON context from text
     */
    private function extractContext(string $text): ?array
    {
        $text = trim($text);
        if ($text === '') {
            return null;
        }

        $firstBrace = strpos($text, '{');
        $firstBracket = strpos($text, '[');

        if ($firstBrace === false && $firstBracket === false) {
            return null;
        }

        $startPos = ($firstBrace !== false && $firstBracket !== false)
            ? min($firstBrace, $firstBracket)
            : ($firstBrace !== false ? $firstBrace : $firstBracket);

        $substr = substr($text, $startPos);
        $lastBrace = strrpos($substr, '}');
        $lastBracket = strrpos($substr, ']');
        $endPos = max($lastBrace !== false ? $lastBrace : -1, $lastBracket !== false ? $lastBracket : -1);

        if ($endPos !== -1) {
            $jsonCandidate = substr($substr, 0, $endPos + 1);
            $decoded = json_decode($jsonCandidate, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }
        }

        return null;
    }
}
