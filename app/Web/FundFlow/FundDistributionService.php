<?php 

declare(strict_types=1);

namespace App\Web\FundFlow;
use App\Http\Services\ApiService;
use App\Http\Services\CommonService;
use App\Traits\HasFileUpload;
use App\Traits\DataTable;
use DB;
use Carbon\Carbon;

class FundDistributionService extends ApiService 
{
    use DataTable,HasFileUpload;

    protected array $columns = [
        1 => 'financial_year',
        2 => 'duration',
        3 => 'duration_limit',
		4 => 'amount_allocated',
		5 => 'majorComponent',
		6 => 'component',
		7 => 'subComponent',
		8 => 'sanction_order_no',
		9 => 'sanction_order_date',
        10 => 'payable_amount'
    ];

    /*public function getDistributionList()
    {
        [$limit, $order, $dir, $search, $page, $filters] = $this->getDataTableParams();

        $query = DB::table('fund_distribution as fd')
                ->leftJoin('major-components as mc', 'mc.id', '=', 'fd.major_component_id')
                ->leftJoin('components as c', 'c.id', '=', 'fd.component_id')
                ->leftJoin('sub-components as sc', 'sc.id', '=', 'fd.sub_component_id')
                ->select('fd.*','mc.name as majorComponent','c.name as component','sc.name as subComponent',
                DB::raw('(fd.amount_allocated - (fd.amount_allocated * fd.tds / 100)) as payable_amount'));

        if (!empty($filters['financial_year']) && $filters['financial_year']) { 
            $query->where('fd.financial_year', $filters['financial_year']);
        }
		
        if ($search) 
        {
            $search = str_replace(',', '', $search);
            $query->where(function($query) use ($search) {
                $query->where('fd.financial_year','like', "%$search%")
                    ->orwhere('fd.duration','like', "%$search%")
                    ->orWhere('fd.duration_limit','like', "%$search%")
                    ->orWhere('fd.amount_allocated','like', "%$search%")
                    ->orWhere('mc.name','like', "%$search%")
                    ->orWhere('c.name','like', "%$search%")
					->orWhere('sc.name','like', "%$search%")
					->orWhere('fd.sanction_order_no','like', "%$search%")
					->orWhere('fd.sanction_order_date','like', "%$search%")
                    ->orWhereRaw('(fd.amount_allocated - (fd.amount_allocated * fd.tds / 100)) like ?', ["%$search%"]);
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
       
        

            if ($order == 'payable_amount') {
                $query->orderByRaw('(fd.amount_allocated - (fd.amount_allocated * fd.tds / 100)) ' . ($dir ?: 'desc'));
            } else {
                $orderColumn = $this->columns[$order] ?? 'fd.created_at';
                $query->orderBy($orderColumn, $dir ?: 'desc');
            }

        //$query->orderBy($order, $dir); 
        
        if ($page) 
        {
            return $this->getDataTableResult(
                AllocationResource::collection($query->paginate($limit))
            );
        }

        return AllocationResource::collection($query->get());
    }*/

