<?php

declare(strict_types=1);

namespace App\Web\BonusQuery;

use Illuminate\Support\Facades\DB;

class ApplicationQueryService
{
    public function getQueryDetails(string $queryId): array
    {
        $queryData = DB::table('rts_service_queries as rsq')
            ->selectRaw('
                rsq.id,
                rsq.rts_service_id as application_id,
                rsq.subject,
                rsq.query_letter_doc,
                rsq.comments,
                rsq.status,
                rsq.raised_at,
                u.full_name as raised_by,
                rs.application_number,
                rsql.comments as query_log_comment,
                rsq.remark as remark
            ')
            ->join('rts_services AS rs', 'rsq.rts_service_id', '=', 'rs.id')
            ->join('rts_service_query_logs as rsql', 'rsql.rts_service_query_id', '=', 'rsq.id') // corrected
            ->join('users AS u', 'rsq.raised_by', '=', 'u.id')
            ->leftjoin('rts_service_query_letter_documents AS rsqld', 'rsqld.file_upload_id', '=', 'rsq.query_letter_doc')
            ->where('rsq.id', $queryId)
            ->first();

        if ($queryData && !empty($queryData->query_letter_doc)) {
            $docIds = explode(',', $queryData->query_letter_doc);
            $Querydocuments = DB::table('rts_service_query_letter_documents')
                ->whereIn('file_upload_id', $docIds)
                ->get()->toArray();
             
        }

        $queryRequiredDocuments = DB::table('rts_service_query_documents as rsqd')
            ->join('document_categories as dc', 'rsqd.document_category_id', '=', 'dc.id')
            ->selectRaw('dc.id, dc.name,rsqd.comments')
            ->where('rsqd.rts_service_query_id', $queryId)
            ->get();

        $queryLog = DB::table('rts_service_query_logs as rsql')
            ->where('rsql.rts_service_query_id', $queryId)
            ->select('rsql.documents')
            ->first();

        $documents = $queryLog?->documents ? json_decode($queryLog->documents) : [];

        $documentFileNames = [];

        foreach ($documents as $document) {
            $fileNames = DB::table('rts_service_documents as rsd')
                ->join('file_uploads as fu', 'rsd.file_upload_id', '=', 'fu.id')
                ->where('rsd.id', $document)
                ->select('fu.*', 'rsd.*')
                ->first();

            $documentFileNames[] = $fileNames;
        }

        return [
            $queryData,
            $Querydocuments ??null,
            $queryRequiredDocuments,
            $documentFileNames
        ];
    }
}
