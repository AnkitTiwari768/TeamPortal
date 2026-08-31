<?php

namespace App\Domain\AICataloguingClaim;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class AICataloguingClaimTemplateExport implements FromCollection, WithHeadings
{
    public function headings(): array
    {
        return [
            'Udyam Number',
            'Catalogue ID',
            'Catalogue Finalization Date',
            'Catalogue Completion Status',
            'Digital Catalogue Footprint',
            'Amount Claimed with Financial Reconciliation Report'
        ];
    }

    public function collection(): Collection
    {
        $faker = Faker::create();
        $rows = collect();

        // Get some real udyam numbers from the database
        $udyams = DB::table('team_msme_schemes')
            ->whereNotNull('udyam_no')
            ->where('udyam_no', '!=', '')
            ->limit(5)
            ->pluck('udyam_no')
            ->toArray();

        if (empty($udyams)) {
            // Fallback to fake udyams if none are found in the DB
            for ($i = 0; $i < 5; $i++) {
                $udyams[] = 'UDYAM-MH-12-' . $faker->numberBetween(1000000, 9999999);
            }
        }

        foreach ($udyams as $udyam) {
            $rows->push([
                'Udyam Number' => $udyam,
                'Catalogue ID' => 'CAT-' . $faker->bothify('##??##'),
                'Catalogue Finalization Date' => $faker->dateTimeBetween('-1 month', 'now')->format('d-m-Y'),
                'Catalogue Completion Status' => 1,
                'Digital Catalogue Footprint' => 1,
                'Amount Claimed with Financial Reconciliation Report' => 1
            ]);
        }

        return $rows;
    }
}
