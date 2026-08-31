<?php

declare(strict_types=1);

namespace App\Web\BonusQuery;

use App\Traits\HasResponses;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class ApplicationQueryLogController
{
    use HasResponses;

    public function store(StoreQueryLogRequest $request, StoreQueryLogAction $action): JsonResponse
    {
        $action->execute($request->toDto());

        return $this->success(message: __('application.query_sent_success'));
    }
}
