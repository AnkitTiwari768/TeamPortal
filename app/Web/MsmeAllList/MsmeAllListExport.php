<?php

declare(strict_types=1);

namespace App\Web\MsmeAllList;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class MsmeAllListExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(private MsmeAllListService $service)
    {
    }

    public function collection()
    {
        return $this->service->getExportRows();
    }

    public function headings(): array
    {
        return [
            'Team ID',
            'Udyam Number',
            'Mobile',
            'Email',
            'Entrepreneur Name',
            'Enterprise Name',
            'Enterprise Type',
            'Major Activity',
            'State',
            'Registration Date',
            'Transaction Type',
            'Incorporation Date',
        ];
    }

    public function map($row): array
    {
        return [
            $row->team_id,
            $row->udyam_no,
            $row->mobile,
            $row->email,
            $row->entrepreneur_name,
            $row->enterprise_name,
            $row->organisation_type,
            $row->major_activity,
            $row->state_name,
            date('d-m-Y', strtotime($row->created_at)),
            $row->transaction_type,
            $row->incorporation_date,
        ];
    }
}
