<?php 

declare(strict_types=1);

namespace App\Web\AuditTrail;

use App\Http\Controllers\ClientController;
use App\Http\Api\V1\AuditTrail\AuditTrailService;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class AuditTrailController extends ClientController
{
    private static $module = 'audit-trail.index';
    
    public function __construct(private AuditTrailService $service)
    {
        $this->service = $service;
    }

    public function index(): View
    {
        return view('audit-trail.index')
            ->with('title', __('Audit Trail'));
    }

    public function getLogs()
    {
        //guard(config('permissions.currency-view'));
        
        return $this->success($this->service->getLogs());
    }

    public function create(): View
    {

    }

    public function store(Request $request): mixed 
    {   
       
    }

    public function edit(string $id): View
    {
       
    }

    public function update(Request $request, string $id): mixed
    {
   
        
    }
}