    public function getDistributionList()
    {
        [$limit, $order, $dir, $search, $page, $filters] = $this->getDataTableParams();

        $query = DB::table('fund_distribution as fd')
            ->leftJoin('major-components as mc', 'mc.id', '=', 'fd.major_component_id')
            ->leftJoin('components as c', 'c.id', '=', 'fd.component_id')
            ->leftJoin('sub-components as sc', 'sc.id', '=', 'fd.sub_component_id')
            ->select(
                'fd.*',
                'mc.name as majorComponent',
                'c.name as component',
                'sc.name as subComponent',
                DB::raw('(fd.amount_allocated - (fd.amount_allocated * fd.tds / 100)) as payable_amount')
            );

        if (!empty($filters['financial_year'])) {
            $query->where('fd.financial_year', $filters['financial_year']);
        }

        $from = !empty($filters['from_dates']) 
            ? Carbon::createFromFormat('d-m-Y', $filters['from_dates'])->format('Y-m-d') 
            : null;

        $to = !empty($filters['to_dates']) 
            ? Carbon::createFromFormat('d-m-Y', $filters['to_dates'])->format('Y-m-d') 
            : null;

        if ($from && $to) {
            $query->whereBetween('fd.created_at', [
                $from . ' 00:00:00',
                $to . ' 23:59:59'
            ]);
        }

        if (!empty($filters['duration'])) {
            $query->where('fd.duration', $filters['duration']);
        }

        if (!empty($filters['duration_limit'])) {
            $query->where('fd.duration_limit', $filters['duration_limit']);
        }

        if (!empty($filters['major_component_id'])) {
            $query->where('fd.major_component_id', $filters['major_component_id']);
        }

        if (!empty($filters['component_id'])) {
            $query->where('fd.component_id', $filters['component_id']);
        }

        if (!empty($filters['sub_component_id'])) {
            $query->where('fd.sub_component_id', $filters['sub_component_id']);
        }

        /*if (!empty($search)) {
            $search = str_replace(',', '', $search);

            $query->where(function ($q) use ($search) {
                $q->where('fd.financial_year', 'like', "%$search%")
                ->orWhere('fd.duration', 'like', "%$search%")
                ->orWhere('fd.duration_limit', 'like', "%$search%")
                ->orWhere('fd.amount_allocated', 'like', "%$search%")
                ->orWhere('fd.sanction_order_no', 'like', "%$search%")
                ->orWhere('fd.sanction_order_date', 'like', "%$search%")
                ->orWhere('mc.name', 'like', "%$search%")
                ->orWhere('c.name', 'like', "%$search%")
                ->orWhere('sc.name', 'like', "%$search%")
                ->orWhereRaw('(fd.amount_allocated - (fd.amount_allocated * fd.tds / 100)) like ?', ["%$search%"]);
            });
        }*/
        
        if (!empty($search)) {

            $search = trim($search);
            $search = str_replace(',', '', $search);

            $monthMap = [
                'january'   => 1,
                'february'  => 2,
                'march'     => 3,
                'april'     => 4,
                'may'       => 5,
                'june'      => 6,
                'july'      => 7,
                'august'    => 8,
                'september' => 9,
                'october'   => 10,
                'november'  => 11,
                'december'  => 12,
            ];

            $monthValue = $monthMap[strtolower($search)] ?? null;

            $query->where(function ($q) use ($search, $monthValue) {

                $q->where('fd.financial_year', 'like', "%$search%")
                    ->orWhere('fd.duration', 'like', "%$search%")
                    ->orWhere('fd.duration_limit', 'like', "%$search%")
                    ->orWhere('fd.amount_allocated', 'like', "%$search%")
                    ->orWhere('fd.sanction_order_no', 'like', "%$search%")
                    ->orWhere('mc.name', 'like', "%$search%")
                    ->orWhere('c.name', 'like', "%$search%")
                    ->orWhere('sc.name', 'like', "%$search%")
                    ->orWhereRaw('(fd.amount_allocated - (fd.amount_allocated * fd.tds / 100)) like ?', ["%$search%"])
                    ->orWhereRaw("DATE_FORMAT(fd.sanction_order_date, '%d-%m-%Y') like ?", ["$search%"]);
                if ($monthValue) {
                    $q->orWhere(function ($sub) use ($monthValue) {
                        $sub->where('fd.duration', 'Monthly')
                            ->where('fd.duration_limit', $monthValue);
                    });
                }
            });
        }    

        if ($order == 'payable_amount') {
            $query->orderByRaw('(fd.amount_allocated - (fd.amount_allocated * fd.tds / 100)) ' . ($dir ?: 'desc'));
        } else {
            $orderColumn = $this->columns[$order] ?? 'fd.created_at';
            $query->orderBy($orderColumn, $dir ?: 'desc');
        }

        if ($page) {
            return $this->getDataTableResult(
                AllocationResource::collection($query->paginate($limit))
            );
        }

        return AllocationResource::collection($query->get());
    }    

    public function getDistributionDetail($id)
    {
        $distribution = DB::table('fund_distribution as al')
            ->leftJoin('file_uploads as f1', 'f1.file_system_name', '=', 'al.upload_document')
            ->leftJoin('major-components as mc', 'mc.id', '=', 'al.major_component_id')
            ->leftJoin('components as c', 'c.id', '=', 'al.component_id')
            ->leftJoin('sub-components as sc', 'sc.id', '=', 'al.sub_component_id')
            ->select(
                'al.*',
                'f1.file_path as allocationDocument',
                'mc.name as majorComponent','c.name as component','sc.name as subComponent',
                DB::raw("DATE_FORMAT(al.sanction_order_date, '%d-%m-%Y') as sanction_order_date_format"),
                DB::raw("DATE_FORMAT(al.created_at, '%d-%m-%Y %H:%i:%s') as created_at")
            )
            ->where('al.id', $id)
            ->first();
        return $distribution;
    }

    public function storeDistribution(array $payload, $id = null): bool
    {
        return DB::transaction(function() use ($payload, $id) {

            $amount = (float) $payload['amount_allocated'];
            $tds = (float) ($payload['tds'] ?? 0);

            $tdsAmount = ($amount * $tds) / 100;
            $netPayable = $amount - $tdsAmount;

            $allocationData = [
                'financial_year'       => $payload['financial_year'],
                'duration'             => $payload['duration'],
                'duration_limit'       => $payload['duration_limit'] ?? null,
                'major_component_id'   => $payload['major_component_id'],
                'component_id'         => $payload['component_id'] ?? null,
                'sub_component_id'     => $payload['sub_component_id'] ?? null,
                'amount_allocated'     => $payload['amount_allocated'],
                'tds'                  => $payload['tds'],
                'sanction_order_no'    => $payload['sanction_order_no'],
                'sanction_order_date'  => date('Y-m-d', strtotime($payload['sanction_order_date'])),
                'upload_document' =>  $payload['upload_document'] ?? null,
                'upload_document_original_name' => isset($payload['upload_document']) 
                        ? self::originalName($payload['upload_document']) : null,
                'remarks'       => $payload['remarks'] ?? null,
            ];

            if (! $id) {
                // New allocation
                $allocationData['id']        = uuid();
                $allocationData['created_at'] = currentDateTime();
                $allocationData['created_by'] = AuthId();

                // Insert allocation
                DB::table('fund_distribution')->insert($allocationData);
                $allocationId = $allocationData['id'];

            } else {
                // Update allocation
                $allocationData['updated_at'] = currentDateTime();
                $allocationData['updated_by'] = AuthId();

                DB::table('fund_distribution')->where('id', $id)->update($allocationData);
                $allocationId = $id;

            }
            return true;
        });
    }

        
}