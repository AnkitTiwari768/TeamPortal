<?php

declare(strict_types=1);

namespace App\Domain\BPPIDUpdate;

use App\Traits\Respond;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

final class BPPIDUpdateController
{
    use Respond;

    public function __construct(
        private BPPIDUpdateListService $listService
    ) {
    }

    public function index()
    {
        $title = "BPP ID Mapping";
        $snps = DB::table('team_snp_scheme')
                ->select('snp_id', 'snp_name')
                ->whereNotNull('snp_id')
                ->where('snp_id', '!=', '')
                ->distinct()
                ->orderBy('snp_name', 'ASC')
                ->get();

        $summary = $this->listService->getSummaryCards();

        return view('BPPIDUpdate.index', compact('title', 'snps', 'summary'));
    }

    public function dataList()
    {
        return $this->success($this->listService->getList());
    }

    public function summaryCards(Request $request)
    {
        return $this->success($this->listService->getSummaryCards($request->query('report_key')));
    }

    public function exportExcel()
    {
        return Excel::download(
            new BppIdListExport($this->listService),
            'BPPID_Update_List_' . date('Y_m_d_His') . '.xlsx'
        );
    }

    public function exportPdf()
    {
        // dompdf's table renderer is memory-hungry on large tables (verified:
        // 2,000 rows alone exhausts 1GB), so rows are capped here the same
        // way msme_all_list caps its PDF export. Excel (exportExcel above)
        // has no such limit and always contains the complete dataset.
        ini_set('memory_limit', '1024M');
        set_time_limit(300);

        $maxRows = 1000;
        $totalRows = $this->listService->getExportRowCount();

        return Pdf::loadView('BPPIDUpdate.pdf.list', [
            'rows' => $this->listService->getExportRows($maxRows),
            'total_rows' => $totalRows,
            'max_rows' => $maxRows,
            'generated_at' => now()->format('d-m-Y H:i:s'),
        ])
            ->setPaper('A4', 'landscape')
            ->download('BPPID_Update_List_' . date('Y_m_d_His') . '.pdf');
    }

    public function updateBulkBppId(
        Request $request,
        UpdateBulkBppIdAction $action
    ) {

        $request->validate([
            'file' => 'required|mimes:xls,xlsx,xlsm',
            'snp_id' => 'required|string',
        ]);

        [$snpTeamId, $records, $invalidRows] = BulkUpdateBppIdData::getRecords($request->file('file'), $request->input('snp_id'));
        $result = $action->bulkUpdateMsmeBpp($snpTeamId, $records);

        $mapped = $result['mapped'] ?? [];
        $unmapped = array_merge($result['unmapped'] ?? [], $invalidRows);

        $reportKey = BppIdUploadReport::store($mapped, $unmapped);
        $report = ['mapped' => $mapped, 'unmapped' => $unmapped];

        return response()->json([
            'status'  => $result['success'] ?? false,
            'message' => $result['message']
                ?? 'Bulk BPP ID update successful',
            'data'    => new BppIdReportResource(array_merge($result, [
                'report_key'     => $reportKey,
                'mapped_count'   => count($mapped),
                'unmapped_count' => count($unmapped),
                'summary'        => BppIdUploadReport::summary($report),
                'rows'           => BppIdUploadReport::rows($report),
            ])),
        ]);
    }

    public function downloadReport(Request $request, string $reportKey, string $type, string $format)
    {
        abort_unless(in_array($type, ['mapped', 'unmapped', 'all'], true), 404);
        abort_unless(in_array($format, ['xlsx', 'pdf'], true), 404);

        $report = BppIdUploadReport::find($reportKey);

        abort_if(!$report, 404, 'Report not found or has expired.');

        $filename = 'bppid_update_' . $type . '_' . date('d-m-Y_His');

        if ($type === 'all') {
            return $this->downloadAllReport($report, $format, $filename);
        }

        $rows = $report[$type] ?? [];

        if ($format === 'xlsx') {
            $export = $type === 'mapped'
                ? new MappedBppIdExport($rows)
                : new UnmappedBppIdExport($rows);

            return Excel::download($export, "{$filename}.xlsx");
        }

        $view = $type === 'mapped'
            ? 'BPPIDUpdate.pdf.mapped'
            : 'BPPIDUpdate.pdf.unmapped';

        return Pdf::loadView($view, ['rows' => $rows])->download("{$filename}.pdf");
    }

    private function downloadAllReport(array $report, string $format, string $filename)
    {
        $rows = BppIdUploadReport::rows($report);

        if ($format === 'xlsx') {
            return Excel::download(new AllBppIdExport($rows), "{$filename}.xlsx");
        }

        // Same dompdf memory ceiling as exportPdf() above - cap and point to Excel.
        ini_set('memory_limit', '1024M');
        set_time_limit(300);

        $maxRows = 1000;
        $totalRows = count($rows);

        return Pdf::loadView('BPPIDUpdate.pdf.all', [
            'rows' => array_slice($rows, 0, $maxRows),
            'total_rows' => $totalRows,
            'max_rows' => $maxRows,
            'generated_at' => now()->format('d-m-Y H:i:s'),
        ])
            ->setPaper('A4', 'landscape')
            ->download("{$filename}.pdf");
    }
}
