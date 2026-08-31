<?php

declare(strict_types=1);

namespace App\Web\BonusQuery;

use App\Traits\HasResponses;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ApplicationQueryController
{
    use HasResponses;

    public function index(string $id, ApplicationQueryListAction $action): JsonResponse
    {
        return $this->success(message: __('application.retrieved_success'), data: $action->execute($id));
    }

    public function show(string $queryId, ApplicationQueryService $service): View
    {
        $title = 'Query Details';

        [$queryData,$Querydocuments, $queryRequiredDocuments, $uploadedDocuments] = $service->getQueryDetails($queryId);
        
        return view('service-applications.edit-query', compact('queryData', 'Querydocuments','queryRequiredDocuments', 'uploadedDocuments', 'title'));
    }

    public function store(StoreApplicationQueryRequest $request, StoreApplicationQueryAction $action): JsonResponse
    {
        //dd($request->all());
        $action->execute($request->toDto());
        //$action->execute($request->toDto());

        return $this->success(message: __('application.raised_success'));
    }

    public function update(Request $request, UpdateApplicationQueryAction $action)
    {
        $validated = $request->validate(
            [
                'query_id' => 'nullable|exists:rts_service_queries,id',
                'application_id' => 'nullable|exists:rts_services,id',
                'action'   => 'required',
                'remark'   => 'required',
            ],
            [
                'query_id.required' => __('validation.query_id.required'),
                'query_id.exists'   => __('validation.query_id.exists'),
                'action.required'   => __('validation.action.required'),
                'remark.required'   => __('validation.remark.required'),
            ]
        );


        $action->execute($validated);

        $message = match ((int) $validated['action']) {
            QueryStatus::Open->value => __('application.raised_success'),
            QueryStatus::Pending->value => __('application.reply_success'),
            QueryStatus::Closed->value => __('application.close_success'),
            QueryStatus::Accept->value => __('application.accept_success'),
            QueryStatus::Reject->value => __('application.reject_success'),
        };

        return $this->success(message: $message);
    }

    public function getQueryDetailById(string $queryId, ApplicationQueryService $service) : JsonResponse
    {
        [$queryData, $queryRequiredDocuments, $uploadedDocuments] = $service->getQueryDetails($queryId);

        return response()->json([
            'status' => true,
            'message' => 'Query details fetched successfully.',
            'data' => [
                'queryData' => $queryData,
                'queryRequiredDocuments' => $queryRequiredDocuments,
                'uploadedDocuments' => $uploadedDocuments,
            ],
        ]);
    }

    public function listApplicantQuery(string $id){
         $title = "View Queries";

    return view('bonus_query.index', compact('id', 'title'));
    }

}
