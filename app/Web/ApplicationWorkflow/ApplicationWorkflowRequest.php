<?php

declare(strict_types=1);

namespace App\Web\ApplicationWorkflow;

use Illuminate\Foundation\Http\FormRequest;

class ApplicationWorkflowRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'application_id' => 'required|string|exists:claims,id',
            'action' => 'required|integer',
			'revert_to' => 'required_if:action,5',
            'comments' => 'nullable|string|max:500'
        ];
    }

    public function messages(): array
    {
        return [
            'application_id.required' => __('validation.application_id.required'),
            'application_id.string'   => __('validation.application_id.string'),
            'application_id.exists'   => __('validation.application_id.exists'),

            'action.required'         => __('validation.action.required'),
            'action.integer'          => __('validation.action.integer'),

            'comments.required'       => __('validation.comments.required'),
            'comments.string'         => __('validation.comments.string'),
            'comments.max'            => __('validation.comments.max'),
        ];
    }



    public function toDto(): ApplicationWorkflowDto
    {
        return ApplicationWorkflowDto::fromArray($this->validated());
    }
}
