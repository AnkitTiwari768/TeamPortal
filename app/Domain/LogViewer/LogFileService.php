<?php

declare(strict_types=1);

namespace App\Domain\LogViewer;

use InvalidArgumentException;
use RuntimeException;

class LogFileService
{
    private string $logsPath;

    public function __construct(?string $logsPath = null)
    {
        $this->logsPath = $logsPath ?? storage_path('logs');
    }

    /**
     * List all log files in storage/logs
     *
     * @return array<int, array{name: string, size: int, size_formatted: string, updated_at: string}>
     */
    public function getLogFiles(): array
    {
        if (!is_dir($this->logsPath)) {
            return [];
        }

        $files = glob($this->logsPath . '/*.log');
        if ($files === false) {
            return [];
        }

        $result = [];
        foreach ($files as $file) {
            $filename = basename($file);
            $size = filesize($file);
            $mtime = filemtime($file);

            $result[] = [
                'name' => $filename,
                'size' => $size,
                'size_formatted' => $this->formatBytes($size),
                'updated_at' => $mtime ? date('Y-m-d H:i:s', $mtime) : 'N/A',
            ];
        }

        usort($result, function ($a, $b) {
            return strcmp($b['updated_at'], $a['updated_at']);
        });

        return $result;
    }

    /**
     * Validate and sanitize filename against path traversal attacks.
     *
     * @throws InvalidArgumentException|RuntimeException
     */
    public function sanitizeFileName(string $filename): string
    {
        $basename = basename($filename);
        if ($basename !== $filename || !preg_match('/^[a-zA-Z0-9_\-\.]+\.log$/', $basename)) {
            throw new InvalidArgumentException('Invalid log file name.');
        }

        $realLogsPath = realpath($this->logsPath);
        if ($realLogsPath === false) {
            throw new RuntimeException('Logs directory not found.');
        }

        $targetPath = $this->logsPath . DIRECTORY_SEPARATOR . $basename;
        if (!file_exists($targetPath)) {
            throw new InvalidArgumentException('Log file does not exist.');
        }

        $realTargetPath = realpath($targetPath);
        if ($realTargetPath === false || !str_starts_with($realTargetPath, $realLogsPath)) {
            throw new InvalidArgumentException('Unauthorized file path access detected.');
        }

        return $realTargetPath;
    }

    /**
     * Clear log file contents safely.
     */
    public function clearLogFile(string $filename): bool
    {
        $filePath = $this->sanitizeFileName($filename);
        $handle = @fopen($filePath, 'w');
        if ($handle === false) {
            return false;
        }
        fclose($handle);
        return true;
    }

    public function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = (int)floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
