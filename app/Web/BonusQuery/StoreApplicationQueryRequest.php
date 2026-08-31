<?php

declare(strict_types=1);

namespace App\Web\BonusQuery;

use Illuminate\Foundation\Http\FormRequest;

class StoreApplicationQueryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
       
        
        return [
            'msme_id' => 'required|string',
            'remarks' => 'nullable|string',
            'claim_type_id' => 'nullable|string',

            'file_upload_ids' => 'nullable|array',
            'file_upload_ids.*' => 'required|string',

        ];
    }

    public function messages(): array
    {
        return [
            'msme_id.required' => __('validation.msme_id.required'),
            'msme_id.uuid'     => __('validation.msme_id.uuid'),

            'remarks.string'   => __('validation.remarks.string'),
            'remarks.max'      => __('validation.remarks.max'),

            'file_upload_ids.array' => __('validation.file_upload_ids.array'),
            'file_upload_ids.*.string' => __('validation.file_upload_ids.item_string'),
        ];
    }

    public function toDto(): StoreApplicationQueryDto
    {
        return StoreApplicationQueryDto::fromArray($this->validated());
    }
}
