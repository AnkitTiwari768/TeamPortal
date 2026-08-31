<?php

declare(strict_types=1);

namespace App\Web\Workshop;


use App\Core\BaseService;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Web\Workshop\WorkshopStatus;

class WorkshopService extends BaseService
{
    public function getEventData(string $eventId)
    {
        $row = DB::table('workshops as w')
            ->leftJoin('workshop_expenses as we', 'we.workshop_id', '=', 'w.id')
            ->select(
                'w.*',
                'we.number_of_participants',
                'we.expense_amount',
                'we.nsic_fees',
                'we.is_tds_applicable',
                'we.tds_percentage',
                'we.tds_amount',
                'we.sanction_order_no',
                'we.sanction_order_date',
                'we.supporting_documents',
                'we.remarks as expense_remarks'
            )
            ->where('w.id', $eventId)
            ->first();

        if ($row) {
            if ($row->schedules) {
                $row->schedules = json_decode($row->schedules, true) ?: [];
            }

            if (!empty($row->event_for) && !is_array($row->event_for)) {
                $row->event_for = json_decode($row->event_for, true) ?: [];
            }

            $row->branch_office = $row->branch_offices_id ?? null;

            // Map executed workshop / expense details to form field names
            $row->no_of_participants = $row->number_of_participants ?? null;
            $row->nsic_fee = $row->nsic_fees ?? null;
            $row->tds_applicable = isset($row->is_tds_applicable) ? ($row->is_tds_applicable ? 'Yes' : 'No') : '';
            $row->sanction_order_number = $row->sanction_order_no ?? null;
            $supportingDoc = $row->supporting_documents ?? null;
            if (is_string($supportingDoc)) {
                $decoded = json_decode($supportingDoc, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $supportingDoc = $decoded;
                }
            }
            $row->supporting_document = $supportingDoc;
            $row->supporting_document_url = null;
            if ($row->supporting_document) {
                $fileUpload = DB::table('file_uploads')->where('id', $row->supporting_document)->first();
                if ($fileUpload) {
                    $row->supporting_document_url = asset('storage/app/' . $fileUpload->file_path . '/' . $fileUpload->file_system_name);
                } else {
                    $row->supporting_document_url = asset('storage/' . $row->supporting_document);
                }
            }
            $row->remarks = $row->expense_remarks ?? null;

            // Calculate net_amount
            $expense = (float) ($row->expense_amount ?? 0);
            $nsic = (float) ($row->nsic_fees ?? 0);
            $tdsPercent = (float) ($row->tds_percentage ?? 0);
            $tdsAmount = ($row->is_tds_applicable) ? ($expense * $tdsPercent / 100) : 0;
            $row->net_amount = $expense + $nsic - $tdsAmount;
        }

        return $row;
    }

    public function getUploadImageData($id)
    {
        $workshop = DB::table('workshops')->where('id', $id)->first();

        if (!$workshop || !$workshop->uploaded_ids) {
            return collect();
        }

        $ids = explode(',', $workshop->uploaded_ids);

        return \DB::table('file_uploads')->whereIn('id', $ids)->get();
    }

    public function cardData()
    {
        $msmeRoleId = DB::table('roles')->where('slug', 'msme')->value('id');
        return DB::table('workshops as w')
            //->leftJoin('roles as r', 'w.event_for', '=', 'r.id')
            ->leftJoin('roles as r', function ($join) {
                $join->whereRaw("JSON_CONTAINS(w.event_for, JSON_QUOTE(r.id))");
            })
            ->leftJoin('states as s', DB::raw('w.state_id COLLATE utf8mb4_unicode_ci'), '=', DB::raw('s.id COLLATE utf8mb4_unicode_ci'))
            ->leftJoin('file_uploads as fu', 'fu.id', '=', 'w.uploaded_ids')
            ->select('w.*', DB::raw('GROUP_CONCAT(r.name) as event_for_name'), 's.name as state_name', 'fu.file_path', 'fu.file_system_name')
            ->whereRaw("JSON_CONTAINS(w.event_for, JSON_QUOTE(?))", [$msmeRoleId])
            ->groupBy('w.id')
            ->orderBy('w.created_at', 'desc')
            ->get()
            ->map(fn($item) => $this->formatWorkshop($item));
    }

