<?php

declare(strict_types=1);

namespace App\Domain\Msme;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;


class MsmeJourneyAction
{
	
    public function execute(string $userId)
    {
        $result = DB::table('team_msme_schemes as a')
            ->leftJoin('team_snpmsme_mapping as b', 'a.id', '=', 'b.msme_id')
            ->leftJoin('team_snp_scheme as c', 'b.snp_id', '=', 'c.id')
            ->select(
                'a.entrepreneur_name',
                'a.team_id',
                'a.created_at as registered_at',
				'b.updated_at as onboard_at',
                'c.snp_name',
				'c.organization_name'
				
                /*DB::raw('EXISTS(
                    select 1 
                    from claims 
                    where claims.msme_udyam_number = a.udyam_no
                ) as claims_exists')*/
            )
            ->where('a.user_id', $userId)
			//->where('a.select_snp', 1)
			//->where('a.status', 1)
            ->first();
			
		

        if (!$result) {
            return [];
        }
		

        return [
            'entrepreneur_name' => $result->entrepreneur_name,
            'team_id'           => $result->team_id,
            'lead_status'       => !empty($result->onboard_at)
				? "Mapped with {$result->organization_name} for onboarding on ONDC"
				: (
					!empty($result->snp_name)
						? "Pending at {$result->snp_name} for onboarding"
						: "Open to SNP(s)"
				  ),
			'registered_at'         => !empty($result->registered_at)?Carbon::parse($result->registered_at)->format('d M Y'):null,
            'onboard_at'        => !empty($result->onboard_at)?Carbon::parse($result->onboard_at)->format('d M Y'):null,
            //'claims_exists'     => (bool) $result->claims_exists,
        ];
    }
}
