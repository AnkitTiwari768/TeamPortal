<?php

declare(strict_types=1);

namespace App\Domain\BatchTimelineHistory;

use App\Traits\Respond;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

final class BatchTimelineHistoryController
{
    use Respond;

    public function index(BatchTimelineHistoryListQuery $query): View
    {
        return view('batch_timeline_history.index')
            ->with('title', 'Batch Timeline History')
            ->with('statusOptions', $query->getStatusOptions());
    }

    public function getList(BatchTimelineHistoryListQuery $query)
    {
        return $this->success(data: $query->execute());
    }

    public function downloadExcel(BatchTimelineHistoryListQuery $query)
    {
        return Excel::download(
            new BatchTimelineHistoryExport($query),
            'Batch_Timeline_History_' . date('Y_m_d_His') . '.xlsx'
        );
    }

    public function downloadPdf(BatchTimelineHistoryListQuery $query)
    {
        // dompdf's table renderer is memory-hungry on large tables; cap the rows
        // and give the request extra headroom rather than exhausting the limit.
        ini_set('memory_limit', '1024M');

        $maxRows = 500;
        $totalRows = $query->getExportRowCount();

        return Pdf::loadView('batch_timeline_history.pdf_export', [
            'rows' => $query->getExportRows($maxRows),
            'total_rows' => $totalRows,
            'max_rows' => $maxRows,
            'generated_at' => now()->format('d-m-Y H:i:s'),
        ])
            ->setPaper('A4', 'landscape')
            ->download('Batch_Timeline_History_' . date('Y_m_d_His') . '.pdf');
    }
}
