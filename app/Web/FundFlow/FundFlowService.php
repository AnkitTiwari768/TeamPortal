<?php 

declare(strict_types=1);

namespace App\Web\FundFlow;
use App\Http\Services\ApiService;
use App\Http\Services\CommonService;
use App\Traits\HasFileUpload;
use App\Traits\DataTable;
use DB;
use Carbon\Carbon;

class FundFlowService extends ApiService 
{
    use DataTable,HasFileUpload;

    protected array $columns = [
        1 => 'financial_year',
        2 => 'duration',
        3 => 'duration_limit',
		4 => 'total_amount_allocated'
    ];

    public function getAllocationList()
    {
        [$limit, $order, $dir, $search, $page, $filters] = $this->getDataTableParams();

        $query = DB::table('fund_allocations as al')
                ->leftJoin('fund_allocations_map as map', 'map.allocation_id', '=', 'al.id')
                ->select('al.*')
                ->groupBy('al.id');
		
        /*if ($search) 
        {
            $search = str_replace(',', '', $search);
            $query->where(function($query) use ($search) {
                $query->where('al.financial_year','like', "%$search%")
                    ->orwhere('al.duration','like', "%$search%")
                    ->orWhere('al.duration_limit','like', "%$search%")
					->orWhere('al.total_amount_allocated','like', "%$search%")
					->orWhere('al.sanction_order_no','like', "%$search%")
					//->orWhere('al.sanction_order_date','like', "%$search%")
                    ->orWhereRaw("DATE_FORMAT(al.sanction_order_date, '%d-%m-%Y') like ?", ["$search%"]);
            });
        }*/

        if ($search) {

            $search = trim($search);
            $search = str_replace(',', '', $search);

            $monthMap = [
                'january' => 1,
                'february' => 2,
                'march' => 3,
                'april' => 4,
                'may' => 5,
                'june' => 6,
                'july' => 7,
                'august' => 8,
                'september' => 9,
                'october' => 10,
                'november' => 11,
                'december' => 12,
            ];

            $monthValue = null;

            if (isset($monthMap[strtolower($search)])) {
                $monthValue = $monthMap[strtolower($search)];
            }

            $query->where(function ($query) use ($search, $monthValue) {

                $query->where('al.financial_year', 'like', "%$search%")
                    ->orWhere('al.duration', 'like', "%$search%")
                    ->orWhere('al.duration_limit', 'like', "%$search%")
                    ->orWhere('al.total_amount_allocated', 'like', "%$search%")
                    ->orWhere('al.sanction_order_no', 'like', "%$search%")
                    ->orWhereRaw("DATE_FORMAT(al.sanction_order_date, '%d-%m-%Y') like ?", ["$search%"]);

                // month search support
                if ($monthValue) {
                    $query->orWhere(function ($q) use ($monthValue) {
                        $q->where('al.duration', 'Monthly')
                        ->where('al.duration_limit', $monthValue);
                    });
                }
            });
        }  

        if (!empty($filters['financial_year']) && $filters['financial_year']) { 
            $query->where('al.financial_year', $filters['financial_year']);
        }

        if (!empty($filters['from_dates'])) {
			$from = Carbon::createFromFormat('d-m-Y', $filters['from_dates'])->format('Y-m-d');
		}

		if (!empty($filters['to_dates'])) {
			$to = Carbon::createFromFormat('d-m-Y', $filters['to_dates'])->format('Y-m-d');
		}

		if (!empty($from) && !empty($to)) {
			$query->whereBetween('al.created_at', [$from . ' 00:00:00', $to . ' 23:59:59']);
		} 

        if (!empty($filters['duration'])) {
            $query->where('al.duration', $filters['duration']);
        }

        if (!empty($filters['duration_limit'])) {
            $query->where('al.duration_limit', $filters['duration_limit']);
        }

        if (!empty($filters['major_component_id'])) {
            $query->where('map.major_component_id', $filters['major_component_id']);
        }

        if (!empty($filters['component_id'])) {
            $query->where('map.component_id', $filters['component_id']);
        }

        if (!empty($filters['sub_component_id'])) {
            $query->where('map.sub_component_id', $filters['sub_component_id']);
        }
        $orderColumn = $this->columns[$order] ?? 'al.created_at';
        $dir = $dir ?: 'desc';
        $query->orderBy($orderColumn, $dir); 
        
        if ($page) 
        {
            return $this->getDataTableResult(
                AllocationResource::collection($query->paginate($limit))
            );
        }

        return AllocationResource::collection($query->get());
    }

