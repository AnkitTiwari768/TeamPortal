<?php

namespace App\Web\Msme;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MsmeSnpExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        $rows = [];
        
        foreach ($this->data as $row) {
            if (empty($row['snps'])) {
                $rows[] = (object)[
                    'msme_id' => $row['msme_id'] ?? 'N/A',
                    'mobile' => $row['mobile'] ?? 'N/A',
                    'udyam_no' => $row['udyam_no'] ?? 'N/A',
                    'enterprise_name' => $row['enterprise_name'] ?? 'N/A',
                    'snp_id' => 'No SNP Found',
                    'snp_name' => '—',
                    'organization_name' => '—',
                ];
            } else {
                foreach ($row['snps'] as $snp) {
                    $rows[] = (object)[
                        'msme_id' => $row['msme_id'] ?? 'N/A',
                        'mobile' => $row['mobile'] ?? 'N/A',
                        'udyam_no' => $row['udyam_no'] ?? 'N/A',
                        'enterprise_name' => $row['enterprise_name'] ?? 'N/A',
                        'snp_id' => $snp['snp_id'] ?? 'N/A',
                        'snp_name' => $snp['snp_name'] ?? 'N/A',
                        'organization_name' => $snp['organization_name'] ?? 'N/A',
                    ];
                }
            }
        }
        
        return collect($rows);
    }

    public function headings(): array
    {
        return [
            'MSME ID',
            'Mobile',
            'Udyam No',
            'Enterprise Name',
            'SNP ID',
            'SNP Name',
            'Organization Name',
        ];
    }

    public function map($row): array
    {
        return [
            $row->msme_id,
            $row->mobile,
            $row->udyam_no,
            $row->enterprise_name,
            $row->snp_id,
            $row->snp_name,
            $row->organization_name,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '667eea']
                ]
            ],
        ];
    }
    
    public function columnWidths(): array
    {
        return [
            'A' => 12,
            'B' => 15,
            'C' => 20,
            'D' => 30,
            'E' => 12,
            'F' => 25,
            'G' => 25,
        ];
    }
}