<?php

declare(strict_types=1);

namespace App\Domain\ComponentUtilizationMapping;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ComponentUtilizationMappingService
{
    public function storeMapping(array $data)
    {
        return DB::transaction(function () use ($data) {
            // Find or create the master record for this FY/Duration/SubDuration
            $mapping = ComponentUtilizationMapping::firstOrNew(
                [
                    'financial_year' => $data['financial_year'],
                    'duration' => $data['duration'] ?? null,
                    'sub_duration' => $data['sub_duration'] ?? null,
                ]
            );

            if (!$mapping->exists) {
                $mapping->id = (string) Str::uuid();
                $mapping->status = 'Active';
                $mapping->created_by = auth()->id();
            }
            $mapping->updated_by = auth()->id();
            $mapping->save();

            // Create the detail record
            $detail = new ComponentUtilizationMappingDetail([
                'id' => (string) Str::uuid(),
                'source_major_component' => $data['source_major_component'],
                'source_sub_component' => $data['source_sub_component'],
                'allocated_amount' => $data['allocated_amount'],
                'released_amount' => $data['released_amount'],
                'remaining_balance' => $data['remaining_balance'],
                'amount_to_be_allocated' => $data['amount_to_be_allocated'],
                'target_category' => $data['target_category'],
                'target_sub_component' => $data['target_sub_component'],
                'remarks' => $data['remarks'] ?? null,
                'status' => 'Active',
                'created_by' => auth()->id(),
                'updated_by' => auth()->id(),
            ]);

            $mapping->details()->save($detail);

            return $detail;
        });
    }

    public function getMappingHistory()
    {
        return ComponentUtilizationMappingDetail::with('mapping')
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
