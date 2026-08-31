<?php 

declare(strict_types=1);

namespace App\Http\Api\V1\AuditTrail;

use App\Http\Controllers\ApiController;
use Illuminate\Http\Request;

class AuditTrailController extends ApiController 
{
    public function __construct(private AuditTrailService $service) 
    {
        $this->service = $service;
    }

    public function index()
    {
        try 
        {
            $result = $this->service->getLogs();
            return $this->success($result);
        }
        catch (\Throwable $e) 
        {
            return $this->handleException($e);
        }
    }

}