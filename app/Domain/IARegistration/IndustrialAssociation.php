<?php

declare(strict_types=1);

namespace App\Domain\IARegistration;

use App\Domain\Audit\Auditable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class IndustrialAssociation extends Model
{
    use HasUuids, Auditable;

    protected $table  = 'industrial_associations';

    protected $fillable = [
        'id',
        'user_id',
        'organization_name',
        'entity_type',
        'entity_email',
        'number_of_members',
        'registration_number',
        'website',
        'contact_number',
        'state_id',
        'district_id',
        'pan_number',
        'complete_address',
        'contact_person_name',
        'contact_person_designation',
        'contact_person_phone',
        'contact_person_email',
        'authorization_document',
        'authorization_document_id',
        'status',
    ];

    protected $casts = [
        'number_of_members' => 'integer',
        'status' => 'integer',
    ];
}
