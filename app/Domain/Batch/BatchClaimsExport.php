<?php

declare(strict_types=1);

namespace App\Domain\Batch;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class BatchClaimsExport implements FromArray, WithHeadings
{
    private string $batchId;
    private string $claimTypeSlug;

    public function __construct(string $batchId, string $claimTypeSlug)
    {
        $this->batchId = $batchId;
        $this->claimTypeSlug = $claimTypeSlug;
    }

    public function headings(): array
    {
        return match ($this->claimTypeSlug) {
            'claim-for-catalogue-creation'            => $this->catalogueHeadings(),
            'claim-for-demand-generation'             => $this->demandGenerationHeadings(),
            'claim-for-accounts-management'           => $this->accountsManagementHeadings(),
            'claim-for-packaging'                     => $this->packagingHeadings(),
            'claim-for-transportation-and-logistic'  => $this->logisticsHeadings(),
            default                                   => ['Claim Amount']
        };
    }

    public function array(): array
    {
        $claims = DB::table('dy_batch_claims as bc')
            ->join('claims as c', 'c.id', '=', 'bc.claim_id')
            ->where('bc.batch_id', $this->batchId)
            ->whereNull('bc.is_deleted')
            ->select('c.*')
            ->get();

        $rows = [];

        foreach ($claims as $claim) {
            $orders = DB::table('claim_orders')
                ->where('claim_id', $claim->id)
                ->orderBy('id', 'asc')
                ->get();

            if ($this->claimTypeSlug === 'claim-for-catalogue-creation') {
                $rows[] = $this->mapCatalogueClaim($claim, $orders);
            } else {
                if ($orders->isEmpty()) {
                    $rows[] = $this->mapSingleTransactionRow($claim, null);
                } else {
                    foreach ($orders as $order) {
                        $rows[] = $this->mapSingleTransactionRow($claim, $order);
                    }
                }
            }
        }

        return $rows;
    }

    private function mapSingleTransactionRow($claim, $order): array
    {
        return match ($this->claimTypeSlug) {
            'claim-for-demand-generation'            => $this->mapDemandGenerationRow($claim, $order),
            'claim-for-accounts-management'          => $this->mapAccountsManagementRow($claim, $order),
            'claim-for-packaging'                    => $this->mapPackagingRow($claim, $order),
            'claim-for-transportation-and-logistic' => $this->mapLogisticsRow($claim, $order),
            default                                  => [$claim->amount ?? 0]
        };
    }

    // --- Heading Helpers ---

    private function catalogueHeadings(): array
    {
        return [
            'seller_np_name',
            'unique_team_registration_id',
            'udyam_number',
            'provider_id',
            'catalogue_score_id',
            'catalogue_score_timestamp',
            'catalogue_score_url',
            'credential_score_id',
            'credential_score_timestamp',
            'credential_score_url',
            'txn_1_domain',
            'txn_1_item_consolidated_category',
            'txn_1_network_order_id',
            'txn_1_network_transaction_id',
            'txn_1_buyer_np_name',
            'txn_1_order_status',
            'txn_1_order_creation_timestamp',
            'txn_1_order_completed_timestamp',
            'txn_1_invoice_number',
            'txn_1_invoice_date',
            'txn_1_cart_level_item_price',
            'txn_1_delivery_fee',
            'txn_1_total_fee',
            'txn_2_domain',
            'txn_2_item_consolidated_category',
            'txn_2_network_order_id',
            'txn_2_network_transaction_id',
            'txn_2_buyer_np_name',
            'txn_2_order_status',
            'txn_2_order_creation_timestamp',
            'txn_2_order_completed_timestamp',
            'txn_2_invoice_number',
            'txn_2_invoice_date',
            'txn_2_cart_level_item_price',
            'txn_2_delivery_fee',
            'txn_2_total_fee',
            'claim_amount',
        ];
    }

    private function demandGenerationHeadings(): array
    {
        return [
            'buyer_np_name',
            'unique_team_registration_id_of_the_mse',
            'udyam_number',
            'provider_id',
            'domain',
            'aov_grouping_type',
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
            'total_fee',
            'claim_amount'
        ];
    }

    private function accountsManagementHeadings(): array
    {
        return [
            'unique_team_registration_id',
            'seller_np_name',
            'udyam_number',
            'provider_id',
            'domain',
            'aov_grouping_type',
            'item_consolidated_category',
            'network_order_id',
            'network_transaction_id',
            'buyer_np_name',
            'order_creation_timestamp',
            'order_completed_timestamp',
            'order_status',
            'invoice_number',
            'invoice_date',
            'cart_level_item_price',
            'delivery_fee',
            'total_fee',
            'claim_amount'
        ];
    }

    private function packagingHeadings(): array
    {
        return [
            'Buyer NP Name',
            'Seller NP Name',
            'Team Registration ID',
            'Udyam Number',
            'Provider ID',
            'Domain',
            'AOV Grouping Type',
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
            'Claim Amount'
        ];
    }

    private function logisticsHeadings(): array
    {
        return [
            'Buyer NP Name',
            'Seller NP Name',
            'Team Registration ID',
            'Udyam Number',
            'Provider ID',
            'Domain',
            'AOV Grouping Type',
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
            'Claim Amount'
        ];
    }

    // --- Mapping Helpers ---

