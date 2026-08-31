<?php 

declare(strict_types=1);

namespace App\Traits\HasApplicant;

use DB;

trait HasApplicant 
{
    public function getApplicantId(string $userId) : string 
    {
        $applicant = DB::table('applicants')
                        ->where('user_id', $userId)
                        ->first();
        
        if (! $applicant) throw new \Exception("Applicant Id not found!");
        
        return $applicant->id;
    }

    public function getApplicantInfo(string $userId)
    {
        return DB::table('applicants')
            ->select(
                'production_name', 
                'company_representative_name', 
                'country_id', 
                'state_id', 
                'city_id', 
                'address_first', 
                'address_second', 
                'postal_code'
            )
            ->where('user_id', $userId)
            ->first();
    }
}