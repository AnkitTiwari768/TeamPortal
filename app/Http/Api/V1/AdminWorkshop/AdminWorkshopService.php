<?php

namespace App\Http\Api\V1\AdminWorkshop;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Core\BaseService;
use App\Http\Services\CommonService;
use Carbon\Carbon;

class AdminWorkshopService extends BaseService
{
    private array $attributeCache = [];

    protected array $columns = [
        1 => 'w.title',
        2 => 'w.financial_year',
        3 => 'w.duration',
        4 => 's.name',
        5 => 'total_expense',
    ];


    public function getList(array $controllerFilters = [])
    {
        [$limit, $order, $dir, $search, $page, $dtFilters] = $this->getDataTableParams('w');

        $filters = array_merge($controllerFilters, $dtFilters ?? []);

        $query = DB::table('admin_workshops as w')
            ->leftJoin('states as s', DB::raw('w.state_id COLLATE utf8mb4_unicode_ci'), '=', DB::raw('s.id COLLATE utf8mb4_unicode_ci'))
            ->leftJoin('locations as l', DB::raw('w.district_id COLLATE utf8mb4_unicode_ci'), '=', DB::raw('l.id COLLATE utf8mb4_unicode_ci'))
            ->leftJoin('admin_workshop_expenses as e', DB::raw('w.id COLLATE utf8mb4_unicode_ci'), '=', DB::raw('e.admin_workshop_id COLLATE utf8mb4_unicode_ci'))
            ->leftJoin('attribute_values as av', DB::raw('w.duration COLLATE utf8mb4_unicode_ci'), '=', DB::raw('av.id COLLATE utf8mb4_unicode_ci'))
            ->select(
                'w.*',
                's.name as state_name',
                'l.name as district_name',
                'av.attribute_value as duration_name',
                DB::raw('SUM(e.expense_amount) as total_expense')
            )
            ->groupBy(
                'w.id',
                's.name',
                'l.name',
                'av.attribute_value'
            );

        // ✅ STATE FILTER
        if (!empty($filters['state_id'])) {
            $query->where('w.state_id', $filters['state_id']);
        }

        if (!empty($filters['from_dates']) && !empty($filters['to_dates'])) {
            $from = Carbon::createFromFormat('d-m-Y', $filters['from_dates'])->format('Y-m-d');
            $to = Carbon::createFromFormat('d-m-Y', $filters['to_dates'])->format('Y-m-d');
            $query->whereBetween('w.workshop_date', [$from, $to]);
        }

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('w.title', 'like', "%{$search}%")
                    ->orWhere('w.workshop_date', 'like', "%{$search}%")
                    ->orWhere('w.financial_year', 'like', "%{$search}%")
                    ->orWhere('av.attribute_value', 'like', "%{$search}%")
                    ->orWhere('w.workshop_mode', 'like', "%{$search}%")
                    ->orWhere('w.state_id', 'like', "%{$search}%")
                    ->orWhere('e.expense_amount', 'like', "%{$search}%");
            });
        }



        if (function_exists('hasRole')) {
            if (hasRole('snp')) {
                $query->where('w.state_id', auth()->user()->state_id ?? null);
            } elseif (hasRole('bnp')) {
                $query->where('w.district_id', auth()->user()->district_id ?? null);
            } elseif (hasRole('lsp')) {
                // Specific role logic for lsp if needed
            }
        }

        $orderColumn = $this->columns[$order] ?? 'w.created_at';
        $query->orderBy($orderColumn, $dir);

        if ($page) {
            return $this->getDataTableResult(
                AdminWorkshopResource::collection($query->paginate($limit))
            );
        }

        return AdminWorkshopResource::collection($query->get());
    }

    public function getById(string $id)
    {
        $workshop = DB::table('admin_workshops as aw')
            ->leftJoin('states as s', 's.id', '=', 'aw.state_id')
            ->leftJoin('locations as l', 'l.id', '=', 'aw.district_id')
            ->leftJoin('sub_districts as sd', 'sd.id', '=', 'aw.sub_district_id')
            ->leftJoin('admin_workshop_expenses as e', 'e.admin_workshop_id', '=', 'aw.id')
            ->leftJoin('attribute_values as av', DB::raw('aw.duration COLLATE utf8mb4_unicode_ci'), '=', DB::raw('av.id COLLATE utf8mb4_unicode_ci'))
            ->where('aw.id', $id)
            ->select(
                'aw.*',

                // ✅ EXPENSE COLUMNS SAFE ALIAS
                'e.id as expense_id',
                'e.expense_amount',
                'e.tds_applicable',
                'e.tds_percentage',
                'e.net_amount',
                'e.sanction_order_no',
                'e.sanction_order_date',


                'av.attribute_value as duration_name',
                's.name as state_name',
                'l.name as district_name'
            )
            ->first();

        if (!$workshop)
            return null;

        // ✅ Expenses alag se lo (IMPORTANT)
        $workshop->expenses = DB::table('admin_workshop_expenses')
            ->where('admin_workshop_id', $id)
            ->get();

        $workshop->schedules = DB::table('admin_workshop_schedules')
            ->where('admin_workshop_id', $id)
            ->get();

        return $workshop;
    }

    public function getByIdshow(string $id)
    {
        //$workshop = DB::table('admin_workshops')->where('id', $id)->first();
        $workshop = DB::table('admin_workshops as aw')
            ->leftJoin('states as s', 's.id', '=', 'aw.state_id')
            ->leftJoin('locations as l', 'l.id', '=', 'aw.district_id')
            ->leftjoin('admin_workshop_expenses as e', 'e.admin_workshop_id', '=', 'aw.id')
            ->leftJoin('attribute_values as av', 'av.id', '=', 'aw.duration')
            ->leftJoin('attribute_values as sav', 'sav.id', '=', 'aw.sub_duration')
            ->leftjoin('attribute_values as tm', function ($join) {
                $join->on(DB::raw('aw.workshop_mode COLLATE utf8mb4_unicode_ci'), '=', DB::raw('tm.id COLLATE utf8mb4_unicode_ci'))
                    ->where('tm.attribute_id', function ($query) {
                        $query->select('id')
                            ->from('attributes')
                            ->where('code', 'admin-workshop-mode')
                            ->where('status', 1)
                            ->limit(1);
                    });
            })
            ->where('aw.id', $id)
            ->select(
                'aw.*',
                'e.*',
                'av.attribute_value as duration_name',
                'sav.attribute_value as sub_duration_name',
                's.name as state_name',
                'l.name as district_name',
                'tm.attribute_value as workshop_mode'
            )
            ->first();

        if (!$workshop)
            return null;
        $map = $this->getAttributeMap([
            'admin-workshop-venue',
            'admin-workshop-mode',
            'admin-workshop-organizer-name',
            'admin-workshop-conducted-by',
            'admin-workshop-target-audience',
            'duration'
        ]);

        $workshop->venue_name = $map[$workshop->venue] ?? null;
        $workshop->workshop_mode_name = $map[$workshop->workshop_mode] ?? null;
        $workshop->organizer_name_text = $map[$workshop->organizer_name] ?? null;
        $workshop->conducted_by_name = $map[$workshop->conducted_by] ?? null;
        $workshop->target_audience_name = $map[$workshop->target_audience] ?? null;
        $workshop->duration_name = $map[$workshop->duration] ?? $workshop->duration;

        if ($workshop) {
            $workshop->schedules = DB::table('admin_workshop_schedules')
                ->where('admin_workshop_id', $id)
                ->get();

            $workshop->expenses = DB::table('admin_workshop_expenses')
                ->where('admin_workshop_id', $id)
                ->get();
        }

        return $workshop;
    }


    public function store(AdminWorkshopDTO $dto): object
    {
        //dd($dto);
        return DB::transaction(function () use ($dto) {
            $workshopId = Str::uuid()->toString();
            $currentTime = now();
            $userId = auth()->id() ?? null;

            DB::table('admin_workshops')->insert([
                'id' => $workshopId,
                'financial_year' => $dto->financialYear,
                'duration' => $dto->duration,
                'sub_duration' => $dto->subDuration,
                'title' => $dto->title,
                'workshop_date' => $dto->workshopDate,
                'state_id' => $dto->stateId,
                'district_id' => $dto->districtId,
                'venue' => $dto->venue,
                'workshop_mode' => $dto->workshopMode,
                'organizer_name' => $dto->organizerName,
                'conducted_by' => $dto->conductedBy,
                'target_audience' => $dto->targetAudience,
                'number_of_participants' => $dto->numberOfParticipants,
                'workshop_description' => $dto->workshopDescription,
                'remarks' => $dto->remarks,
                'uploaded_ids' => $dto->uploadedIds,
                'status' => $dto->status,
                'created_by' => $userId,
                'updated_by' => $userId,
                'created_at' => $currentTime,
                'updated_at' => $currentTime,
                'nsic_fees' => $dto->nsic_fees,
                'conduct_by_other' => $dto->conducted_by_other,
                'branch_office_id' => $dto->branch_office,
                'sub_district_id' => $dto->sub_district_id,
            ]);

            $this->syncSchedules($workshopId, $dto->schedules, $currentTime);

            if (!empty($dto->expenseAmount) && $dto->expenseAmount > 0) {
                $this->insertExpense(
                    workshopId: $workshopId,
                    expenseAmount: $dto->expenseAmount,
                    tdsApplicable: $dto->tdsApplicable,
                    tdsPercentage: $dto->tdsPercentage,
                    sanctionOrderNumber: $dto->sanctionOrderNumber,
                    sanctionOrderDate: $dto->sanctionOrderDate,
                    currentTime: $currentTime
                );

                // ✅ AUTOMATED FUND POOL DISTRIBUTION INTEGRATION (CREATE FLOW 🔥)
                $this->processWorkshopFundDistribution($workshopId, $dto);
            }

            return $this->getById($workshopId);
        });
    }


    public function updateAdminWorkshop(string $id, AdminWorkshopDTO $dto): object
    {
        return DB::transaction(function () use ($id, $dto) {

            $currentTime = now();
            $userId = auth()->id() ?? null;

            // ✅ UPDATE WORKSHOP
            DB::table('admin_workshops')->where('id', $id)->update([
                'financial_year' => $dto->financialYear,
                'duration' => $dto->duration,
                'sub_duration' => $dto->subDuration,
                'title' => $dto->title,
                'workshop_date' => $dto->workshopDate,
                'state_id' => $dto->stateId,
                'district_id' => $dto->districtId,
                'venue' => $dto->venue,
                'workshop_mode' => $dto->workshopMode,
                'organizer_name' => $dto->organizerName,
                'conducted_by' => $dto->conductedBy,
                'target_audience' => $dto->targetAudience,
                'number_of_participants' => $dto->numberOfParticipants,
                'workshop_description' => $dto->workshopDescription,
                'remarks' => $dto->remarks,
                'uploaded_ids' => $dto->uploadedIds,
                'status' => $dto->status,
                'updated_by' => $userId,
                'updated_at' => $currentTime,
                'nsic_fees' => $dto->nsic_fees,
                'conduct_by_other' => $dto->conducted_by_other,
                'branch_office_id' => $dto->branch_office,
                'sub_district_id' => $dto->sub_district_id,
            ]);

            // ✅ SCHEDULE UPDATE
            $this->syncSchedules($id, $dto->schedules, $currentTime);

            // ✅ EXPENSE LOGIC (IMPORTANT 🔥)
            if (!empty($dto->expenseAmount) && $dto->expenseAmount > 0) {

                $existingExpense = DB::table('admin_workshop_expenses')
                    ->where('admin_workshop_id', $id)
                    ->first();

                // Calculate
                $tdsAmount = ($dto->tdsApplicable == 1)
                    ? ($dto->expenseAmount * $dto->tdsPercentage) / 100
                    : 0;

                $netAmount = $dto->expenseAmount - $tdsAmount;

                if ($existingExpense) {

                    // ✅ UPDATE
                    DB::table('admin_workshop_expenses')
                        ->where('admin_workshop_id', $id)
                        ->update([
                            'expense_amount' => round($dto->expenseAmount, 2),
                            'tds_applicable' => $dto->tdsApplicable,
                            'tds_percentage' => round($dto->tdsPercentage, 2),
                            'tds_amount' => round($tdsAmount, 2),
                            'net_amount' => round($netAmount, 2),
                            'sanction_order_no' => $dto->sanctionOrderNumber,
                            'sanction_order_date' => $dto->sanctionOrderDate,
                            'updated_at' => $currentTime,
                        ]);

                } else {

                    // ✅ INSERT (if not exists)
                    $this->insertExpense(
                        workshopId: $id,
                        expenseAmount: $dto->expenseAmount,
                        tdsApplicable: $dto->tdsApplicable,
                        tdsPercentage: $dto->tdsPercentage,
                        sanctionOrderNumber: $dto->sanctionOrderNumber,
                        sanctionOrderDate: $dto->sanctionOrderDate,
                        currentTime: $currentTime
                    );
                }
            }

            // ✅ FUND DISTRIBUTION UPDATE LOGIC (ISOLATED 🔥)
            $distribution = DB::table('fund_distributions')
                ->where('source_type', 'WORKSHOP')
                ->where('source_id', $id)
                ->whereNull('deleted_at')
                ->first();

            if ($distribution) {
                $originalAmount = (float) $distribution->distribution_amount;

                // Isolated Tax Calculation using Original frozen Amount
                $tdsPct = ($dto->tdsApplicable == 1) ? (float) $dto->tdsPercentage : 0.0;

                $newTdsAmt = round(($originalAmount * $tdsPct) / 100, 2);
                $newNetPayable = round($originalAmount - $newTdsAmt, 2);

                DB::table('fund_distributions')
                    ->where('id', $distribution->id)
                    ->update([
                        'tds_percentage' => round($tdsPct, 2),
                        'tds_amount' => $newTdsAmt,
                        'net_payable_amount' => $newNetPayable,
                        'updated_at' => $currentTime,
                        'updated_by' => $userId,
                    ]);
            }

            return $this->getById($id);
        });
    }


    public function addAdminWorkshopExpense(string $adminWorkshopId, AdminWorkshopExpenseDTO $dto): object
    {
        $currentTime = now();

        $expenseId = $this->insertExpense(
            workshopId: $adminWorkshopId,
            expenseAmount: $dto->expenseAmount,
            tdsApplicable: $dto->tdsApplicable,
            tdsPercentage: $dto->tdsPercentage,
            sanctionOrderNumber: $dto->sanctionOrderNo,
            sanctionOrderDate: $dto->sanctionOrderDate,
            currentTime: $currentTime,
            componentId: $dto->componentId,
            subcomponentId: $dto->subcomponentId,
        );

        return DB::table('admin_workshop_expenses')->where('id', $expenseId)->first();
    }


    private function insertExpense(string $workshopId, float $expenseAmount, int $tdsApplicable, float $tdsPercentage, ?string $sanctionOrderNumber, ?string $sanctionOrderDate, mixed $currentTime, ?string $componentId = null, ?string $subcomponentId = null, ): string
    {

        if ($tdsApplicable === 1 && $tdsPercentage > 0) {
            $tdsAmount = ($expenseAmount * $tdsPercentage) / 100;
        } else {
            $tdsPercentage = 0;
            $tdsAmount = 0;
        }

        $netAmount = $expenseAmount - $tdsAmount;
        $expenseId = Str::uuid()->toString();

        DB::table('admin_workshop_expenses')->insert([
            'id' => $expenseId,
            'admin_workshop_id' => $workshopId,
            'component_id' => $componentId,
            'subcomponent_id' => $subcomponentId,
            'expense_amount' => round($expenseAmount, 2),
            'tds_applicable' => $tdsApplicable,
            'tds_percentage' => round($tdsPercentage, 2),
            'tds_amount' => round($tdsAmount, 2),
            'net_amount' => round($netAmount, 2),
            'sanction_order_no' => $sanctionOrderNumber,
            'sanction_order_date' => $sanctionOrderDate,
            'created_at' => $currentTime,
            'updated_at' => $currentTime,
        ]);

        return $expenseId;
    }

    private function syncSchedules(string $adminWorkshopId, array $schedulesPayload, mixed $currentTime): void
    {
        DB::table('admin_workshop_schedules')
            ->where('admin_workshop_id', $adminWorkshopId)
            ->delete();

        if (!empty($schedulesPayload)) {
            $rows = array_map(fn($s) => [
                'id' => Str::uuid()->toString(),
                'admin_workshop_id' => $adminWorkshopId,
                'start_time' => $s['start_time'],
                'end_time' => $s['end_time'],
                'created_at' => $currentTime,
                'updated_at' => $currentTime,
            ], $schedulesPayload);

            DB::table('admin_workshop_schedules')->insert($rows);
        }
    }

    public function getDropdownList(): array
    {
        $commonService = new CommonService();
        return [
            'state_id' => $commonService->getStates(countryId: ''),
            'sub_domains' => $commonService->getDropdownNewList(
                'sub_domains',
                'status',
                'ASC',
                'name',
                ['id', 'name']
            ),
        ];
    }

    public function getAttributesValue(string $code): mixed
    {
        $attribute = DB::table('attributes')
            ->where('code', $code)
            ->where('status', 1)
            ->first();

        if (!$attribute) {
            return [];
        }

        $query = DB::table('attribute_values')
            ->where('attribute_id', $attribute->id)
            ->where('status', 1);

        if ($code === 'duration') {
            $query->whereNull('parent_id');
        }

        return $query->orderBy('attribute_value', 'ASC')
            ->get(['id', 'attribute_value']);
    }

    public function getCumulativeExpenseByComponent(string $componentId, ?string $subcomponentId = null): float
    {
        $query = DB::table('admin_workshop_expenses')
            ->where('component_id', $componentId);

        if ($subcomponentId) {
            $query->where('subcomponent_id', $subcomponentId);
        }

        return (float) $query->sum('net_amount');
    }

    public function getAttributeMap(array $codes): array
    {
        $attributes = DB::table('attributes')
            ->whereIn('code', $codes)
            ->where('status', 1)
            ->pluck('id', 'code');

        $values = DB::table('attribute_values')
            ->whereIn('attribute_id', $attributes->values())
            ->where('status', 1)
            ->get(['id', 'attribute_value']);

        return $values->pluck('attribute_value', 'id')->toArray();
    }
    public function district_list($state_id = null)
    {
        $list = ['' => 'Select'];

        $query = DB::table('locations')
            ->select('id', 'name')
            ->where('status', 1);

        if (!empty($state_id)) {
            $query->where('state_id', $state_id);
        }

        $districts = $query->orderBy('name')->get();

        foreach ($districts as $district) {
            $list[$district->id] = $district->name;
        }

        return $list;
    }

    public function getSubDurationByParent(string $parentId): mixed
    {
        $attribute = DB::table('attributes')
            ->where('code', 'duration')
            ->where('status', 1)
            ->first();

        if (!$attribute) {
            return [];
        }

        return DB::table('attribute_values')
            ->where('attribute_id', $attribute->id)
            ->where('status', 1)
            ->where('parent_id', $parentId)
            ->orderBy('sort_order', 'ASC')
            ->get(['id', 'attribute_value']);
    }

    /**
     * Private helper orchestrating high-fidelity Waterfall Deductions
     * across candidate allocation pools during primary workshop storage.
     */
    private function processWorkshopFundDistribution(string $workshopId, AdminWorkshopDTO $dto): void
    {
        $poolResolver = app(\App\Web\FundDistribution\ClaimPoolResolver::class);
        $processor    = app(\App\Web\FundDistribution\ClaimDistributionProcessor::class);

        // 1. Multi-entry guard constraint
        $exists = DB::table('fund_distributions')
            ->where('source_type', 'WORKSHOP')
            ->where('source_id', $workshopId)
            ->whereNull('deleted_at')
            ->exists();

        if ($exists) {
            return;
        }

        // 2. Component mappings
        $majorComponentId = 'cfdaa9b0-471b-4509-9b26-ee378280e176'; // "For NSIC"
        $subComponentId   = '79be82d7-b562-4a24-94c9-25f4e00c7a2d'; // "Awareness Generation..."

        // 3. Assemble resolver search criteria payload
        $criteria = [
            'financial_year'     => $dto->financialYear,
            'major_component_id' => $majorComponentId,
            'sub_component_id'   => $subComponentId,
            'duration_id'        => $dto->duration,
            'sub_duration_id'    => $dto->subDuration,
        ];

        // 4. Discover applicable candidate fund pools
        $discoveredPools = $poolResolver->resolve($criteria);

        if ($discoveredPools->isEmpty()) {
            throw new \Exception("Operational constraint failed: No fundamental pool container exists for specified allocations.");
        }

        // 5. Formulate static deductive transfer packet
        $transferMetadata = [
            'amount'           => (float) $dto->expenseAmount,
            'tds_percentage'   => ($dto->tdsApplicable == 1) ? (float) $dto->tdsPercentage : 0.0,
            'source_type'      => 'WORKSHOP',
            'source_id'        => $workshopId,
            'reference_number' => $dto->sanctionOrderNumber,
            'remarks'          => "Automated Workshop Deduction",
            'user_id'          => auth()->id() ?? null,
            'duration_id'      => $dto->duration,
            'sub_duration_id'  => $dto->subDuration,
        ];

        // 6. Release atomic physical ledger deductions
        $processor->executeTransactionalDeductions($discoveredPools, $transferMetadata);
    }

    public function getBranchOfficeName(string $codes = null)
    {
        return DB::table('nsic_branch_offices')
            ->when($codes, function ($q) use ($codes) {
                $q->where('code', $codes);
            })
            ->where('status', 1)
            ->pluck('name', 'id')
            ->toArray();
    }
}
