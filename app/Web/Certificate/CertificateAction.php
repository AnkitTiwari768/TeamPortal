<?php

namespace App\Web\Certificate;

use Barryvdh\DomPDF\Facade\Pdf;

class CertificateAction
{
    public function generate($networkProvider, string $role)
    {
        $data = [
            'organization_name' => $networkProvider->organization_name,
            'role_name'         => $role,
            'current_date'      => now()->format('d-M-Y'),
            'logo1'             => $this->getBase64Image(base_path('assets/img/snp_certificate_logo_1.png')),
            'logo2'             => $this->getBase64Image(base_path('assets/img/snp_certificate_logo_2.png')),
        ];

        $filename = "SNP_{$networkProvider->np_team_id}.pdf";

        return Pdf::loadView('pdf.snp_empanelment_letter_download', $data)
            ->download($filename)
            ->withHeaders([
                'X-Filename' => $filename,
            ]);
    }

    private function getBase64Image(string $path): string
    {
        return base64_encode(file_get_contents($path));
    }
}