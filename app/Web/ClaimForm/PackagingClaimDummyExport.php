<?php

declare(strict_types=1);

namespace App\Web\ClaimForm;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;
use Carbon\Carbon;

class PackagingClaimDummyExport implements FromCollection, WithHeadings
{
    public function headings(): array
    {
        return [
            'Buyer NP Name',
            'Seller NP Name',
            'Team Registration ID',
            'Udyam Number',
            'Provider ID',
            'Catalogue Score ID',
            'Credential Score ID',
            'Domain',
            'Item Consolidated Category',
            'Network Order ID',
            'Network Transaction ID',
            'Order Status',
            'Order Creation Timestamp',
            'Order Completed Timestamp',
            'Invoice Number',
            'Invoice Date',
            'Cart Level Item Price',
            'Delivery Fee',
            'Total Fee',
        ];
    }

    public function collection(): Collection
    {
        $faker = Faker::create();
        $rows = collect();

        // Get some real team registration IDs and udyam numbers
        $teamData = DB::table('team_msme_schemes')
            ->select('team_id', 'udyam_no')
            ->whereNotNull('team_id')
            ->where('team_id', '!=', '')
            ->whereNotNull('udyam_no')
            ->where('udyam_no', '!=', '')
            ->limit(5)
            ->get();

        if ($teamData->isEmpty()) {
            // Fallback to fake data if none are found in the DB
            for ($i = 0; $i < 5; $i++) {
                $rows->push([
                    'Buyer NP Name' => $faker->name(),
                    'Seller NP Name' => $faker->company(),
                    'Team Registration ID' => 'TEAM' . $faker->numberBetween(100000, 999999),
                    'Udyam Number' => 'UDYAM-MH-12-' . $faker->numberBetween(1000000, 9999999),
                    'Provider ID' => (string) $faker->numberBetween(100000, 999999),
                    'Catalogue Score ID' => (string) $faker->numberBetween(100000, 999999),
                    'Credential Score ID' => (string) $faker->numberBetween(100000, 999999),
                    'Domain' => 'ONDC:RET11',
                    'Item Consolidated Category' => 'F&B',
                    'Network Order ID' => 'ORD' . $faker->numberBetween(100000, 999999),
                    'Network Transaction ID' => 'TXN' . $faker->numberBetween(100000, 999999),
                    'Order Status' => 'Completed',
                    'Order Creation Timestamp' => Carbon::now()->subDays(5)->format('Y-m-d\TH:i:s'),
                    'Order Completed Timestamp' => Carbon::now()->subDays(5)->addHours(2)->format('Y-m-d\TH:i:s'),
                    'Invoice Number' => 'INV' . $faker->numberBetween(100000, 999999),
                    'Invoice Date' => Carbon::now()->subDays(5)->format('d-m-Y'),
                    'Cart Level Item Price' => '500.00',
                    'Delivery Fee' => '50.00',
                    'Total Fee' => '550.00'
                ]);
            }
        } else {
            foreach ($teamData as $team) {
                $rows->push([
                    'Buyer NP Name' => $faker->name(),
                    'Seller NP Name' => $faker->company(),
                    'Team Registration ID' => $team->team_id,
                    'Udyam Number' => $team->udyam_no,
                    'Provider ID' => (string) $faker->numberBetween(100000, 999999),
                    'Catalogue Score ID' => (string) $faker->numberBetween(100000, 999999),
                    'Credential Score ID' => (string) $faker->numberBetween(100000, 999999),
                    'Domain' => 'ONDC:RET11',
                    'Item Consolidated Category' => 'F&B',
                    'Network Order ID' => 'ORD' . $faker->numberBetween(100000, 999999),
                    'Network Transaction ID' => 'TXN' . $faker->numberBetween(100000, 999999),
                    'Order Status' => 'Completed',
                    'Order Creation Timestamp' => Carbon::now()->subDays(5)->format('Y-m-d\TH:i:s'),
                    'Order Completed Timestamp' => Carbon::now()->subDays(5)->addHours(2)->format('Y-m-d\TH:i:s'),
                    'Invoice Number' => 'INV' . $faker->numberBetween(100000, 999999),
                    'Invoice Date' => Carbon::now()->subDays(5)->format('d-m-Y'),
                    'Cart Level Item Price' => '500.00',
                    'Delivery Fee' => '50.00',
                    'Total Fee' => '550.00'
                ]);
            }
        }

        return $rows;
    }
}
