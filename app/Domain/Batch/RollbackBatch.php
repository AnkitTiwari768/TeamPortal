<?php

declare(strict_types=1);

namespace App\Domain\Batch;

use App\Traits\Respond;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class RollbackBatch
{
    use Respond;

    public function index()
    {
        $title = 'Batch Rollback';
        return view('batch.rollback.index', compact('title'));
    }

    public function getBatches()
    {
        $batches = DB::table('dy_batches')
            ->select('id', 'batch_number')
            ->whereNotNull('batch_number')
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->success($batches, 'Batches retrieved successfully.');
    }

    public function rollback(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'batch_id' => 'required|uuid',
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors(), 'Validation failed');
        }

        $batchId = $request->input('batch_id');

        $createdBy = DB::table('dy_batches')->where('id', $batchId)->value('created_by')
            ?: DB::table('dy_batch_claims')
            ->join('claims', 'claims.id', '=', 'dy_batch_claims.claim_id')
            ->where('dy_batch_claims.batch_id', $batchId)
            ->value('claims.created_by')
            ?: 'ceb923b2-0bf3-11f1-922a-00155d022d06';

        try {
            DB::beginTransaction();

            // 1. Delete from dy_workflow_logs where instance_id in (select id from dy_workflow_instances where entity_id in (select claim_id from dy_batch_claims where batch_id = ...))
            DB::table('dy_workflow_logs')
                ->whereIn('instance_id', function ($query) use ($batchId) {
                    $query->select('id')
                        ->from('dy_workflow_instances')
                        ->whereIn('entity_id', function ($subQuery) use ($batchId) {
                            $subQuery->select('claim_id')
                                ->from('dy_batch_claims')
                                ->where('batch_id', $batchId);
                        });
                })->delete();

            // 2. Delete from dy_workflow_logs where instance_id in (select id from dy_workflow_instances where entity_id = ...)
            DB::table('dy_workflow_logs')
                ->whereIn('instance_id', function ($query) use ($batchId) {
                    $query->select('id')
                        ->from('dy_workflow_instances')
                        ->where('entity_id', $batchId);
                })->delete();

            // 3. Delete from dy_workflow_instances where entity_id in (select claim_id from dy_batch_claims where batch_id = ...)
            DB::table('dy_workflow_instances')
                ->whereIn('entity_id', function ($query) use ($batchId) {
                    $query->select('claim_id')
                        ->from('dy_batch_claims')
                        ->where('batch_id', $batchId);
                })->delete();

            // 4. Delete from dy_workflow_instances where entity_id = ...
            DB::table('dy_workflow_instances')
                ->where('entity_id', $batchId)
                ->delete();

            // 5. Delete from dy_batch_claims where batch_id = ...
            DB::table('dy_batch_claims')
                ->where('batch_id', $batchId)
                ->delete();

            // 6. Delete from dy_batches where id = ...
            DB::table('dy_batches')
                ->where('id', $batchId)
                ->delete();

            // 7. Update claims set claim_status = 0 where created_by = ...
            DB::table('claims')
                ->where('created_by', $createdBy)
                ->update(['claim_status' => 0]);

            DB::commit();

            return $this->success(
                data: [
                    'batch_id'   => $batchId,
                    'created_by' => $createdBy,
                ],
                message: 'Batch rollback completed successfully.'
            );
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->handleException($e);
        }
    }

    public function rollbackWithClaims(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'batch_id' => 'required|uuid',
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors(), 'Validation failed');
        }

        $batchId = $request->input('batch_id');

        $createdBy = DB::table('dy_batches')->where('id', $batchId)->value('created_by')
            ?: DB::table('dy_batch_claims')
            ->join('claims', 'claims.id', '=', 'dy_batch_claims.claim_id')
            ->where('dy_batch_claims.batch_id', $batchId)
            ->value('claims.created_by')
            ?: 'ceb923b2-0bf3-11f1-922a-00155d022d06';

        // Retrieve claims associated with the batch before they are deleted from dy_batch_claims
        $claimIds = DB::table('dy_batch_claims')
            ->where('batch_id', $batchId)
            ->pluck('claim_id')
            ->toArray();

        try {
            DB::beginTransaction();

            // 1. Delete from dy_workflow_logs where instance_id in (select id from dy_workflow_instances where entity_id in (select claim_id from dy_batch_claims where batch_id = ...))
            DB::table('dy_workflow_logs')
                ->whereIn('instance_id', function ($query) use ($batchId) {
                    $query->select('id')
                        ->from('dy_workflow_instances')
                        ->whereIn('entity_id', function ($subQuery) use ($batchId) {
                            $subQuery->select('claim_id')
                                ->from('dy_batch_claims')
                                ->where('batch_id', $batchId);
                        });
                })->delete();

            // 2. Delete from dy_workflow_logs where instance_id in (select id from dy_workflow_instances where entity_id = ...)
            DB::table('dy_workflow_logs')
                ->whereIn('instance_id', function ($query) use ($batchId) {
                    $query->select('id')
                        ->from('dy_workflow_instances')
                        ->where('entity_id', $batchId);
                })->delete();

            // 3. Delete from dy_workflow_instances where entity_id in (select claim_id from dy_batch_claims where batch_id = ...)
            DB::table('dy_workflow_instances')
                ->whereIn('entity_id', function ($query) use ($batchId) {
                    $query->select('claim_id')
                        ->from('dy_batch_claims')
                        ->where('batch_id', $batchId);
                })->delete();

            // 4. Delete from dy_workflow_instances where entity_id = ...
            DB::table('dy_workflow_instances')
                ->where('entity_id', $batchId)
                ->delete();

            // Delete from dy_declarations where entity_id = ...
            DB::table('dy_declarations')
                ->where('entity_id', $batchId)
                ->delete();

            // Delete from batch_claim_workflow where batch_id = ...
            DB::table('batch_claim_workflow')
                ->where('batch_id', $batchId)
                ->delete();

            // Delete from batch_timeline_details where batch_id = ...
            DB::table('batch_timeline_details')
                ->where('batch_id', $batchId)
                ->delete();

            // Delete from batch_timelines where batch_id = ...
            DB::table('batch_timelines')
                ->where('batch_id', $batchId)
                ->delete();

            // 5. Delete from dy_batch_claims where batch_id = ...
            DB::table('dy_batch_claims')
                ->where('batch_id', $batchId)
                ->delete();

            // 6. Delete from dy_batches where id = ...
            DB::table('dy_batches')
                ->where('id', $batchId)
                ->delete();

            // 7. Update claims set claim_status = 0 where id in ($claimIds)
            if (!empty($claimIds)) {
                DB::table('claims')
                    ->whereIn('id', $claimIds)
                    ->update(['claim_status' => 0]);
            }

            DB::commit();

            return $this->success(
                data: [
                    'batch_id'   => $batchId,
                    'created_by' => $createdBy,
                ],
                message: 'Batch and claims rollback completed successfully.'
            );
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->handleException($e);
        }
    }

    public function removeBatchAndClaimsPermanently(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'batch_id' => 'required|uuid',
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors(), 'Validation failed');
        }

        $batchId = $request->input('batch_id');

        // Retrieve claims associated with the batch before they are deleted
        $claimIds = DB::table('dy_batch_claims')
            ->where('batch_id', $batchId)
            ->pluck('claim_id')
            ->toArray();

        try {
            DB::beginTransaction();

            // 1. Delete from dy_workflow_logs where instance_id in (select id from dy_workflow_instances where entity_id in ($claimIds))
            if (!empty($claimIds)) {
                DB::table('dy_workflow_logs')
                    ->whereIn('instance_id', function ($query) use ($claimIds) {
                        $query->select('id')
                            ->from('dy_workflow_instances')
                            ->whereIn('entity_id', $claimIds);
                    })->delete();
            }

            // 2. Delete from dy_workflow_logs where instance_id in (select id from dy_workflow_instances where entity_id = $batchId)
            DB::table('dy_workflow_logs')
                ->whereIn('instance_id', function ($query) use ($batchId) {
                    $query->select('id')
                        ->from('dy_workflow_instances')
                        ->where('entity_id', $batchId);
                })->delete();

            // 3. Delete from dy_workflow_instances where entity_id in ($claimIds)
            if (!empty($claimIds)) {
                DB::table('dy_workflow_instances')
                    ->whereIn('entity_id', $claimIds)
                    ->delete();
            }

            // 4. Delete from dy_workflow_instances where entity_id = $batchId
            DB::table('dy_workflow_instances')
                ->where('entity_id', $batchId)
                ->delete();

            // Delete from dy_declarations where entity_id = $batchId
            DB::table('dy_declarations')
                ->where('entity_id', $batchId)
                ->delete();

            // Delete from batch_claim_workflow where batch_id = $batchId
            DB::table('batch_claim_workflow')
                ->where('batch_id', $batchId)
                ->delete();

            // Delete from batch_timeline_details where batch_id = $batchId
            DB::table('batch_timeline_details')
                ->where('batch_id', $batchId)
                ->delete();

            // Delete from batch_timelines where batch_id = $batchId
            DB::table('batch_timelines')
                ->where('batch_id', $batchId)
                ->delete();

            // Delete from dy_batch_claims where batch_id = $batchId
            DB::table('dy_batch_claims')
                ->where('batch_id', $batchId)
                ->delete();

            // Delete from dy_batches where id = $batchId
            DB::table('dy_batches')
                ->where('id', $batchId)
                ->delete();

            if (!empty($claimIds)) {
                // Delete from claim_orders where claim_id in ($claimIds)
                DB::table('claim_orders')
                    ->whereIn('claim_id', $claimIds)
                    ->delete();

                // Delete from claim_documents where claim_id in ($claimIds)
                DB::table('claim_documents')
                    ->whereIn('claim_id', $claimIds)
                    ->delete();

                // Delete from claims where id in ($claimIds)
                DB::table('claims')
                    ->whereIn('id', $claimIds)
                    ->delete();
            }

            DB::commit();

            return $this->success(
                data: [
                    'batch_id' => $batchId,
                ],
                message: 'Batch and all associated claims removed permanently from the system.'
            );
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->handleException($e);
        }
    }
}