    private function mapCatalogueClaim($claim, $orders): array
    {
        $order1 = $orders->get(0);
        $order2 = $orders->get(1);

        return [
            $order1->seller_np_name ?? $claim->seller_np_name ?? '',
            $claim->team_registration_id ?? '',
            $claim->msme_udyam_number ?? '',
            $claim->bpp_id ?? $claim->ondc_seller_network_id ?? '',
            $claim->catalogue_score_report ?? '',
            $claim->catalogue_score_timestamp ?? '',
            $claim->catalogue_score_url ?? '',
            $claim->seller_credential_report ?? '',
            $claim->credential_score_timestamp ?? '',
            $claim->seller_credential_score_url ?? '',

            $order1->domain ?? '',
            $order1->item_consolidated_category ?? '',
            $order1->ondc_order_id ?? '',
            $order1->network_transaction_id ?? '',
            $order1->buyer_np_name ?? '',
            $order1->order_status ?? '',
            $order1->order_creation_timestamp ?? '',
            $order1->order_completed_timestamp ?? '',
            $order1->invoice_number ?? '',
            $order1->invoice_date ?? '',
            $order1->cart_level_item_price ?? '',
            $order1->delivery_fee ?? '',
            $order1->total_fee ?? '',

            $order2->domain ?? '',
            $order2->item_consolidated_category ?? '',
            $order2->ondc_order_id ?? '',
            $order2->network_transaction_id ?? '',
            $order2->buyer_np_name ?? '',
            $order2->order_status ?? '',
            $order2->order_creation_timestamp ?? '',
            $order2->order_completed_timestamp ?? '',
            $order2->invoice_number ?? '',
            $order2->invoice_date ?? '',
            $order2->cart_level_item_price ?? '',
            $order2->delivery_fee ?? '',
            $order2->total_fee ?? '',

            $claim->amount ?? 0
        ];
    }

    private function mapDemandGenerationRow($claim, $order = null): array
    {
        return [
            $order->buyer_np_name ?? $claim->buyer_np_name ?? '',
            $order->team_id ?? $claim->team_registration_id ?? '',
            $order->msme_udyam_number ?? $claim->msme_udyam_number ?? '',
            $order->provider_id ?? $claim->bpp_id ?? '',
            $order->domain ?? '',
            $order->aov_grouping_type ?? '',
            $order->item_consolidated_category ?? '',
            $order->ondc_order_id ?? '',
            $order->network_transaction_id ?? '',
            $order->seller_np_name ?? $claim->seller_np_name ?? '',
            $order->order_status ?? '',
            $order->order_creation_timestamp ?? '',
            $order->order_completed_timestamp ?? '',
            $order->invoice_number ?? '',
            $order->invoice_date ?? '',
            $order->cart_level_item_price ?? '',
            $order->delivery_fee ?? '',
            $order->total_fee ?? '',
            $claim->amount ?? 0
        ];
    }

    private function mapAccountsManagementRow($claim, $order = null): array
    {
        return [
            $claim->team_registration_id ?? $order->team_id ?? '',
            $claim->seller_np_name ?? $order->seller_np_name ?? '',
            $claim->msme_udyam_number ?? $order->msme_udyam_number ?? '',
            $claim->bpp_id ?? $order->provider_id ?? '',
            $order->domain ?? '',
            $order->aov_grouping_type ?? '',
            $order->item_consolidated_category ?? '',
            $order->ondc_order_id ?? '',
            $order->network_transaction_id ?? '',
            $order->buyer_np_name ?? '',
            $order->order_creation_timestamp ?? '',
            $order->order_completed_timestamp ?? '',
            $order->order_status ?? '',
            $order->invoice_number ?? '',
            $order->invoice_date ?? '',
            $order->cart_level_item_price ?? '',
            $order->delivery_fee ?? '',
            $order->total_fee ?? '',
            $claim->amount ?? 0
        ];
    }

    private function mapPackagingRow($claim, $order = null): array
    {
        return [
            $order->buyer_np_name ?? '',
            $claim->seller_np_name ?? $order->seller_np_name ?? '',
            $claim->team_registration_id ?? $order->team_id ?? '',
            $claim->msme_udyam_number ?? $order->msme_udyam_number ?? '',
            $claim->bpp_id ?? $order->provider_id ?? '',
            $order->domain ?? '',
            $order->aov_grouping_type ?? '',
            $order->item_consolidated_category ?? '',
            $order->ondc_order_id ?? '',
            $order->network_transaction_id ?? '',
            $order->order_status ?? '',
            $order->order_creation_timestamp ?? '',
            $order->order_completed_timestamp ?? '',
            $order->invoice_number ?? '',
            $order->invoice_date ?? '',
            $order->cart_level_item_price ?? '',
            $order->delivery_fee ?? '',
            $order->total_fee ?? '',
            $claim->amount ?? 0
        ];
    }

    private function mapLogisticsRow($claim, $order = null): array
    {
        return [
            $order->buyer_np_name ?? '',
            $claim->seller_np_name ?? $order->seller_np_name ?? '',
            $claim->team_registration_id ?? $order->team_id ?? '',
            $claim->msme_udyam_number ?? $order->msme_udyam_number ?? '',
            $claim->bpp_id ?? $order->provider_id ?? '',
            $order->domain ?? '',
            $order->aov_grouping_type ?? '',
            $order->item_consolidated_category ?? '',
            $order->ondc_order_id ?? '',
            $order->network_transaction_id ?? '',
            $order->order_status ?? '',
            $order->order_creation_timestamp ?? '',
            $order->order_completed_timestamp ?? '',
            $order->invoice_number ?? '',
            $order->invoice_date ?? '',
            $order->cart_level_item_price ?? '',
            $order->delivery_fee ?? '',
            $order->total_fee ?? '',
            $claim->amount ?? 0
        ];
    }
}
