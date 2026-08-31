<?php

declare(strict_types=1);

namespace App\Domain\BPPIDUpdate;

use Maatwebsite\Excel\Facades\Excel;

class BulkUpdateBppIdData
{
    public static function getRecords($file, string $snpId): array
    {
        $sheets = Excel::toArray([], $file);

        $rows = $sheets[0] ?? [];

        $records = [];
        $invalidRows = [];

        foreach ($rows as $index => $row) {

            // Skip Header Row
            if ($index === 0) {
                continue;
            }

            if (
                empty($row[0]) ||
                empty($row[1])
            ) {
                // Skip fully blank rows, but report partially filled rows as unmapped
                if (empty($row[0]) && empty($row[1])) {
                    continue;
                }

                $invalidRows[] = [
                    'udyam_no' => isset($row[0]) ? trim((string) $row[0]) : '',
                    'bpp_id'   => isset($row[1]) ? trim((string) $row[1]) : '',
                    'reason'   => 'Udyam No or BPP ID missing in uploaded file',
                ];

                continue;
            }

            $records[] = [
                'udyam_no' => trim((string) $row[0]),
                'bpp_id'   => trim((string) $row[1]),
            ];
        }
        return [
             $snpId,
            $records,
            $invalidRows,
        ];
    }
}
