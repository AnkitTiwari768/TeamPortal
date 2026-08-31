<?php

declare(strict_types=1);

namespace App\Domain\NetworkProvider;

use App\Domain\Audit\Audit;
use App\Traits\HasFileUpload;
use App\Domain\NetworkProvider\NetworkProvider;
use Illuminate\Support\Facades\DB;

class NetworkProviderService
{
    use HasFileUpload;

    public function getNetworkProviderById(string $id)
    {
        $row = NetworkProvider::findOrFail($id);

        return $row ? $this->processNetworkProvider($row) : null;
    }

    public function getNetworkProviderByUserId(string $userId)
    {
        $row = NetworkProvider::where('user_id', $userId)->first();

        return $row ? $this->processNetworkProvider($row) : null;
    }

    public function getSnpDetailsByUserId(string $userId)
    {
        $row = DB::table('team_snp_scheme')
            ->where('user_id', $userId)
            ->first();

        if (!$row) {
            return null;
        }

        $userDetails = DB::table('users')
            ->where('id', $userId)
            ->first();

        $roleSelections = [];

        if (!empty($row->domain)) {
            $domains = json_decode($row->domain, true);
            $subDomains = json_decode($row->sub_domain, true);
            $transactionTypes = json_decode($row->transaction_type, true);
            $states = json_decode($row->state_id, true);

            $snpRoleId = DB::table('roles')
                ->where('slug', 'snp')
                ->value('id');

            foreach ($domains as $index => $domain) {
                $roleSelections[] = [
                    'role' => $snpRoleId,
                    'domain' => $domain,
                    'transaction_type' => $transactionTypes[$index] ?? null,
                    'serviceability' => $states[$index] ?? null,
                    'status' => '76a4bd27-d49f-11f0-922a-00155d022d06',
                    'ondc_domain_mapping' => $subDomains[$index] ?? null,
                ];
            }
        }

        $row->role_selection_details = !empty($roleSelections)
            ? json_encode($roleSelections)
            : [];

        $row->email = $userDetails->email ?? '';
        $row->mobile = $userDetails->mobile ?? '';

        return $row;
    }

    public function getLanguages()
    {
        return DB::table('language')->get();
    }



    private function processNetworkProvider(NetworkProvider $row)
    {
        // Decode all JSON fields
        $jsonFields = [
            'roles',
            'contact_details',
            'authorized_person_details',
            'configuration_details',
            'bank_details',
            'role_selection_details',
            'value_proposition_details',
            'commercial_model_details'
        ];

        foreach ($jsonFields as $field) {
            if (!empty($row->{$field})) {
                $row->{$field} = json_decode($row->{$field}, true);
            }
        }

        $document = $this->getFileDetails(data_get($row->authorized_person_details, '0.certificate_id'));

        if ($document) {
            $row->authorized_person_certificate = $document['document_link'];
            $row->authorized_person_certificate_name = $document['document_name'];
        }

        $flyerDocument = $this->getFileDetails(data_get($row->value_proposition_details, 'flyer_id'));

        if ($flyerDocument) {
            $row->flyer_document = $document['document_link'];
            $row->flyer_document_name = $document['document_name'];
        }

        return $row;
    }

    public function getFileDetails(string $fileId)
    {
        $file = DB::table('file_uploads')->where('id', $fileId)->first();

        if ($file) {
            return [
                'document_link' => url('storage/app/' . $file->file_path . '/' . $file->file_system_name),
                'document_name' => $file->file_name
            ];
        }

        return [];
    }
}
