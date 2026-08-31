<?php

declare(strict_types=1);

namespace App\Domain\LogViewer;

use InvalidArgumentException;

class LogReader
{
    private const LOG_HEADER_REGEX = '/^\[\d{4}-\d{2}-\d{2}[T ]\d{2}:\d{2}:\d{2}(?:\.\d+)?(?:[\+-]\d{2}:?\d{2}|Z)?\]\s+[\w\.-]+\.[a-zA-Z]+:/';

    public function __construct(
        private LogFileService $fileService,
        private LogParser $parser
    ) {}

    /**
     * Read, filter, and paginate log entries efficiently in reverse order.
     *
     * @param string $filename
     * @param array{
     *   search?: string|null,
     *   level?: string|null,
     *   from_date?: string|null,
     *   to_date?: string|null,
     *   from_time?: string|null,
     *   to_time?: string|null,
     *   page?: int,
     *   per_page?: int
     * } $options
     * @return array
     */
    public function getPaginatedEntries(string $filename, array $options = []): array
    {
        $filePath = $this->fileService->sanitizeFileName($filename);

        $search = isset($options['search']) && trim((string)$options['search']) !== '' ? trim((string)$options['search']) : null;
        $level = isset($options['level']) && trim((string)$options['level']) !== '' ? strtoupper(trim((string)$options['level'])) : null;
        $fromDate = !empty($options['from_date']) ? $this->normalizeDate((string)$options['from_date']) : null;
        $toDate = !empty($options['to_date']) ? $this->normalizeDate((string)$options['to_date']) : null;
        $fromTime = !empty($options['from_time']) ? trim((string)$options['from_time']) : null;
        $toTime = !empty($options['to_time']) ? trim((string)$options['to_time']) : null;

        $page = max(1, (int)($options['page'] ?? 1));
        $perPage = isset($options['per_page']) ? max(1, min(500, (int)$options['per_page'])) : 50;

        $offset = ($page - 1) * $perPage;

        $handle = fopen($filePath, 'rb');
        if (!$handle) {
            throw new InvalidArgumentException("Unable to open log file: {$filename}");
        }

        $fileSize = filesize($filePath);
        if ($fileSize === 0) {
            fclose($handle);
            return [
                'entries' => [],
                'pagination' => [
                    'total' => 0,
                    'per_page' => $perPage,
                    'current_page' => 1,
                    'last_page' => 1,
                    'from' => 0,
                    'to' => 0,
                ],
                'summary' => [
                    'total' => 0,
                    'errors' => 0,
                    'critical' => 0,
                    'warnings' => 0,
                    'info' => 0,
                ],
                'file_info' => [
                    'name' => basename($filePath),
                    'size_bytes' => 0,
                    'size_formatted' => '0 B',
                    'updated_at' => date('Y-m-d H:i:s', filemtime($filePath) ?: time()),
                ]
            ];
        }

        $chunkSize = 65536; // 64 KB chunk
        $pos = $fileSize;
        $buffer = '';
        $currentLines = [];
        $entries = [];

        $totalMatched = 0;
        $summary = [
            'total' => 0,
            'errors' => 0,
            'critical' => 0,
            'warnings' => 0,
            'info' => 0,
        ];

        while ($pos > 0) {
            $readSize = min($chunkSize, $pos);
            $pos -= $readSize;
            fseek($handle, $pos);
            $chunk = fread($handle, $readSize);
            $buffer = $chunk . $buffer;

            $lines = explode("\n", $buffer);

            if ($pos > 0) {
                $buffer = array_shift($lines);
            } else {
                $buffer = '';
            }

            for ($i = count($lines) - 1; $i >= 0; $i--) {
                $line = rtrim($lines[$i], "\r");

                if ($line === '' && empty($currentLines)) {
                    continue;
                }

                if (preg_match(self::LOG_HEADER_REGEX, $line)) {
                    array_unshift($currentLines, $line);
                    $rawEntry = implode("\n", $currentLines);
                    $currentLines = [];

                    $parsed = $this->parser->parse($rawEntry);

                    if ($this->matchesFilters($parsed, $search, $level, $fromDate, $toDate, $fromTime, $toTime)) {
                        $totalMatched++;
                        $this->updateSummary($summary, $parsed['level']);

                        if ($totalMatched > $offset && count($entries) < $perPage) {
                            $entries[] = $parsed;
                        }
                    }
                } else {
                    array_unshift($currentLines, $line);
                }
            }
        }

        if (!empty($currentLines)) {
            $rawEntry = implode("\n", $currentLines);
            $parsed = $this->parser->parse($rawEntry);
            if ($this->matchesFilters($parsed, $search, $level, $fromDate, $toDate, $fromTime, $toTime)) {
                $totalMatched++;
                $this->updateSummary($summary, $parsed['level']);
                if ($totalMatched > $offset && count($entries) < $perPage) {
                    $entries[] = $parsed;
                }
            }
        }

        fclose($handle);

        $summary['total'] = $totalMatched;
        $lastPage = (int)ceil($totalMatched / $perPage);
        if ($lastPage < 1) {
            $lastPage = 1;
        }

        return [
            'entries' => $entries,
            'pagination' => [
                'total' => $totalMatched,
                'per_page' => $perPage,
                'current_page' => $page,
                'last_page' => $lastPage,
                'from' => $totalMatched > 0 ? $offset + 1 : 0,
                'to' => $totalMatched > 0 ? min($offset + count($entries), $totalMatched) : 0,
            ],
            'summary' => $summary,
            'file_info' => [
                'name' => basename($filePath),
                'size_bytes' => $fileSize,
                'size_formatted' => $this->fileService->formatBytes($fileSize),
                'updated_at' => date('Y-m-d H:i:s', filemtime($filePath) ?: time()),
            ]
        ];
    }