    public function formatWorkshop($item)
    {
        $schedule = json_decode($item->schedules, true);

        if (is_string($schedule)) {
            $schedule = json_decode($schedule, true);
        }

        if (!empty($schedule) && is_array($schedule)) {

            $startDates = [];
            $endDates   = [];

            foreach ($schedule as $row) {
                $startDates[] = Carbon::createFromFormat('d-m-Y', $row['start_date']);
                $endDates[]   = Carbon::createFromFormat('d-m-Y', $row['end_date']);
            }

            $start = collect($startDates)->min();
            $end   = collect($endDates)->max();

            $item->start_date = $start->format('d-m-Y');
            $item->end_date   = $end->format('d-m-Y');
            $item->duration   = $start->diffInDays($end) + 1;
        }

        return $item;
    }

    /*public function getScheduleSummary($schedules)
    {
        $schedules = json_decode($schedules, true);

        if (is_string($schedules)) {
            $schedules = json_decode($schedules, true);
        }

        if(empty($schedules)){
            return [
                'start_date'=>null,
                'end_date'=>null,
                'duration'=>0,
                'schedules'=>[]
            ];
        }

        $startDates = collect($schedules)->pluck('start_date');
        $endDates   = collect($schedules)->pluck('end_date');

        $start = Carbon::createFromFormat('d-m-Y', $startDates->min());
        $end   = Carbon::createFromFormat('d-m-Y', $endDates->max());

        return [
            'start_date'=>$start->format('d-m-Y'),
            'end_date'=>$end->format('d-m-Y'),
            'duration'=>$start->diffInDays($end)+1,
            'schedules'=>$schedules
        ];
    }*/

