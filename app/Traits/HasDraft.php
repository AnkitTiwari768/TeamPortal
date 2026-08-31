<?php

namespace App\Traits;

trait HasDraft 
{

    public function stepOne(string $id)
    {
        [$enable_pd, $enable_sd, $enable_ed, $enable_lrd] 
            = $this->getNationalApplicationDrafts($id);

        return [
            'enable_pd' => $enable_pd,
            'enable_sd' => $enable_sd,
            'enable_ed' => $enable_ed,
            'enable_lrd' => $enable_lrd,
        ];
    }

    public function stepTwo(string $id)
    {
       [$enable_pd, $enable_sd, $enable_ed, $enable_lrd] 
            = $this->getNationalApplicationDrafts($id);

        return [
            'enable_pd' => $enable_pd,
            'enable_sd' => $enable_sd,
            'enable_ed' => $enable_ed,
            'enable_lrd' => $enable_lrd,
        ];
    
    }

    public function stepThree(string $id)
    {
        [$enable_pd, $enable_sd, $enable_ed, $enable_lrd] 
            = $this->getNationalApplicationDrafts($id);

        return [
            'enable_pd' => $enable_pd,
            'enable_sd' => $enable_sd,
            'enable_ed' => $enable_ed,
            'enable_lrd' => $enable_lrd,
        ];
        
    }

    public function stepFour(string $id)
    {
       [$enable_pd, $enable_sd, $enable_ed, $enable_lrd] 
            = $this->getNationalApplicationDrafts($id);

        return [
            'enable_pd' => $enable_pd,
            'enable_sd' => $enable_sd,
            'enable_ed' => $enable_ed,
            'enable_lrd' => $enable_lrd,
        ];
        
    }

    public function getNationalApplicationDrafts(string $id)
    {
        $prodCount = \DB::table('national_permission_production_details')->where('national_permission_application_id', $id)->count();
        $equipCount = \DB::table('national_permission_equipment_details')->where('national_permission_application_id', $id)->count();
        $shootCount = \DB::table('national_permission_application_shooting_details')->where('national_permission_application_id', $id)->count();
        $localCount = \DB::table('national_permission_local_representaitives')->where('national_permission_application_id', $id)->count();

        $enable_pd = $enable_ed = $enable_sd = $enable_lrd = false;

        if ($prodCount > 0) {
            $enable_pd = true;
        }

        if ($equipCount > 0) {
            $enable_ed = true;
        }

        if ($shootCount > 0) {
            $enable_sd = true;
        }

        if ($localCount > 0) {
            $enable_lrd = true;
        }

        return [
            $enable_pd,
            $enable_sd,
            $enable_ed,
            $enable_lrd,
        ];

        // return \DB::table('national_permission_applications')
        //     ->select(
        //         'is_draft',
        //         'is_shooting_detail_draft',
        //         'is_equipment_detail_draft',
        //         'is_local_representative_detail_draft',
        //         'payment_status'
        //     )
        //     ->where('id', $id)
        //     ->first();
    }
}