<?php

namespace App\Web\RootManager\Common;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SnpMsmeExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths
{
    protected $data;

    public function __construct($data)
    {
        $this->data = collect($data);
    }

    public function collection()
    {
        return $this->data;
    }

    public function headings(): array
    {
        return [
            'SN',
            'SNP ID',
            'SNP Organisation Name',
            'Open MSME',
            'Direct Selection by MSE',
            'Onboarded MSE'
        ];
    }

    public function map($row): array
    {
        return [
            $row['SN'],
            $row['SNP ID'],
            $row['SNP Organisation Name'],
            $row['Open MSME'],
            $row['Direct Selection by MSE'],
            $row['Onboarded MSE'],
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
            'A' => 8,
            'B' => 15,
            'C' => 35,
            'D' => 15,
            'E' => 20,
            'F' => 15,
        ];
    }
}