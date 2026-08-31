<?php

namespace App\Web\FlowManagement;

use Illuminate\Support\Facades\DB;

class FlowHelperService
{
    public function getRoles($key = null): array
    {
        $queries = DB::table('roles')
            ->select('id', 'name')
            ->where('status', config('constant.ACTIVE'))
            ->get();

        return $this->buildSelectList($queries, $key);
    }

    public function getUsers($key = null): array
    {
        $queries = DB::table('users')
            ->select('id', 'full_name as name')
            ->where('status', config('constant.ACTIVE'))
            ->get();

        return $this->buildSelectList($queries, $key);
    }

    public function getWorkflowTypes($key = null): ?array
    {
        return DB::table('workflow_types')
            ->select('id', 'name')
            ->where('status', config('constant.ACTIVE'))
            ->pluck('name', 'id')
            ->toArray();
    }

    /**
     * Build key-value list with optional single value access.
     */
    private function buildSelectList($queries, $key = null): array
    {
        $list = [];

        if (!$key) {
            $list[''] = 'Select';
        }

        if ($queries) {
            foreach ($queries as $data) {
                $list[$data->id] = $data->name;
            }
        }

        return ($key && isset($list[$key])) ? $list[$key] : $list;
    }
}
