<?php

declare(strict_types=1);

namespace App\Domain\FundDistribution;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FundDistribution extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'fund_distributions';

    protected $fillable = [
        'id',
        'financial_year',
        'duration_id',
        'sub_duration_id',
        'major_component_id',
        'sub_component_id',
        'fund_pool_id',
        'distribution_amount',
        'tds_percentage',
        'tds_amount',
        'net_payable_amount',
        'sanction_order_number',
        'sanction_order_date',
        'remarks',
        'upload_document',
        'upload_document_original_name',
        'source_type',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'distribution_amount' => 'float',
        'tds_percentage'      => 'float',
        'tds_amount'          => 'float',
        'net_payable_amount'  => 'float',
        'sanction_order_date' => 'date:Y-m-d',
    ];
}