    private function matchesFilters(
        array $entry,
        ?string $search,
        ?string $level,
        ?string $fromDate,
        ?string $toDate,
        ?string $fromTime,
        ?string $toTime
    ): bool {
        // Level filter
        if ($level !== null && $level !== 'ALL') {
            if ($level === 'ONLY_ERRORS') {
                if (!in_array($entry['level'], ['EMERGENCY', 'ALERT', 'CRITICAL', 'ERROR'], true)) {
                    return false;
                }
            } elseif ($entry['level'] !== $level) {
                return false;
            }
        }

        // Date filter
        if ($fromDate !== null && $entry['date'] !== '') {
            if (strcmp($entry['date'], $fromDate) < 0) {
                return false;
            }
        }
        if ($toDate !== null && $entry['date'] !== '') {
            if (strcmp($entry['date'], $toDate) > 0) {
                return false;
            }
        }

        // Time filter
        if ($fromTime !== null && $entry['time'] !== '') {
            $entryTime = strlen($entry['time']) === 5 ? $entry['time'] . ':00' : $entry['time'];
            $fromTimeNormalized = strlen($fromTime) === 5 ? $fromTime . ':00' : $fromTime;
            if (strcmp($entryTime, $fromTimeNormalized) < 0) {
                return false;
            }
        }
        if ($toTime !== null && $entry['time'] !== '') {
            $entryTime = strlen($entry['time']) === 5 ? $entry['time'] . ':00' : $entry['time'];
            $toTimeNormalized = strlen($toTime) === 5 ? $toTime . ':00' : $toTime;
            if (strcmp($entryTime, $toTimeNormalized) > 0) {
                return false;
            }
        }

        // Search filter (case-insensitive)
        if ($search !== null) {
            if (mb_stripos($entry['raw'], $search) === false) {
                return false;
            }
        }

        return true;
    }

    private function updateSummary(array &$summary, string $level): void
    {
        switch ($level) {
            case 'EMERGENCY':
            case 'ALERT':
            case 'CRITICAL':
                $summary['critical']++;
                $summary['errors']++;
                break;
            case 'ERROR':
                $summary['errors']++;
                break;
            case 'WARNING':
                $summary['warnings']++;
                break;
            case 'INFO':
            case 'NOTICE':
                $summary['info']++;
                break;
        }
    }

    private function normalizeDate(string $dateStr): ?string
    {
        $dateStr = trim($dateStr);
        if ($dateStr === '') {
            return null;
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateStr)) {
            return $dateStr;
        }

        if (preg_match('/^(\d{2})-(\d{2})-(\d{4})$/', $dateStr, $m)) {
            return "{$m[3]}-{$m[2]}-{$m[1]}";
        }

        $time = strtotime($dateStr);
        return $time ? date('Y-m-d', $time) : null;
    }
}
