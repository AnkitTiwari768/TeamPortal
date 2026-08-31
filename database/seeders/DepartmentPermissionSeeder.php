<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

use DB;

class DepartmentPermissionSeeder extends Seeder 
{
    // public function run(): void 
    // {
    //     $permissions = DB::table('permissions')
    //                     ->select('id')
    //                     ->where('status', true)
    //                     ->whereIn('name', [
    //                         'Dashboard',
    //                         'User Create',
    //                         'User Update',
    //                         'User View',
    //                         'Country Create',
    //                         'Country Update',
    //                         'Country View',
    //                         'State Create',
    //                         'State Update',
    //                         'State View',
    //                         'dashboard view',
    //                         'Signed Authority View',
    //                         'Signed Authority Create',
    //                         'Signed Authority Update',
    //                         'Signed Authority List',
    //                         'Production Category View',
    //                         'Production Category Create',
    //                         'Production Category Update',
    //                         'Audit Create',
    //                         'Audit View',
    //                         'National Application View',
    //                         'National Application Coproduction View',
    //                         'National Application Animation Coproduction View',
    //                         'Animation Post Production Services View',
    //                         'National Application View-Document',
    //                         'National Application Upload-Document',
    //                         'National Application Raise-Query',
    //                         'National Application Coproduction Upload-Document',
    //                         'National Application Coproduction View-Document',
    //                         'National Application Payment-Detail',
    //                         'National Application Assign-Script-Evaluator',
    //                         'National Application Allow-Modification-Permission',
    //                         'National Application Coproduction Raise-Query',
    //                         'National Application Coproduction Payment-Detail',
    //                         'National Application Coproduction Assign-Script-Evaluator',
    //                         'National Application Coproduction Allow-Modification-Permission',
    //                         'National Application Forward-MHA',
    //                         'National Application Coproduction Forward-MHA',
    //                         'National Application Coproduction Forward-Legal',
    //                         'National Application Add-Liason-Officer',
    //                         'National Application Coproduction Add-Liason-Officer'
    //                     ])
    //                     ->get()
    //                     ->toArray();

    //     if ($permissions) 
    //     {
    //         $deparmentId = DB::table('departments')
    //                         ->select('id')
    //                         ->where('slug', 'film-facilitation-office')
    //                         ->first()
    //                         ?->id;

    //         if ($deparmentId) 
    //         {
    //             $departmentPermissions = [];

    //             $permissions = array_column($permissions, 'id');

    //             foreach ($permissions as $permissionId) 
    //             {
    //                 $departmentPermissions[] = [
    //                     'department_id' => $deparmentId,
    //                     'permission_id' => $permissionId
    //                 ];
    //             }
                
    //             if ($departmentPermissions) 
    //             {
    //                 DB::table('department_permissions')
    //                 ->insert($departmentPermissions);
    //             }
    //         }
    //     }
    // }

    public function run(): void 
    {
        $permissions = DB::table('permissions')
                        ->select('id')
                        ->where('status', true)
                        ->whereIn('name', [
                            'Dashboard',
                            'dashboard view',
                            'National Application View',
                            'National Application Coproduction View',
                            'National Application Animation Coproduction View',
                            'Animation Post Production Services View',
                            'National Application View-Document',
                            'National Application Upload-Document',
                            'National Application Coproduction View-Document',
                        ])
                        ->get()
                        ->toArray();

        if ($permissions) 
        {
            $deparmentId = DB::table('departments')
                            ->select('id')
                            ->where('slug', 'ministry-of-information-and-broadcasting')
                            ->first()
                            ?->id;

            if ($deparmentId) 
            {
                $departmentPermissions = [];

                $permissions = array_column($permissions, 'id');

                foreach ($permissions as $permissionId) 
                {
                    $departmentPermissions[] = [
                        'department_id' => $deparmentId,
                        'permission_id' => $permissionId
                    ];
                }
                
                if ($departmentPermissions) 
                {
                    DB::table('department_permissions')
                    ->insert($departmentPermissions);
                }
            }
        }
    }
}