<?php

namespace App\Web\PmvBulkRegistration;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class PMVBulkImport implements ToCollection, WithChunkReading
{
    private $service;
    private $headerChecked = false;

    private $expectedHeader = [
        'owner_name','store_name','pmv_id','mobile',
        'email','pan','pin_code','address','product_category'
    ];

    public function __construct(PMVBulkUploadService $service)
    {
        $this->service = $service;
    }

    public function collection(Collection $rows)
    {
         // ✅ Total rows count check (excluding first 2 rows)
        $dataRowCount = $rows->count() - 2;
        if ($dataRowCount > 5000) {
          throw new \Exception("Upload limit exceeded: Maximum 5000 records allowed, but your file contains {$dataRowCount} records. Please remove extra rows and re-upload.");
        }
        foreach ($rows as $index => $row) {

            $rowArray = array_map('trim', $row->toArray());

            /* ================= SKIP ROW 1 ================= */
            if ($index == 0) {
                continue; // Mandatory row
            }

            /* ================= HEADER CHECK (ROW 2) ================= */
            if ($index == 1 && !$this->headerChecked) {

                $headers = array_map(function ($h) {
                    return strtolower(trim($h));
                }, $rowArray);

                $missing = array_diff($this->expectedHeader, $headers);
                $extra   = array_diff($headers, $this->expectedHeader);

                if (!empty($missing) || !empty($extra)) {
                    throw new \Exception("Invalid Excel format. Please upload file using the provided template.");
                }

                $this->headerChecked = true;
                continue; // skip header row
            }

            /* ================= DATA ROWS ================= */

            // skip empty rows
            if (empty(array_filter($rowArray))) {
                continue;
            }

            $this->service->processRowByIndex($rowArray, $index + 1);
        }
    }

    public function chunkSize(): int
    {
        return 500;
    }
}