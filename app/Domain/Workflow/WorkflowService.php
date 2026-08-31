<?php

declare(strict_types=1);

namespace App\Domain\Workflow;

use Illuminate\Support\Facades\DB;

class WorkflowService
{
    /**
     * Execute a workflow transition.
     */
    public function transition(
        string $workflowTypeId,
        string $entityType,
        string $entityId,
        string $action,
        string $userRole,
        bool $dump = false
    ) {

        $userRole = $this->getWorkflowRoleName($userRole);

        // current workflow instance
        $instance = DB::table('dy_workflow_instances')
            ->where('workflow_type_id', $workflowTypeId)
            ->where('entity_type', $entityType)
            ->where('entity_id', $entityId)
            ->first();



        // dd($instance, $workflowTypeId, $entityType, $entityId);

        if (!$instance) {
            dd($instance, $workflowTypeId, $entityType, $entityId);
            throw new \Exception("Workflow instance not found");
        }

        // fetch valid transition
        $transition = DB::table('dy_workflow_transitions as wt')
            ->where('workflow_type_id', $workflowTypeId)
            ->where('from_state_id', $instance->current_state_id)
            ->where('action', $action)
            ->whereJsonContains('allowed_roles', $userRole)
            ->first();

        if ($dump) {
            dd($transition);
        }
        // dd($workflowTypeId, $instance->current_state_id, $transition, $action, $userRole);

        if (!$transition) {
            dd($workflowTypeId, $instance->current_state_id, $action, $userRole);
            // dd($instance,$workflowTypeId, $instance, $action, $userRole , $transition);
            throw new \Exception("Unauthorized transition");
        }

        // update state
        DB::table('dy_workflow_instances')
            ->where('workflow_type_id', $workflowTypeId)
            ->where('id', $instance->id)
            ->update([
                'current_state_id' => $transition->to_state_id,
                'updated_at' => now()
            ]);

        // log
        DB::table('dy_workflow_logs')->insert([
            'id'            => DB::raw('UUID()'),
            'instance_id'   => $instance->id,
            'from_state_id' => $instance->current_state_id,
            'to_state_id'   => $transition->to_state_id,
            'action'        => $action,
            'performed_by'  => authId(),
            'comments'      => request('comments'),
            'created_at'    => now(),
        ]);

        return true;
    }


    public function autoTransition(
        string $workflowTypeId,
        string $entityType,
        string $entityId,
        string $userRole
    ) {
        $instance = $this->getWorkflowInstance(
            workflowTypeId: $workflowTypeId,
            entityType: $entityType,
            entityId: $entityId
        );


        $authRoleName = authRoleName();
        $authRoleName = $this->getWorkflowRoleName($authRoleName);
        $userRole = $this->getWorkflowRoleName($userRole);

        $transition = DB::table('dy_workflow_transitions')
            ->whereJsonContains('allowed_roles', $authRoleName)
            ->where('workflow_type_id', $workflowTypeId)
            ->where('from_state_id', $instance->current_state_id)
            ->first();

        if (! $transition) {
            return;
        }

        if ($transition->auto_execute) {
            $this->transition(
                workflowTypeId: $workflowTypeId,
                entityType: $entityType,
                entityId: $entityId,
                action: $transition->action,
                userRole: $userRole
            );
        }
    }

    public function getWorkflowInstance(
        string $entityType,
        string $entityId,
        ?string $workflowTypeId = null
    ) {
        $instance = DB::table('dy_workflow_instances');

        if ($workflowTypeId) {
            $instance->where('workflow_type_id', $workflowTypeId);
        }

        return $instance->where('entity_type', $entityType)
            ->where('entity_id', $entityId)
            ->first();
    }

    public function getWorkflowStateById(string $id)
    {
        return DB::table('dy_workflow_states')->where('id', $id)->first();
    }

    public function getWorkflowTypeIdBySlug(string $slug)
    {
        return DB::table('workflow_types')->where('slug', $slug)->value('id');
    }


    public function getWorkflowRoleName($role)
    {
        return match ($role) {
            'Seller Network Participant (SNP)' => 'SNP',
            'Buyer Network Participant (BNP)' => 'BNP',
            'Logistics Service Provider (LSP)' => 'LSP',
            'ONDC Admin' => 'ONDC Admin',
            'NSIC' => 'NSIC',
            'NSIC Maker' => 'NSIC Maker',
            'NSIC Checker' => 'NSIC Checker',
            'NSIC Finance' => 'NSIC Finance',
            'nsic-maker' => 'NSIC Maker',
            'nsic-checker' => 'NSIC Checker',
            'CA' => 'CA',
            'SNP' => 'SNP',
            'BNP' => 'BNP',
            'LSP' => 'LSP',
            'snp' => 'SNP',
            'bnp' => 'BNP',
            'lsp' => 'LSP',
            'nsic' => 'NSIC',
            'nsic-finance' => 'NSIC Finance',
            'ondc-admin' => 'ONDC Admin',
            'ca' => 'CA'
        };
    }

    public function getWorkflowTypeId(string $slug): ?string
    {
        return DB::table('workflow_types')
            ->where('slug', $slug)
            ->value('id');
    }

    public function getVisibleStateIds(string $role, ?string $tab, string $workflowTypeId): array
    {
        $query = DB::table('dy_workflow_states')
            ->where('workflow_type_id', $workflowTypeId);

        if ($tab) {
            $query->whereJsonContains('shown_roles', ['role' => $role, 'tab' => $tab]);
        } else {
            $query->whereJsonContains('shown_roles', ['role' => $role]);
        }

        return $query->pluck('id')
            ->unique()
            ->toArray();
    }
}
