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
            'target_sub_component' => [
                'required',
                'string',
                function (string $attribute, mixed $value, \Closure $fail) {
                    $financialYear = $this->input('financial_year');
                    $duration = $this->input('duration');
                    $subDuration = $this->input('sub_duration');
                    $sourceMajorComponent = $this->input('source_major_component');
                    $sourceSubComponent = $this->input('source_sub_component');
                    $targetCategory = $this->input('target_category');
                    // 1. Prevent mapping to the exact same Source (Circular mapping)
                    if ($targetCategory === $sourceMajorComponent && $value === $sourceSubComponent) {
                        $fail('This Category and Component combination has already been allocated. Please select a different Component.');
                        return;
                    }

                    // 2. Prevent duplicate mappings from the same Source to the same Target
                    $query = \Illuminate\Support\Facades\DB::table('component_utilization_mapping_details as d')
                        ->join('component_utilization_mappings as m', \Illuminate\Support\Facades\DB::raw('m.id COLLATE utf8mb4_unicode_ci'), '=', \Illuminate\Support\Facades\DB::raw('d.mapping_id COLLATE utf8mb4_unicode_ci'))
                        ->where('m.financial_year', $financialYear)
                        ->where('d.source_major_component', $sourceMajorComponent)
                        ->where('d.target_category', $targetCategory)
                        ->where('d.target_sub_component', $value);

                    if ($duration) {
                        $query->where('m.duration', $duration);
                    } else {
                        $query->whereNull('m.duration');
                    }

                    if ($subDuration) {
                        $query->where('m.sub_duration', $subDuration);
                    } else {
                        $query->whereNull('m.sub_duration');
                    }

                    if ($sourceSubComponent) {
                        $query->where('d.source_sub_component', $sourceSubComponent);
                    } else {
                        $query->whereNull('d.source_sub_component');
                    }

                    if ($query->exists()) {
                        $fail('This Category and Component combination has already been allocated. Please select a different Component.');
                    }
                }
            ],
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
