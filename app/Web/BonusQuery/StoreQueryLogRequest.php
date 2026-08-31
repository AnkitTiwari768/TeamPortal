<?php

declare(strict_types=1);

namespace App\Web\BonusQuery;

use Illuminate\Foundation\Http\FormRequest;

class StoreQueryLogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'query_id' => 'required|string|exists:rts_service_queries,id',
            'comments' => 'required|string|max:500',
            'documents' => 'required|array',
            'documents.*' => 'required|exists:rts_service_documents,id'
        ];
    }

    public function messages(): array
    {
        return [
            'query_id.required'        => __('validation.query_id.required'),
            'query_id.string'          => __('validation.query_id.string'),
            'query_id.exists'          => __('validation.query_id.exists'),

            'comments.required'        => __('validation.comments.required'),
            'comments.string'          => __('validation.comments.string'),
            'comments.max'             => __('validation.comments.max'),

            'documents.required'       => __('validation.documents.required'),
            'documents.array'          => __('validation.documents.array'),

            'documents.*.required'     => __('validation.documents.item_required'),
            'documents.*.exists'       => __('validation.documents.item_exists'),
        ];
    }


    public function toDto(): StoreQueryLogDto
    {
        return StoreQueryLogDto::fromArray($this->validated());
    }
}
