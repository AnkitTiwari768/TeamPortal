<?php


namespace App\Web\MseBulkRegistration;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class FailedUdyamExport implements FromCollection, WithHeadings
{
    protected $failures;

    public function __construct($failures)
    {
        $this->failures = collect($failures)->map(function ($item) {
            return [
                'mobile' => $item['mobile'] ?? '',
                'udyam'  => $item['udyam'] ?? '',
                'reason' => $item['reason'] ?? '',
            ];
        });
    }

    public function collection()
    {
        return $this->failures;
    }

    public function headings(): array
    {
        return ['Mobile', 'Udyam', 'Reason'];
    }
}