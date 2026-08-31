<?php

declare(strict_types=1);

namespace App\Domain\FundFlow;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;

class ComponentUtilizationMappingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'financial_year' => [
                'bail',
                'required',
                'string',
                'regex:/^[0-9]{4}-[0-9]{4}$/',
            ],
            'duration_id' => [
                'bail',
                'nullable',
                'string',
                'exists:attribute_values,id',
            ],
            'sub_duration_id' => [
                'bail',
                'nullable',
                'string',
                'exists:attribute_values,id',
            ],
            'target_major_component_id' => [
                'bail',
                'required',
                'string',
                'exists:attribute_values,id',
            ],
            'target_sub_component_id' => [
                'bail',
                'required',
                'string',
                'exists:attribute_values,id',
            ],
            'remarks' => [
                'bail',
                'nullable',
                'string',
                'max:1000',
            ],
            'total_max_utilization_amount' => [
                'bail',
                'nullable',
                'numeric',
                'min:0',
            ],
            'eligible_lines' => ['required', 'array', 'min:1'],
            'eligible_lines.*.eligible_major_component_id' => ['bail', 'required', 'string', 'exists:attribute_values,id'],
            'eligible_lines.*.eligible_sub_component_id' => ['bail', 'required', 'string', 'exists:attribute_values,id'],
            'eligible_lines.*.total_allocated_amount' => ['bail', 'nullable', 'numeric', 'min:0'],
            'eligible_lines.*.total_distributed_amount' => ['bail', 'nullable', 'numeric', 'min:0'],
            'eligible_lines.*.max_utilization_amount' => [
                'bail',
                'required',
                'numeric',
                'min:0',
                'regex:/^\d{1,16}(\.\d{1,2})?$/',
            ],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $fy = $this->input('financial_year');
            $targetMajor = $this->input('target_major_component_id');
            $targetSub = $this->input('target_sub_component_id');
            $lines = $this->input('eligible_lines', []);

            $seenKeys = [];

            foreach ($lines as $index => $line) {
                $eligibleMajor = $line['eligible_major_component_id'] ?? null;
                $eligibleSub = $line['eligible_sub_component_id'] ?? null;
                $maxAmount = $line['max_utilization_amount'] ?? null;

                // Rule 1: Negative amount check
                if ($maxAmount !== null && (float)$maxAmount < 0) {
                    $validator->errors()->add(
                        "eligible_lines.{$index}.max_utilization_amount",
                        __('fund_flow.validation_utilization_mapping.negative_amount_not_allowed')
                    );
                }

                if ($eligibleMajor) {
                    // Rule 2: Cannot select same target & eligible component
                    if ($targetMajor === $eligibleMajor && ($targetSub === $eligibleSub || (empty($targetSub) && empty($eligibleSub)))) {
                        $validator->errors()->add(
                            "eligible_lines.{$index}.eligible_major_component_id",
                            __('fund_flow.validation_utilization_mapping.same_target_eligible_component')
                        );
                    }

                    // Rule 3: Duplicate eligible component rows check
                    $key = $eligibleMajor . '|' . ($eligibleSub ?? '');
                    if (isset($seenKeys[$key])) {
                        $validator->errors()->add(
                            "eligible_lines.{$index}.eligible_major_component_id",
                            __('fund_flow.validation_utilization_mapping.duplicate_component_row')
                        );
                    }
                    $seenKeys[$key] = true;

                    // Rule 4: Carry forward / mapping allowed only for components whose allocation is created
                    if ($fy) {
                        $allocatedSum = DB::table('fund_pools')
                            ->where('financial_year', $fy)
                            ->where('major_component_id', $eligibleMajor)
                            ->when($eligibleSub, function ($q) use ($eligibleSub) {
                                $q->where('sub_component_id', $eligibleSub);
                            })
                            ->sum('total_allocated_amount');

                        if ($allocatedSum <= 0) {
                            // Also check fund_allocation_component_mappings directly as fallback
                            $hasAllocation = DB::table('fund_allocation_component_mappings as acm')
                                ->join('fund_allocations as fa', 'fa.id', '=', 'acm.fund_allocation_id')
                                ->where('fa.financial_year', $fy)
                                ->where('acm.major_component_id', $eligibleMajor)
                                ->when($eligibleSub, function ($q) use ($eligibleSub) {
                                    $q->where('acm.sub_component_id', $eligibleSub);
                                })
                                ->exists();

                            if (!$hasAllocation) {
                                $validator->errors()->add(
                                    "eligible_lines.{$index}.eligible_major_component_id",
                                    __('fund_flow.validation_utilization_mapping.allocation_not_created')
                                );
                            }
                        }
                    }
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'financial_year.required' => __('fund_flow.validation_utilization_mapping.financial_year_required'),
            'financial_year.regex' => __('fund_flow.validation_utilization_mapping.financial_year_regex'),
            'target_major_component_id.required' => __('fund_flow.validation_utilization_mapping.target_major_component_required'),
            'target_major_component_id.exists' => __('fund_flow.validation_utilization_mapping.target_major_component_exists'),
            'target_sub_component_id.required' => __('fund_flow.validation_utilization_mapping.target_sub_component_required'),
            'eligible_lines.required' => __('fund_flow.validation_utilization_mapping.eligible_lines_required'),
            'eligible_lines.min' => __('fund_flow.validation_utilization_mapping.eligible_lines_required'),
            'eligible_lines.*.eligible_major_component_id.required' => __('fund_flow.validation_utilization_mapping.eligible_major_component_required'),
            'eligible_lines.*.eligible_sub_component_id.required' => __('fund_flow.validation_utilization_mapping.eligible_sub_component_required'),
            'eligible_lines.*.max_utilization_amount.required' => __('fund_flow.validation_utilization_mapping.max_amount_required'),
            'eligible_lines.*.max_utilization_amount.min' => __('fund_flow.validation_utilization_mapping.negative_amount_not_allowed'),
            'eligible_lines.*.max_utilization_amount.regex' => __('fund_flow.validation_utilization_mapping.max_amount_regex'),
        ];
    }
}
