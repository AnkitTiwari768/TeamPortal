<?php

namespace App\Domain\NetworkProvider;

use App\Domain\Audit\Auditable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class NetworkProvider extends Model
{
    use HasUuids, Auditable;

    protected $table = 'network_providers';

    protected $fillable = [
        'id',
        'user_id',
        'roles',
        'np_team_id',
        'role_names',
        'organization_id',
        'organization_name',
        'email',
        'bppid_providerid',
        'contact_details',
        'authorized_person_details',
        'configuration_details',
        'bank_details',
        'role_selection_details',
        'value_proposition_details',
        'commercial_model_details',
        'is_snp',
        'is_new_registration',
        'status',
        'status_updated_at',
        'review_remarks',
        'created_by',
        'updated_by',
    ];

    // protected $casts = [
    //     'roles' => 'array',
    //     'contact_details' => 'array',
    //     'authorized_person_details' => 'array',
    //     'configuration_details' => 'array',
    //     'bank_details' => 'array',
    //     'role_selection_details' => 'array',
    //     'value_proposition_details' => 'array',
    //     'commercial_model_details' => 'array',
    // ];
}
