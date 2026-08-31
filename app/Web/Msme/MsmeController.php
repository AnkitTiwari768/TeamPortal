<?php

declare(strict_types=1);

namespace App\Web\Msme;

use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\ClientController;

class MsmeController extends ClientController
{
    private static string $module =  'registered-msme';

    public function __construct(private MsmeService $service) {}

    public function index(): View
    {
        return view('msme.index')
            ->with('title', 'MSME Registartion')
            ->with('lists', (object) $this->service->getDropdownList());
    }

    public function getMsmeList()
    {
        return $this->success($this->service->getMsmeList());
    }

    public function getMsmeDetails(string $id): View
    {
        $title = __('MSME Details');
        $module_url = 'registered-msme';
        $detail = (array) $this->service->getMsmeDetails($id);
        return view('msme.details', compact('detail', 'title', 'module_url'));
    }

    public function relevantSnp()
    {
        $module_url = 'relevant-snp';
        $title = __('Relevant NP');
        return view('msme.relevant-snp', compact('title', 'module_url'));
    }

    public function getRelevantSnpList()
    {
        return $this->success($this->service->getSnpList());
    }

    public function relevantSnpView(string $id)
    {
        $module_url = 'relevant-snp';
        $title = __('View SNP');
        $data =  $this->service->relevantSnpViewDetails($id);
        return view('msme.relevant-snp-view', compact('title', 'module_url', 'data'));
    }
}
