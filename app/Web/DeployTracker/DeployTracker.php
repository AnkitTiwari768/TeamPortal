<?php

namespace App\Web\DeployTracker;

use Illuminate\Support\Facades\Log;

class DeployTracker
{
    private string $basePath;
    private string $snapshotFile;
    private string $logFile;

    private array $ignore = [
        'vendor',
        'node_modules',
        '.git',
        'storage',
        'bootstrap/cache',
        '.env',
        'tests',
    ];

    private array $skipFiles = [
        'snapshot.json',
        'changes.log',
        '.DS_Store',
        'Thumbs.db',
    ];

    public function __construct()
    {
        $this->basePath = base_path();
        $this->snapshotFile = base_path('app/Web/DeployTracker/snapshot.json');
        $this->logFile = base_path('app/Web/DeployTracker/changes.log');

        if (!is_dir(dirname($this->snapshotFile))) {
            mkdir(dirname($this->snapshotFile), 0777, true);
        }
    }

    public function track(string $developer = 'auto'): void
    {
        try {
            Log::info('🔍 DeployTracker: Scan started...');

            $current = $this->scan($this->basePath);
            $previous = $this->loadSnapshot();

            $newFiles = [];
            $modified = [];
            $deleted = [];

            foreach ($current as $file => $hash) {
                if (!isset($previous[$file])) {
                    $newFiles[] = $file;
                } elseif ($previous[$file] !== $hash) {
                    $modified[] = $file;
                }
            }

            foreach ($previous as $file => $hash) {
                if (!isset($current[$file])) {
                    $deleted[] = $file;
                }
            }

            Log::info('📊 Changes count - New: ' . count($newFiles) . ', Modified: ' . count($modified) . ', Deleted: ' . count($deleted));

            if (!empty($newFiles) || !empty($modified) || !empty($deleted)) {
                Log::info('📝 Sample changes:', [
                    'new' => array_slice($newFiles, 0, 5),
                    'modified' => array_slice($modified, 0, 5),
                    'deleted' => array_slice($deleted, 0, 5),
                ]);
            }

            $this->saveSnapshot($current);

            if (!empty($newFiles) || !empty($modified) || !empty($deleted)) {
                $this->writeLog($developer, $newFiles, $modified, $deleted);
                Log::info('✅ changes.log updated');
            } else {
                Log::info('ℹ️ No changes found');
            }

        } catch (\Throwable $e) {
            Log::error('❌ DeployTracker error: ' . $e->getMessage());
            Log::error('❌ Trace: ' . $e->getTraceAsString());
        }
    }

    private function scan(string $dir): array
    {
        $result = [];
        if (!is_dir($dir)) {
            return $result;
        }
        $items = scandir($dir);
        if ($items === false) {
            return $result;
        }
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') continue;
            $path = $dir . DIRECTORY_SEPARATOR . $item;

            if (in_array($item, $this->skipFiles)) {
                continue;
            }

            $shouldIgnore = false;
            foreach ($this->ignore as $skip) {
                if (strpos($path, DIRECTORY_SEPARATOR . $skip) !== false ||
                    strpos($path, $skip . DIRECTORY_SEPARATOR) !== false ||
                    strpos($path, $skip) !== false) {
                    $shouldIgnore = true;
                    break;
                }
            }
            if ($shouldIgnore) {
                continue;
            }

            if (is_dir($path)) {
                $result += $this->scan($path);
            } else {
                $relative = str_replace($this->basePath . DIRECTORY_SEPARATOR, '', $path);
                $result[$relative] = hash_file('sha256', $path);
            }
        }
        return $result;
    }

    private function loadSnapshot(): array
    {
        if (!file_exists($this->snapshotFile)) {
            return [];
        }
        $content = file_get_contents($this->snapshotFile);
        return json_decode($content, true) ?? [];
    }

    private function saveSnapshot(array $data): void
    {
        file_put_contents(
            $this->snapshotFile,
            json_encode($data, JSON_PRETTY_PRINT)
        );
    }

    private function writeLog(string $developer, array $newFiles, array $modified, array $deleted): void
    {
        $today = date('Y-m-d');
        $dateHeader = "\n" . str_repeat('=', 60) . "\n";
        $dateHeader .= "===== Date: {$today} =====\n";
        $dateHeader .= str_repeat('=', 60) . "\n";

        // Check if today's header already exists
        $headerExists = false;
        if (file_exists($this->logFile)) {
            $content = file_get_contents($this->logFile);
            preg_match_all('/===== Date: (\d{4}-\d{2}-\d{2}) =====/', $content, $matches, PREG_SET_ORDER);
            if (!empty($matches)) {
                $lastDate = end($matches)[1];
                if ($lastDate === $today) {
                    $headerExists = true;
                }
            }
        }

        // Build the entry (without full date, only time)
        $entry = "Developer: {$developer}\n";
        $entry .= "Time: " . date('H:i:s') . "\n";
        $entry .= "Server: " . gethostname() . "\n\n";

        if (!empty($newFiles)) {
            $entry .= "NEW FILES (" . count($newFiles) . "):\n";
            foreach ($newFiles as $file) {
                $entry .= "  ➕ " . $file . "\n";
            }
            $entry .= "\n";
        }

        if (!empty($modified)) {
            $entry .= "MODIFIED FILES (" . count($modified) . "):\n";
            foreach ($modified as $file) {
                $entry .= "  ✏️ " . $file . "\n";
            }
            $entry .= "\n";
        }

        if (!empty($deleted)) {
            $entry .= "DELETED FILES (" . count($deleted) . "):\n";
            foreach ($deleted as $file) {
                $entry .= "  ❌ " . $file . "\n";
            }
            $entry .= "\n";
        }

        $entry .= str_repeat('=', 60) . "\n";

        // Prepare the final log string
        $log = "";
        if (!$headerExists) {
            $log = $dateHeader;
        }
        $log .= $entry;

        file_put_contents($this->logFile, $log, FILE_APPEND);
    }
}