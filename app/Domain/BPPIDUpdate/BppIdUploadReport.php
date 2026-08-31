<?php

declare(strict_types=1);

namespace App\Domain\BPPIDUpdate;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * Caches the outcome of a single BPP ID bulk-upload (30 min TTL, scoped to
 * the uploading user) and exposes it in a normalized shape - a per-row list
 * (Old BPPID, New BPPID, Updated At, Udyam No.) and summary counts - so the
 * same data backs the immediate on-page cards/table and the Excel/PDF
 * "complete data" downloads for that upload.
 */
class BppIdUploadReport
{
    public const CACHE_PREFIX = 'bppid_report_';

    private const TTL_MINUTES = 30;

    public static function store(array $mapped, array $unmapped): string
    {
        $reportKey = (string) Str::uuid();

        Cache::put(self::CACHE_PREFIX . $reportKey, [
            'user_id'  => Auth::id(),
            'mapped'   => $mapped,
            'unmapped' => $unmapped,
        ], now()->addMinutes(self::TTL_MINUTES));

        return $reportKey;
    }

    public static function find(string $reportKey): ?array
    {
        $report = Cache::get(self::CACHE_PREFIX . $reportKey);

        if (!$report || $report['user_id'] !== Auth::id()) {
            return null;
        }

        return $report;
    }

    /**
     * Every row from the upload - mapped (now saved) and unmapped (never
     * saved) combined - normalized to the same 4 fields shown in the
     * Old BPPID / New BPPID / Updated At / Udyam No. table.
     */
    public static function rows(array $report): array
    {
        $rows = [];

        foreach ($report['mapped'] ?? [] as $row) {
            $rows[] = [
                'old_bpp_id' => $row['old_bpp_id'] ?? null,
                'new_bpp_id' => $row['bpp_id'] ?? null,
                'updated_at' => $row['updated_at'] ?? null,
                'udyam_no'   => $row['udyam_no'] ?? null,
            ];
        }

        foreach ($report['unmapped'] ?? [] as $row) {
            $rows[] = [
                'old_bpp_id' => null,
                'new_bpp_id' => $row['bpp_id'] ?? null,
                'updated_at' => null,
                'udyam_no'   => $row['udyam_no'] ?? null,
            ];
        }

        return $rows;
    }

    public static function summary(array $report): array
    {
        $mapped = $report['mapped'] ?? [];
        $unmapped = $report['unmapped'] ?? [];

        return [
            'total'           => count($mapped) + count($unmapped),
            'mapped'          => count($mapped),
            'unmapped'        => count($unmapped),
            'duplicate'       => collect($mapped)->filter(fn ($r) => !empty($r['is_duplicate']))->count(),
            'already_updated' => collect($mapped)->filter(fn ($r) => !empty($r['already_updated']))->count(),
        ];
    }
}
