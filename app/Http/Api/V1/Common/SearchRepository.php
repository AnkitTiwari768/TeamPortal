<?php 

declare(strict_types=1);

namespace App\Http\Api\V1\Common;

use App\Http\Api\V1\Common\Contracts\SearchRepositoryInterface;

use App\Shared\Module;

class SearchRepository implements SearchRepositoryInterface 
{
    public function getSearchList(string $module, ?string $phrase = null)
    {
        return match ($module) {
            
            Module::ROLE => $this->getDropdownList(
                tableName:'roles',
                statusColumn: 'status',
                selectedColumns: ['id', 'name'],
                phrase:$phrase
            ),

            Module::USER => $this->getUserList($phrase),
        };
    }

    protected function getDropdownList(
        string $tableName,
        string $statusColumn,
        array $selectedColumns,
        ?string $phrase,
        ?string $referenceIdValue=NULL,
        ?string $referenceIdColumn=NULL
    )
    {
        $query = \DB::table($tableName)
            ->select($selectedColumns)
            ->where($statusColumn, config('constant.ACTIVE'));

        if($phrase)
        {
            $query->whereRaw("TRIM(name) LIKE ?", ["%".$phrase."%"]);
        }
        if ($referenceIdValue) 
        {
            $query->where($referenceIdColumn, $referenceIdValue);
        }

        return $query->get()->toArray();
    }

    protected function getUserList(string $phrase) 
    {
        return \DB::table('users')
            ->selectRaw('id, TRIM(CONCAT_WS(" ", first_name, middle_name, last_name)) as name')
            ->whereRaw("TRIM(CONCAT_WS(' ', first_name, middle_name, last_name)) LIKE ?", ["%".$phrase."%"])
            ->where('status', config('constant.ACTIVE'))
            ->get()
            ->toArray();
    }
}