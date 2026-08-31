<?php

declare(strict_types=1);

namespace App\Domain\MIS;

use Illuminate\Http\Request;

final class SNPWiseMSMEMISReportController
{
    public function index()
    {
        $title = 'SNP Wise MSME MIS Report';
        return view('mis.snp-wise-msme-mis-report', compact('title'));
    }

    public function getSNPWiseMSMEMISReport(Request $request,SNPWiseMSMEMISReportAction $action)
    {
        try {
            $data = $action->execute($request->from_date,$request->to_date);

            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}