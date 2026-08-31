<?php

declare(strict_types=1);

namespace App\Domain\MIS;

use Illuminate\Http\Request;

final class StateWiseMISReportController
{
    public function index()
    {
        $title = 'State Wise MIS Report';
        return view('mis.state-wise-mis-report', compact('title'));
    }

    public function getStateWiseMISReport(StateWiseMISReportAction $action)
    {
        try {           
            $data = $action->execute();
            return response()->json([
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}