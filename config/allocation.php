<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Fund Allocation Configuration
    |--------------------------------------------------------------------------
    |
    | pooling_mode:
    |   1 = Strict Pooling (By Financial Year + Duration + Sub-Duration + Components)
    |   2 = Merged Pooling (Consolidated across Durations)
    |
    */

    'pooling_mode' => (int) env('FUND_POOLING_MODE', 1),

    'merged_pool_duration_uuid' => env('MERGED_POOL_DURATION_UUID', '00000000-0000-0000-0000-000000000000'),

    'major_component_code' => 'major-components',
    'component_code'       => 'component',
    'sub_component_code'   => 'major-components',
    'duration_code'        => 'duration',

    'allocation_document_path' => 'allocation-documents',
];
