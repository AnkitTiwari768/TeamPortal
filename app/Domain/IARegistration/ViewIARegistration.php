<?php

declare(strict_types=1);

namespace App\Domain\IARegistration;

class ViewIARegistration
{
    public function execute(string $registrationId): ListIAResource
    {
        $industrialAssociation = IndustrialAssociation::query()
            ->from('industrial_associations as ia')
            ->join('attribute_values as av', 'ia.entity_type', '=', 'av.id')
            ->leftJoin('file_uploads as fu', 'ia.authorization_document_id', '=', 'fu.id')
            ->leftJoin('states as st', 'st.id', '=', 'ia.state_id')
            ->leftJoin('locations as dt', 'dt.id', '=', 'ia.district_id')
            ->select([
                'ia.*',
                'av.attribute_value as entity_type',
                'st.name as state_name',
                'dt.name as district_name',
                'fu.file_path as authorization_document_path'
            ])
            ->where('ia.id', $registrationId)
            ->firstOrFail();

        return new ListIAResource($industrialAssociation);
    }
}
