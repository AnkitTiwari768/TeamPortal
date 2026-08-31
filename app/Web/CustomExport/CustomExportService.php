<?php

namespace App\Web\CustomExport;

use App\Web\SNP\SNPMSMEService;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class CustomExportService implements FromCollection, WithHeadings
{
    protected $ids;
    protected $service;

    public function __construct(array $ids, SNPMSMEService $service)
    {
        $this->ids = $ids;
        $this->service = $service;
    }

    public function collection()
    {
        $data = $this->service->getmyMSMEList($this->ids);
            return $data->map(function($item, $index) {
            return [
                 'Team Id' => $item->team_id,
                'Udyam' => $item->udyam_no,
                'Mobile' => $item->mobile,
                'Email' => $item->email,
                'Entrepreneur Name' => $item->entrepreneur_name,
                'Enterprise Name' => $item->enterprise_name,
                //'Organisation Type' => $item->msme_classification,
                'Enterprise Type' => $item->msme_classification ?? '',
                //'Social Category' => $item->social_category ?? '',
                'Created At' => $item->created_at,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Team Id',
            'Udyam',
            'Mobile',
            'Email',
            'Entrepreneur Name',
            'Enterprise Name',
            //'Organisation Type',
            'Enterprise Type',
            //'Social Category',
            'Created At',
        ];
    }
}
