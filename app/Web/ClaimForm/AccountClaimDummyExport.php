<?php

declare(strict_types=1);

namespace App\Web\ClaimForm;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;
use Carbon\Carbon;

class AccountClaimDummyExport implements FromCollection, WithHeadings
{
    public function headings(): array
    {
        return [
            'seller_np_name',
            'unique_team_registration_id',
            'udyam_number',
            'provider_id',
            'catalogue_score_id',
            'credential_score_id',
            'domain',
            'item_consolidated_category',
            'network_order_id',
            'network_transaction_id',
            'buyer_np_name',
            'order_status',
            'order_creation_timestamp',
            'order_completed_timestamp',
            'invoice_number',
            'invoice_date',
            'cart_level_item_price',
            'delivery_fee',
            'total_fee'
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
                    'seller_np_name' => $faker->company(),
                    'unique_team_registration_id' => 'TEAM' . $faker->numberBetween(100000, 999999),
                    'udyam_number' => 'UDYAM-MH-12-' . $faker->numberBetween(1000000, 9999999),
                    'provider_id' => (string) $faker->numberBetween(100000, 999999),
                    'catalogue_score_id' => (string) $faker->numberBetween(100000, 999999),
                    'credential_score_id' => (string) $faker->numberBetween(100000, 999999),
                    'domain' => 'ONDC:RET11',
                    'item_consolidated_category' => 'F&B',
                    'network_order_id' => 'ORD' . $faker->numberBetween(100000, 999999),
                    'network_transaction_id' => 'TXN' . $faker->numberBetween(100000, 999999),
                    'buyer_np_name' => $faker->name(),
                    'order_status' => 'Completed',
                    'order_creation_timestamp' => Carbon::now()->subDays(5)->format('Y-m-d\TH:i:s'),
                    'order_completed_timestamp' => Carbon::now()->subDays(5)->addHours(2)->format('Y-m-d\TH:i:s'),
                    'invoice_number' => 'INV' . $faker->numberBetween(100000, 999999),
                    'invoice_date' => Carbon::now()->subDays(5)->format('d-m-Y'),
                    'cart_level_item_price' => '500.00',
                    'delivery_fee' => '50.00',
                    'total_fee' => '550.00'
                ]);
            }
        } else {
            foreach ($teamData as $team) {
                $rows->push([
                    'seller_np_name' => $faker->company(),
                    'unique_team_registration_id' => $team->team_id,
                    'udyam_number' => $team->udyam_no,
                    'provider_id' => (string) $faker->numberBetween(100000, 999999),
                    'catalogue_score_id' => (string) $faker->numberBetween(100000, 999999),
                    'credential_score_id' => (string) $faker->numberBetween(100000, 999999),
                    'domain' => 'ONDC:RET11',
                    'item_consolidated_category' => 'F&B',
                    'network_order_id' => 'ORD' . $faker->numberBetween(100000, 999999),
                    'network_transaction_id' => 'TXN' . $faker->numberBetween(100000, 999999),
                    'buyer_np_name' => $faker->name(),
                    'order_status' => 'Completed',
                    'order_creation_timestamp' => Carbon::now()->subDays(5)->format('Y-m-d\TH:i:s'),
                    'order_completed_timestamp' => Carbon::now()->subDays(5)->addHours(2)->format('Y-m-d\TH:i:s'),
                    'invoice_number' => 'INV' . $faker->numberBetween(100000, 999999),
                    'invoice_date' => Carbon::now()->subDays(5)->format('d-m-Y'),
                    'cart_level_item_price' => '500.00',
                    'delivery_fee' => '50.00',
                    'total_fee' => '550.00'
                ]);
            }
        }

        return $rows;
    }
}