    public function getAllocationDetail($id)
    {
        $allocation = DB::table('fund_allocations as al')
            ->leftJoin('file_uploads as f1', function($join) {
                $join->on('f1.id', '=', 'al.document_path')
                     ->orOn('f1.file_system_name', '=', 'al.document_path');
            })
            ->select(
                'al.*',
                'f1.file_path as allocationDocument',
                'f1.original_name as upload_document_original_name',
                DB::raw("DATE_FORMAT(al.sanction_order_date, '%d-%m-%Y') as sanction_order_date_format"),
                DB::raw("DATE_FORMAT(al.created_at, '%d-%m-%Y %H:%i:%s') as created_at")
            )
            ->where('al.id', $id)
            ->first();

        if ($allocation) {
            $mapDetails = DB::table('fund_allocations_map as alm')
                ->leftJoin('major-components as mc', 'mc.id', '=', 'alm.major_component_id')
                ->leftJoin('components as c', 'c.id', '=', 'alm.component_id')
                ->leftJoin('sub-components as sc', 'sc.id', '=', 'alm.sub_component_id')
                ->select(
                    'alm.*',
                    'mc.name as major_component_name',
                    'c.name as component_name',
                    'sc.name as sub_component_name'
                )
                ->where('alm.allocation_id', $allocation->id)
                ->get();
    
            $allocation->map_details = $mapDetails;
        }

        return $allocation;
    }

    public function getDetails()
    {
        $commonService = new CommonService();
        
        return [
            'majorcomponents' => $commonService->getMajorComponents(),
            'components' => $commonService->getComponents(),
            'subcomponents' => $commonService->getSubComponents(),
            'status' => $commonService->getStatus()
        ];
    }

    public function deleteDocuments($payload){

        if($payload['document_type']=='upload'){ 
            $path="app/".config('upload.allocation_document_path');  
        }
        $this->deleteFile($path,$payload['id']);
        return true;
    }

    public function storeAllocation(array $payload, $id = null): bool
    {
        //dd($payload);
        return DB::transaction(function() use ($payload, $id) {
            $allocationData = [
                'financial_year'       => $payload['financial_year'],
                'duration'             => $payload['duration'],
                'duration_limit'       => $payload['duration_limit'] ?? null,
                'total_amount_allocated' => $payload['total_amount_allocated'],
                'sanction_order_no'    => $payload['sanction_order_no'],
                // 'sanction_order_date'  => $payload['sanction_order_date'],
                'sanction_order_date'  => date('Y-m-d', strtotime($payload['sanction_order_date'])),
                //'tds'  => $payload['tds'],
                'document_path' =>  $payload['upload_document'] ?? null,
                'remarks'       => $payload['remarks'] ?? null,
            ];

            if (! $id) {
                // New allocation
                $allocationData['id']        = uuid();
                $allocationData['created_at'] = currentDateTime();
                $allocationData['created_by'] = AuthId();

                // Insert allocation
                DB::table('fund_allocations')->insert($allocationData);
                $allocationId = $allocationData['id'];

            } else {
                // Update allocation
                $allocationData['updated_at'] = currentDateTime();
                $allocationData['updated_by'] = AuthId();

                DB::table('fund_allocations')->where('id', $id)->update($allocationData);
                $allocationId = $id;

                // Delete old mappings
                if ($id && !empty($payload['personalities_details'])) {
                    DB::table('fund_allocations_map')->where('allocation_id', $id)->delete();
                }
                //DB::table('fund_allocations_map')->where('allocation_id', $id)->delete();
            }

            // Prepare allocation map data
            $allocatedMapData = [];
            foreach ($payload['personalities_details'] ?? [] as $item) {
                $allocatedMapData[] = [
                    'id'                => uuid(),
                    'allocation_id'     => $allocationId, // 🔑 link with parent
                    'financial_year'    => $payload['financial_year'],
                    'amount'            => $item['amount'],
                    'major_component_id'=> $item['major_component_id'],
                    'component_id'      => $item['component_id'] ?? null,
                    'sub_component_id'  => $item['sub_component_id'] ?? null,
                    //'created_at'        => currentDateTime(),
                    //'created_by'        => AuthId(),
                ];
            }

            // Insert all new mappings
            if (!empty($allocatedMapData)) {
                DB::table('fund_allocations_map')->insert($allocatedMapData);
            }
            return true;
        });
    }

        
}