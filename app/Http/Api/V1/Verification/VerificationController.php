<?php 
declare(strict_types=1);
namespace App\Http\Api\V1\Verification;
use App\Http\Controllers\ApiController;
use App\Http\Api\V1\User\VerificationValidation as Validation;
use Illuminate\Http\Request;
use App\Http\Services\VerificationService;


class VerificationController extends ApiController 
{
    public function __construct(private VerificationService $service) 
    {
        $this->service = $service;
    }

}