<?php 

declare(strict_types=1);

namespace App\Traits;

trait NestableModule
{
    public function buildModuleTree(?string $parentId = null)
    {
        $modules = \DB::table('modules')
                        ->select('id', 'name')
                        ->where('parent_id', $parentId)
                        ->where('status',config('constant.ACTIVE'))
                        ->get();
		
        $nestedModules = [];

        foreach ($modules as $module) 
        {
            $moduleData = [
                'id' => $module->id,
                'name' => $module->name,
                'children' => $this->buildModuleTree($module->id),
            ];

            $nestedModules[] = $moduleData;
        }

        return $nestedModules;
    }
}