<?php

namespace App\Domain\DemandGeneration;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class DummyTransactionsExport implements FromCollection, WithHeadings
{
    protected array $requiredHeaders = [
        'buyer_np_name',
        'unique_team_registration_id_of_the_mse',
        'udyam_number',
        'provider_id',
        'domain',
        'item_consolidated_category',
        'network_order_id',
        'network_transaction_id',
        'seller_np_name',
        'order_status',
        'order_creation_timestamp',
        'order_completed_timestamp',
        'invoice_number',
        'invoice_date',
        'cart_level_item_price',
        'delivery_fee',
        'total_fee'
    ];

    protected int $numTeams;
    protected int $numTransactions;

    protected array $domainCategoryMap = [
        'Grocery' => 'ONDC:RET10',
        'F&B' => 'ONDC:RET11',
        'Fashion' => 'ONDC:RET12',
        'BPC' => 'ONDC:RET13',
        'Electronics' => 'ONDC:RET14',
        'Appliances' => 'ONDC:RET15',
        'Home & Kitchen' => 'ONDC:RET16',
        'Health & Wellness' => 'ONDC:RET18',
        'Automotives, Components & Accessories' => 'ONDC:RET1A',
        'Hardware and Industrial' => 'ONDC:RET1B',
        'Building and Construction Supplies' => 'ONDC:RET1C',
        'B2C Logistics' => 'nic2004:60232',
        'B2B Domestic Logistics' => 'ONDC:LOG12',
        'B2B International Logistics' => 'ONDC:LOG13',
        'Gift Card' => 'FIS10',
        'Invoice Loan' => 'FIS12',
        'Purchase Finance' => 'FIS12',
        'Credit Line' => 'FIS12',
        'Sachet Insurance' => 'FIS13',
        'Marine Insurance' => 'FIS13',
        'Agriculture Services' => 'ONDC:SRV14',
        'Equipment Hiring' => 'ONDC:SRV17',
        'Repair & Maintenance Services' => 'ONDC:SRV10',
        'Home & Infrastructure Services' => 'ONDC:SRV11',
        'Beauty and Personal Care Services' => 'ONDC:SRV12',
    ];

    public function __construct(int $numTeams = 5, int $numTransactions = 10)
    {
        $this->numTeams = $numTeams;
        $this->numTransactions = $numTransactions;
    }

    public function collection()
    {
        $faker = Faker::create();
        $rows = collect();

        // ✅ Fetch valid combinations from DB
        $teamData = DB::table('team_msme_schemes')
            ->select('team_id', 'udyam_no')
            ->whereNotNull('team_id')
            ->where('team_id', '!=', 'N/A')
            ->limit($this->numTeams)
            ->get();

        if ($teamData->isEmpty()) {
            throw new \Exception('No valid team data found in database');
        }

        for ($i = 0; $i < $this->numTransactions; $i++) {

            // ✅ Pick valid pair
            $team = $faker->randomElement($teamData);

            $category = $faker->randomElement(array_keys($this->domainCategoryMap));
            $domain = $this->domainCategoryMap[$category];

            $cartPrice = $faker->randomFloat(2, 100, 10000);
            $deliveryFee = $faker->randomFloat(2, 10, 500);
            $totalFee = $cartPrice + $deliveryFee;

            $rows->push([
                'buyer_np_name' => $faker->name(),

                // ✅ REAL DATA
                'unique_team_registration_id_of_the_mse' => $team->team_id,
                'udyam_number' => $team->udyam_no,

                'provider_id' => $faker->numberBetween(1, 999999),
                'domain' => $domain,
                'item_consolidated_category' => $category,

                'network_order_id' => strtoupper($faker->bothify('ORD######')),
                'network_transaction_id' => strtoupper($faker->bothify('TXN######')),
                'seller_np_name' => $faker->company(),

                'order_status' => 'COMPLETED',

                // ⚠️ FIX FORMAT (VERY IMPORTANT)
                'order_creation_timestamp' => $faker->date('Y-m-d\TH:i:s'),
                'order_completed_timestamp' => $faker->date('Y-m-d\TH:i:s'),

                // ⚠️ FIX FORMAT
                'invoice_number' => strtoupper($faker->bothify('INV######')),
                'invoice_date' => $faker->date('d-m-Y'),

                'cart_level_item_price' => $cartPrice,
                'delivery_fee' => $deliveryFee,
                'total_fee' => $totalFee,
            ]);
        }

        return $rows;
    }

    public function headings(): array
    {
        return $this->requiredHeaders;
    }
}
