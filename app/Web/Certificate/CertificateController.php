<?php

namespace App\Web\Certificate;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Web\Certificate\CertificateService;
use Illuminate\Support\Facades\Log;

class CertificateController extends Controller
{
    public function __construct(
        private CertificateService $certificateService
    ) {}

    public function download(Request $request)
    {
        try {
            $pdfResponse = $this->certificateService->generateCertificate();

            return $pdfResponse;

        } catch (\Throwable $e) {
            Log::error('Certificate download failed: ' . $e->getMessage());
            abort(500, 'Could not generate certificate.');
        }
    }
}