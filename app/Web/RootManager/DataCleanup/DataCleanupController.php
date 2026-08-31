<?php

declare(strict_types=1);

namespace App\Web\RootManager\DataCleanup;

use App\Http\Controllers\ClientController;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class DataCleanupController extends ClientController
{
    private const CLAIMS_TABLES = [
        'dy_queries',
        'qms_attachments',
        'qms_messages',
        'qms_queries',
        'temporary_claims',
        'temporary_claim_orders',
        'claims',
        'claim_orders',
        'dy_batches',
        'dy_batch_claims',
        'dy_workflow_logs',
        'dy_workflow_instances',
    ];

    private const COMPONENT_UTILIZATION_TABLES = [
        'component_utilization_mappings',
        'component_utilization_mapping_details',
    ];

    private const FUND_ALLOCATION_TABLES = [
        'fund_allocations',
        'fund_allocations_map',
        'fund_allocation_component_mappings',
        'fund_allocation_histories',
        'fund_carry_forwards',
        'fund_carry_forward_details',
        'fund_carry_forward_logs',
    ];

    private const FUND_DISTRIBUTION_TABLES = [
        'fund_distributions',
        'fund_pools',
    ];

    private const WORKSHOP_TABLES = [
        'workshops',
        'workshop_expenses',
    ];

    public function index()
    {
        return view('root-manager.data-cleanup.index', [
            'title' => 'Data Cleanup',
        ]);
    }

    public function deleteClaimsData(): RedirectResponse
    {
        return $this->truncateTables(self::CLAIMS_TABLES, 'All claims data');
    }

    public function deleteComponentUtilizationData(): RedirectResponse
    {
        return $this->truncateTables(self::COMPONENT_UTILIZATION_TABLES, 'All component utilization data');
    }

    public function deleteFundAllocationData(): RedirectResponse
    {
        return $this->truncateTables(self::FUND_ALLOCATION_TABLES, 'All fund allocation data');
    }

    public function deleteFundDistributionData(): RedirectResponse
    {
        return $this->truncateTables(self::FUND_DISTRIBUTION_TABLES, 'All fund distribution data');
    }

    public function deleteWorkshopData(): RedirectResponse
    {
        return $this->truncateTables(self::WORKSHOP_TABLES, 'All workshop data');
    }

    private function truncateTables(array $tables, string $label): RedirectResponse
    {
        try {
            DB::statement('SET FOREIGN_KEY_CHECKS=0');

            foreach ($tables as $table) {
                DB::table($table)->delete();
            }

            DB::statement('SET FOREIGN_KEY_CHECKS=1');

            return redirect()->back()->with('success', "{$label} deleted successfully.");
        } catch (\Throwable $e) {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');

            return redirect()->back()->with('error', "Failed to delete {$label}: {$e->getMessage()}");
        }
    }
}
