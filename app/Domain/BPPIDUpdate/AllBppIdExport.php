<?php

declare(strict_types=1);

namespace App\Domain\BPPIDUpdate;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class AllBppIdExport implements FromCollection, WithHeadings
{
    public function __construct(private array $rows)
    {
    }

    public function collection()
    {
        return collect($this->rows)->map(fn ($row) => [
            $row['old_bpp_id'] ?? '-',
            $row['new_bpp_id'] ?? '-',
            $row['updated_at'] ?? '-',
            $row['udyam_no'] ?? '-',
        ]);
    }

    public function headings(): array
    {
        return ['Old BPPID', 'New BPPID', 'Updated At', 'Udyam No.'];
    }
}
