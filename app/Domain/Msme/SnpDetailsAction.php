<?php

declare(strict_types=1);

namespace App\Domain\Msme;

use Illuminate\Support\Facades\DB;

class SnpDetailsAction
{
    public function execute(string $msmeId)
    {
        $result = DB::table('team_snpmsme_mapping as a')
            ->join('team_msme_schemes as b', 'a.msme_id', '=', 'b.id')
            ->join('team_snp_scheme as c', 'a.snp_id', '=', 'c.id')
            ->leftJoin('users as u', 'u.id', '=', 'c.user_id')
            ->select([
                'c.*',
                'u.first_name as user_name',
                'u.email as user_email',
            ])
            ->where('b.user_id', $msmeId)
            ->first();

        if (!$result) {
            return [];
        }

        $result->domain = $result->domain ? $this->getDomains(json_decode($result->domain, true)) : [];
        $result->sub_domain = $result->sub_domain ? $this->getSubDomains(json_decode($result->sub_domain, true)) : [];
        $result->transaction_type = $result->transaction_type ? $this->getTransactionTypes(json_decode($result->transaction_type, true)) : [];
        $result->state_id = $result->state_id ? $this->getStates(json_decode($result->state_id, true)) : [];

        return $result;
    }

    public function getDomains(array $domains)
    {
        return DB::table('attribute_values')->whereIn('id', $domains)->pluck('attribute_value')->toArray();
    }

    public function getSubDomains(array $subdomains)
    {
        return DB::table('sub_domains')->whereIn('id', $subdomains)->pluck('name')->toArray();
    }

    public function getTransactionTypes(array $transactionTypes)
    {
        return DB::table('attribute_values')->whereIn('id', $transactionTypes)->pluck('attribute_value')->toArray();
    }

    public function getStates(array $states)
    {
        return DB::table('states')->whereIn('id', $states)->pluck('name')->toArray();
    }
}
