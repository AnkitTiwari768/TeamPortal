<?php

declare(strict_types=1);

namespace App\Domain\ComponentUtilizationMapping;

use Illuminate\Foundation\Http\FormRequest;

class ComponentUtilizationMappingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'financial_year' => ['required', 'string'],
            'duration' => ['nullable', 'string'],
            'sub_duration' => ['nullable', 'string'],
            'source_major_component' => ['required', 'string'],
            'source_sub_component' => ['required', 'string'],
            'allocated_amount' => ['required', 'numeric'],
            'released_amount' => ['required', 'numeric'],
            'remaining_balance' => ['required', 'numeric'],
            'amount_to_be_allocated' => ['required', 'numeric', 'min:0', 'lte:remaining_balance'],
            'target_category' => ['required', 'string'],
            'target_sub_component' => ['required', 'string'],
            'remarks' => ['nullable', 'string'],
        ];
    }
    
    public function messages(): array
    {
        return [
            'amount_to_be_allocated.lte' => 'The amount to be allocated cannot exceed the remaining balance.',
        ];
    }
}