    public function getScheduleSummary($schedules)
    {
        $schedules = is_string($schedules) ? json_decode($schedules, true) : $schedules;

        if (empty($schedules)) {
            return [
                'start_date' => null,
                'end_date'   => null,
                'duration'   => 0,
                'schedules'  => []
            ];
        }

        $startDates = array_column($schedules, 'start_date');
        $endDates   = array_column($schedules, 'end_date');

        $start = Carbon::createFromFormat('d-m-Y', min($startDates));
        $end   = Carbon::createFromFormat('d-m-Y', max($endDates));

        return [
            'start_date' => $start->format('d-m-Y'),
            'end_date'   => $end->format('d-m-Y'),
            'duration'   => $start->diffInDays($end) + 1,
            'schedules'  => $schedules
        ];
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

    public function getBranchOfficeName(?string $codes = null)
    {
        return DB::table('nsic_branch_offices')
            ->when($codes, function ($q) use ($codes) {
                $q->where('code', $codes);
            })
            ->where('status', 1)
            ->pluck('name', 'id')
            ->toArray();
    }


    public function getEventForRoles()
    {
        return DB::table('roles')
            ->where('can_view_workshop', true)
            ->orderBy('name', 'asc')
            ->pluck('name', 'id')
            ->toArray();
    }

    public function getOrganizerNames()
    {
        return DB::table('roles')
            ->where('is_organizer', true)
            ->orderBy('name', 'asc')
            ->pluck('name', 'id')
            ->toArray();
    }

    public function getNsicBranchOffices(?string $key = null)
    {
        $query = DB::table('nsic_branch_offices')
            ->where('status', 1);

        if ($key !== null) {
            return $query->where('id', $key)->value('name');
        }

        return $query->orderBy('name', 'asc')
            ->pluck('name', 'id')
            ->toArray();
    }

    public function getWorkshopDetails(string $id): ?array
    {
        $event = Workshop::find($id);
        if (!$event) {
            return null;
        }

        $roleIds = is_string($event->event_for) ? json_decode($event->event_for, true) : $event->event_for;
        $roleIds = is_array($roleIds) ? $roleIds : [];
        $event_for = DB::table('roles')->whereIn('id', $roleIds)->pluck('name')->implode(', ');

        $uploadedImage = $this->getUploadImageData($id);

        $org_name = DB::table('roles')
            ->where('id', $event->organiser_name)
            ->value('name');

        // Robust decoding of schedules if it's double-encoded or string
        $schedules = $event->schedules;
        if (is_string($schedules)) {
            $schedules = json_decode($schedules, true);
        }
        if (is_string($schedules)) {
            $schedules = json_decode($schedules, true);
        }
        $event->schedules = is_array($schedules) ? $schedules : [];

        return [
            'event' => $event,
            'event_for' => $event_for,
            'uploadedImage' => $uploadedImage,
            'org_name' => $org_name,
        ];
    }

    public function getEcecutedWorkshopDetails(string $id): ?array
    {
        $event = Workshop::find($id);
        if (!$event) {
            return null;
        }

        $roleIds = is_string($event->event_for) ? json_decode($event->event_for, true) : $event->event_for;
        $roleIds = is_array($roleIds) ? $roleIds : [];
        $event_for = DB::table('roles')
            ->whereIn('id', $roleIds)
            ->pluck('name')
            ->implode(', ');

        $uploadedImage = $this->getUploadImageData($id);

        $org_name = DB::table('roles')
            ->where('id', $event->organiser_name)
            ->value('name');

        // Get workshop expense details
       $workshopExpense = DB::table('workshop_expenses')
            ->where('workshop_id', $id)
            ->first();

        // if ($workshopExpense) {

        //     $documentIds = json_decode($workshopExpense->supporting_documents, true);
        //     $documentIds = is_array($documentIds)
        //         ? $documentIds
        //         : [$workshopExpense->supporting_documents];

        //     // Remove extra quotes and spaces
        //     $documentIds = array_map(function ($id) {
        //         return trim($id, "\"' ");
        //     }, $documentIds);
        //     $workshopExpense->supporting_documents = DB::table('file_uploads')
        //         ->whereIn('id', $documentIds)
        //         ->get();

        //     }
          if ($workshopExpense) {
            // ✅ FIX: Check if supporting_documents is non-null and string before decoding
            $documentIds = null;
            if (!empty($workshopExpense->supporting_documents) && is_string($workshopExpense->supporting_documents)) {
                $documentIds = json_decode($workshopExpense->supporting_documents, true);
                // Ensure it's an array after decoding
                if (!is_array($documentIds)) {
                    $documentIds = [$workshopExpense->supporting_documents];
                }
            }
              // If documentIds is not an array or is empty, set it to empty array
            if (!is_array($documentIds)) {
                $documentIds = [];
            }

            // Remove extra quotes and spaces from each ID
            $documentIds = array_map(function ($id) {
                return trim($id, "\"' ");
            }, $documentIds);

            // Fetch file details if any IDs exist
            if (!empty($documentIds)) {
                $workshopExpense->supporting_documents = DB::table('file_uploads')
                    ->whereIn('id', $documentIds)
                    ->get();
            } else {
                $workshopExpense->supporting_documents = collect();
            }
          }
        // Robust decoding of schedules if it's double-encoded or string
        $schedules = $event->schedules;
        if (is_string($schedules)) {
            $schedules = json_decode($schedules, true);
        }
        if (is_string($schedules)) {
            $schedules = json_decode($schedules, true);
        }
        $event->schedules = is_array($schedules) ? $schedules : [];

        return [
            'event'            => $event,
            'event_for'        => $event_for,
            'uploadedImage'    => $uploadedImage,
            'org_name'         => $org_name,
            'workshopExpense'  => $workshopExpense,
        ];
    }

    public function updateStatus(string $id, array $data): bool
    {
        $workshop = Workshop::find($id);
        if ($workshop) {
            $workshop->status = $data['status'];
            $workshop->remark = $data['remark'] ?? null;
            $workshop->status_updated_at = now();

            if ($workshop->status === WorkshopStatus::CANCELLED->value) {
                $workshop->is_executed_workshop = false;
            }

            $saved = $workshop->save();

            if ($saved && $workshop->status === 'Completed') {
                $this->processWorkshopFundDistribution($id);
            }

            return $saved;
        }
        return false;
    }

    /**
     * Process fund distribution for workshop when marked as Completed.
     * Enforces component restrictions, Net Amount calculation, TDS deduction, and balance limits.
     */
    public function processWorkshopFundDistribution(string $workshopId, ?object $dto = null): void
    {
        $poolResolver = app(\App\Web\FundDistribution\ClaimPoolResolver::class);
        $processor    = app(\App\Web\FundDistribution\ClaimDistributionProcessor::class);

        // Fetch workshop record
        $workshop = DB::table('workshops')->where('id', $workshopId)->first();
        if (!$workshop) {
            throw new \Exception("Workshop record not found.");
        }

        // 1. Status Check: Must be 'Completed'
        $status = $workshop->status ?? null;
        if ($status !== 'Completed' && $status !== WorkshopStatus::CONFIRMED->value) {
            return;
        }

        // Fetch workshop expense details
        $expense = DB::table('workshop_expenses')
            ->where('workshop_id', $workshopId)
            ->first();

        $financialYear       = $dto->financialYear ?? $workshop->financial_year;
        $duration            = $dto->duration ?? $workshop->duration;
        $subDuration         = $dto->subDuration ?? $workshop->sub_duration;
        $expenseAmount       = (float) ($dto->expenseAmount ?? ($expense->expense_amount ?? 0));
        $nsicFee             = (float) ($dto->nsic_fees ?? $dto->nsicFee ?? ($expense->nsic_fees ?? 0));
        $tdsApplicable       = isset($dto->tdsApplicable) ? ($dto->tdsApplicable == 1 || $dto->tdsApplicable === 'Yes' ? 1 : 0) : ($expense->is_tds_applicable ?? $expense->tds_applicable ?? 0);
        $tdsPercentage       = (float) ($dto->tdsPercentage ?? ($expense->tds_percentage ?? 0));
        $sanctionOrderNumber = $dto->sanctionOrderNumber ?? ($expense->sanction_order_no ?? null);

        $tdsAmount = ($tdsApplicable == 1 && $tdsPercentage > 0)
            ? round(($expenseAmount * $tdsPercentage) / 100, 2)
            : 0.0;
        $netAmount = round($expenseAmount + $nsicFee - $tdsAmount, 2);

        if ($netAmount <= 0) {
            return;
        }

        // 2. Target Component mappings
        $majorComponentId = 'cfdaa9b0-471b-4509-9b26-ee378280e176'; // "For NSIC"
        $subComponentId   = '79be82d7-b562-4a24-94c9-25f4e00c7a2d'; // "Awareness Generation for MSEs towards the TEAM scheme"

        // 3. Assemble resolver search criteria payload
        $criteria = [
            'financial_year'     => $financialYear,
            'major_component_id' => $majorComponentId,
            'sub_component_id'   => $subComponentId,
            'duration_id'        => $duration,
            'sub_duration_id'    => $subDuration,
        ];

        // 4. Discover applicable candidate fund pools. Marking a workshop Completed must
        // never be blocked by a missing allocation -- same as the Add flow, which already
        // tolerates it -- so an empty result here just means there is nothing to deduct
        // against yet rather than a hard failure.
        $discoveredPools = $poolResolver->resolve($criteria);

        if ($discoveredPools->isEmpty()) {
            return;
        }

        // Verify discovered pools match the required major & sub component
        foreach ($discoveredPools as $pool) {
            if ($pool->major_component_id !== $majorComponentId || $pool->sub_component_id !== $subComponentId) {
                throw new \Exception("Fund Distribution Error: Fund distribution entry allowed only for Major Component 'For NSIC' and Sub Component 'Awareness Generation for MSEs towards the TEAM scheme'.");
            }
        }

        // 5. Balance Check: Net Amount cannot be higher than total available pool remaining balance
        $totalAvailableBalance = (float) $discoveredPools->sum('remaining_balance');
        // Validation removed as per requirements to allow negative balance

        // 6. Check if fund distribution record already exists for this workshop
        $existingDistributions = DB::table('fund_distributions')
            ->where('source_type', 'WORKSHOP')
            ->where('source_id', $workshopId)
            ->whereNull('deleted_at')
            ->get();

        $poolService = app(\App\Web\Allocation\PoolService::class);
        foreach ($existingDistributions as $dist) {
            $distPool = DB::table('fund_pools')->where('id', $dist->fund_pool_id)->first();
            if ($distPool) {
                $newDistributed = (float) $distPool->total_distributed_amount - (float) $dist->distribution_amount;
                DB::table('fund_pools')->where('id', $distPool->id)->update([
                    'total_distributed_amount' => max(0, $newDistributed),
                    'updated_at' => now(),
                ]);
                $poolService->recalculateBalance($distPool->id);
            }
            DB::table('fund_distributions')->where('id', $dist->id)->delete();
        }

        $currentTime = now();
        $userId = auth()->id() ?? null;

        // Formulate static deductive transfer packet & execute transactional deduction
        $transferMetadata = [
            'amount'           => $expenseAmount,
            'tds_percentage'   => round($tdsPercentage, 2),
            'source_type'      => 'WORKSHOP',
            'source_id'        => $workshopId,
            'reference_number' => $sanctionOrderNumber,
            'remarks'          => "Automated Workshop Deduction",
            'user_id'          => $userId,
            'duration_id'      => $duration,
            'sub_duration_id'  => $subDuration,
        ];

        $this->executeWorkshopDeductions($discoveredPools, $transferMetadata);
    }


     public function getWorkshopCategoryName(?string $categoryId): ?string
    {
        if (empty($categoryId)) {
            return null;
        }

        return DB::table('sub_domains')
            ->where('id', $categoryId)
            ->where('status', 1)
            ->value('name');
    }

    /**
     * Executes physical deduction routines across targeted candidate pools for Workshops.
     * Allows balance to go negative if insufficient funds exist.
     */
    protected function executeWorkshopDeductions(\Illuminate\Support\Collection $candidatePools, array $deductionMetadata): array
    {
        $totalRequired = (float) ($deductionMetadata['amount'] ?? 0.0);
        if ($totalRequired <= 0) {
            throw new \Exception("Constraint Error: Workshop distributions cannot possess zero or negative balances.");
        }

        $remainingDeduction = $totalRequired;
        $totalCandidates = $candidatePools->count();
        $processedGuids = [];
        $poolService = app(\App\Web\Allocation\PoolService::class);

        \Illuminate\Support\Facades\Log::info("Processing safe deduction sequence for Workshop.", [
            'source_type' => $deductionMetadata['source_type'],
            'source_id'   => $deductionMetadata['source_id'],
            'initial_amt' => $totalRequired
        ]);

        foreach ($candidatePools->values() as $index => $poolMeta) {
            if ($remainingDeduction <= 0) {
                break;
            }

            $activePool = \Illuminate\Support\Facades\DB::table('fund_pools')
                ->where('id', $poolMeta->id)
                ->lockForUpdate()
                ->first();

            if (!$activePool) {
                \Illuminate\Support\Facades\Log::warning("Candidate pool unexpectedly vaporized during iterative drain. Skipping loop index {$index}.");
                continue;
            }

            $isLastPool = ($index === $totalCandidates - 1);
            $posBuffer = max(0.0, (float) $activePool->remaining_balance);
            
            // If it's the last pool, deduct all remaining amount to allow negative balance
            if ($isLastPool) {
                $deductibleFromThisPool = $remainingDeduction;
            } else {
                if ($posBuffer <= 0.0) {
                    continue;
                }
                $deductibleFromThisPool = min($posBuffer, $remainingDeduction);
            }

            if ($deductibleFromThisPool > 0.001) {
                $generatedDistributionId = $this->writeWorkshopDistributionInstance(
                    $activePool,
                    $deductibleFromThisPool,
                    $deductionMetadata
                );

                $processedGuids[] = $generatedDistributionId;

                $newDist = (float) $activePool->total_distributed_amount + $deductibleFromThisPool;
                \Illuminate\Support\Facades\DB::table('fund_pools')->where('id', $activePool->id)->update([
                    'total_distributed_amount' => $newDist,
                    'updated_at' => now(),
                ]);
                $poolService->recalculateBalance($activePool->id);

                $remainingDeduction -= $deductibleFromThisPool;
            }
        }

        return $processedGuids;
    }

    protected function writeWorkshopDistributionInstance(object $pool, float $allottedAmount, array $metadata): string
    {
        $uuid = (string) \Illuminate\Support\Str::uuid();

        $taxPct = (float) ($metadata['tds_percentage'] ?? 0.0);
        $taxAmt = round(($allottedAmount * $taxPct) / 100, 2);
        $netPayable = $allottedAmount - $taxAmt;

        $insertData = [
            'id'                  => $uuid,
            'financial_year'      => $pool->financial_year,
            'duration_id'         => $metadata['duration_id'] ?? $pool->duration_id,
            'sub_duration_id'     => $metadata['sub_duration_id'] ?? $pool->sub_duration_id,
            'major_component_id'  => $pool->major_component_id,
            'sub_component_id'    => $pool->sub_component_id,
            'fund_pool_id'        => $pool->id,

            'source_type'         => $metadata['source_type'] ?? 'WORKSHOP',
            'source_id'           => $metadata['source_id'] ?? null,

            'distribution_amount' => $allottedAmount,
            'tds_percentage'      => $taxPct,
            'tds_amount'          => $taxAmt,
            'net_payable_amount'  => $netPayable,

            'sanction_order_number' => $metadata['reference_number'] ?? null,
            'sanction_order_date'   => now()->toDateString(),
            'remarks'               => $metadata['remarks'] ?? 'Automated Workshop Deduction',

            'created_by'            => $metadata['user_id'] ?? (function_exists('AuthId') ? AuthId() : null),
            'created_at'            => now(),
            'updated_at'            => now()
        ];

        \Illuminate\Support\Facades\DB::table('fund_distributions')->insert($insertData);

        return $uuid;
    }
}
