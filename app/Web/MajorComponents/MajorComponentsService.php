<?php 

declare(strict_types=1);

namespace App\Web\MajorComponents;
use App\Traits\DataTable;

class MajorComponentsService
{

	use DataTable;
    protected array $columns = [
        1 => 'name',
        2 => 'status',
    ];

	public function storeMajorComponent(array $payload, ?string $countryId= null): bool
    {
        $countryData = [
            'name' => $payload['name'],
			'slug' => slugify($payload['name']),
            'status' => $payload['status'],
        ];

        if (! $countryId)
        {
            $countryData['created_at'] = currentDateTime();
            $countryData['created_by'] = AuthId();
            $countryData['updated_at'] = currentDateTime();
            $countryData['updated_by'] = AuthId();

            return (bool) MajorComponents::create($countryData);
        }

        $countryData['updated_at'] = currentDateTime();
        $countryData['updated_by'] = AuthId();

       $country = MajorComponents::findOrfail($countryId);
        
       return (bool) $country->fill($countryData)->save();
    }

    public function getMajorComponents()
    {
        [$limit, $order, $dir, $search, $page, $filters] = $this->getDataTableParams();
        
        $search ??= $this->escape_special_characters($search);

        $query = MajorComponents::select('name', 'slug','status','id');

        if ($search) 
        {
            $query->where(function($query) use ($search) {
                $query->where('name','like', "%$search%")
			        ->orWhereRaw($this->datatable_status("status"). "LIKE '$search%'");    
            });
        }

        $query->orderBy($order, $dir); 
        
        if ($page) 
        {
            return $this->getDataTableResult(
                MajorComponentsResource::collection($query->paginate($limit))
            );
        }

        return MajorComponentsResource::collection($query->get());
    }

   
	public function getMajorComponent(string $countryId)
    {
        return MajorComponents::select('name', 'status')->where('id',$countryId)->first();
    }

}