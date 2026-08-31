<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

use DB;

class StateToLocationCategorySeeder extends Seeder 
{
    public function run(): void 
    {
        $locationCategoriesCommon = DB::table('location_categories')->whereIn('name', [
            'Bridge',
            'Building',
            'Bus Depot',
            'Metro',
            'Dilli Haats',
            'Flyover',
            'Foot Over Bridge',
            'Forest',
            'Golf Course',
            'Airport',
            'Railways Junction',
            'Landmarks',
            'Market',
            'Monuments',
            'Others',
            'Park',
            'Public Bus',
            'Railway Station',
            'Road',
            'School',
            'Sports',
            'Stadium'
        ])->get();

        
        $locationCategoriesDelhi = DB::table('location_categories')->whereIn('name', [
            'Bridge',
            'Building',
            'Bus Depot',
            'Delhi Metro',
            'Dilli Haats',
            'Flyover',
            'FoB',
            'Forest',
            'Golf Course',
            'Indira Gandhi International Airport',
            'ISKCON',
            'Delhi Junction',
            'Landmarks',
            'Market',
            'Monuments',
            'Others',
            'Park',
            'Public Bus',
            'Railway',
            'Railway Station',
            'Road',
            'School',
            'Sports',
            'Stadium'
        ])->get();

        $states = DB::table('states')->get();

        $data = [];

        foreach ($states as $state)
        {
            if ($state->id === '99eaa834-698d-418b-91dc-a4fa03bb98ce') 
            {
                foreach ($locationCategoriesDelhi as $lcd) 
                {
                    $data[] = [
                        'state_id' => $state->id,
                        'location_category_id' => $lcd->id
                    ];
                }
            }
            else 
            {
                foreach ($locationCategoriesCommon as $lc) 
                {
                    $data[] = [
                        'state_id' => $state->id,
                        'location_category_id' => $lc->id
                    ];
                }
            }
        }

        DB::table('state_to_location_categories')->insert($data);
    }
}