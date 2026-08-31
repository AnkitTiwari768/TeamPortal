<?php

declare(strict_types=1);

namespace App\Domain\BPPIDUpdate;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class BppIdListExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(private BPPIDUpdateListService $service)
    {
    }

    public function collection()
    {
        return $this->service->getExportRows();
    }

    public function headings(): array
    {
        return [
            'Old BPPID',
            'New BPPID',
            'Updated At',
            'Udyam No.',
        ];
    }

    public function map($row): array
    {
        return [
            '-',
            $row->bpp_id ?: '-',
            $row->bpp_updated_at ? date('d-m-Y H:i:s', strtotime($row->bpp_updated_at)) : '-',
            $row->udyam_no,
        ];
    }
}
