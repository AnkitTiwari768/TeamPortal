<?php

declare(strict_types=1);

namespace App\Web\MsmeAllList;

use Illuminate\View\View;
use App\Http\Controllers\ClientController;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class SnpCategoryListController extends ClientController
{
    public function __construct(
        private SnpCategoryListService $service
    ) {
    }

    public function index(): View
    {
        return view('msme_all_list.snpCategorylist.index')
            ->with('title', 'SNP Category List')
            ->with('lists', (object) $this->service->getDropdownList());
    }

    public function getSnpCategoryList()
    {
        return $this->success($this->service->getSnpCategoryList());
    }

    public function downloadExcel()
    {
        return Excel::download(
            new SnpCategoryListExport($this->service),
            'SNP_Category_List_' . date('Y_m_d_His') . '.xlsx'
        );
    }

    public function downloadPdf()
    {
        // dompdf's table renderer is memory-hungry on large tables; cap the rows
        // and give the request extra headroom rather than exhausting the limit.
        ini_set('memory_limit', '1024M');

        $maxRows = 500;
        $totalRows = $this->service->getExportRowCount();

        return Pdf::loadView('msme_all_list.snpCategorylist.pdf_export', [
            'rows' => $this->service->getExportRows($maxRows),
            'total_rows' => $totalRows,
            'max_rows' => $maxRows,
            'generated_at' => now()->format('d-m-Y H:i:s'),
        ])
            ->setPaper('A4', 'landscape')
            ->download('SNP_Category_List_' . date('Y_m_d_His') . '.pdf');
    }
}
