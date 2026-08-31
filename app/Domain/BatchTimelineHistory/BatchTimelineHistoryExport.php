<?php

declare(strict_types=1);

namespace App\Domain\BatchTimelineHistory;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class BatchTimelineHistoryExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(private BatchTimelineHistoryListQuery $query)
    {
    }

    public function collection()
    {
        return $this->query->getExportRows();
    }

    public function headings(): array
    {
        return [
            'Organization Name',
            'Batch Number',
            'Subject',
            'Action',
            'Status',
            'Date',
            'Created By Role',
        ];
    }

    public function map($row): array
    {
        return [
            $row->organization_name,
            $row->batch_number,
            $row->subject,
            $row->action,
            $row->status,
            date('d-m-Y H:i:s', strtotime($row->created_at)),
            $row->created_by_role,
        ];
    }
}
