<?php

namespace App\Web\PmvBulkRegistration;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Traits\SubUserTrait; // ✅ Import Trait

class PMVBulkUploadService
{
    use SubUserTrait; // ✅ Use Trait

    public $successCount = 0;
    public $errorCount = 0;

    private $categories = [];

    public function __construct()
    {
        // ✅ Load categories once
        $this->categories = DB::table('pm_vishwakarma_categories')
            ->pluck('id', 'name')
            ->mapWithKeys(fn($id, $name) => [strtolower(trim($name)) => $id])
            ->toArray();
    }

    /* ================= MAIN FUNCTION ================= */

    public function processExcel($file)
    {
        // ✅ Delete previous errors of same user and sub-users
        $userIds = $this->getUserIdsWithSubUsers(); // ✅ Get all user IDs
        DB::table('pm_vishwakarma_bulk_registration_errors')
            ->whereIn('created_by', $userIds) // ✅ Use whereIn
            ->delete();
            
        // ✅ Pass SAME instance to Import
        $import = new PMVBulkImport($this);

        Excel::import($import, $file);

        return [
            'success' => $this->successCount,
            'failed'  => $this->errorCount,
            'total'   => $this->successCount + $this->errorCount
        ];
    }

    /* ================= ROW PROCESS ================= */

    public function processRow($row, $rowNumber)
    {
        $data = $this->prepareRow($row);
        if ($this->hasMissingFields($data, $rowNumber)) return;

        $errors = PMVRowValidator::validate($data);

        $categoryId = $this->getCategoryId($data['product_category_name']);

        if (!$categoryId) {
            $errors[] = "Product category not match";
        }

        if (!empty($errors)) {
            $this->storeError($data, $rowNumber, implode(', ', $errors), $data['product_category_name']);
            return;
        }
        $finalData = $this->prepareFinalData($data, $categoryId);

        $this->storeSuccess($finalData);
    }

    public function processRowByIndex($row, $rowNumber)
    {
        $data = [
            'owner_name' => $row[0] ?? '',
            'store_name' => $row[1] ?? '',
            'pmv_id' => $row[2] ?? '',
            'mobile' => $row[3] ?? '',
            'email' => $row[4] ?? '',
            'pan' => $row[5] ?? '',
            'pin_code' => $row[6] ?? '',
            'address' => $row[7] ?? '',
            'product_category_name' => $row[8] ?? ''
        ];

        $data = array_map('trim', $data);

        $errors = [];

        /* ================= MISSING FIELD CHECK ================= */
        $requiredFields = [
            'owner_name',
            'store_name',
            'mobile',
            'pin_code',
            'address',
            'product_category_name'
        ];

        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || $data[$field] === '') {
                $errors[] = ucfirst(str_replace('_', ' ', $field)) . " is required";
            }
        }

        /* ================= FIELD VALIDATION ================= */
        $validationErrors = PMVRowValidator::validate($data);

        if (!empty($validationErrors)) {
            $errors[] = $validationErrors[0];
        }

        /* ================= CATEGORY CHECK ================= */
        $categoryId = $this->getCategoryId($data['product_category_name']);

        if (!$categoryId) {
            $errors[] = "Product category not match";
        }

        /* ================= FINAL ERROR STORE ================= */
        if (!empty($errors)) {
            $this->storeError(
                $data,
                $rowNumber,
                implode(', ', $errors),
                $data['product_category_name']
            );
            return;
        }

        /* ================= SUCCESS ================= */
        $finalData = $this->prepareFinalData($data, $categoryId);

        $this->storeSuccess($finalData);
    }   
   
    private function hasMissingFields($data, $rowNumber)
    {
        $missing = [];

        foreach ($data as $key => $value) {
            if ($value === '' || $value === null) {
                $missing[] = $key;
            }
        }

        if (!empty($missing)) {
            $this->storeError($data, $rowNumber, "Missing fields - " . implode(', ', $missing), $data['product_category_name'] ?? null);
            return true;
        }

        return false;
    }

    private function getCategoryId($name)
    {
        return $this->categories[strtolower($name)] ?? null;
    }

    private function prepareFinalData($data, $categoryId)
    {
        return [
            'id' => (string) Str::uuid(),
            'owner_name' => $data['owner_name'],
            'store_name' => $data['store_name'],
            'pmv_id' => $data['pmv_id'],
            'mobile' => $data['mobile'],
            'email' => $data['email'],
            'pan_no' => $data['pan'],
            'pin_code' => $data['pin_code'],
            'address' => $data['address'],
            'type_of_business' => $categoryId,
            'type' => hasRole('snp') ? 1 : 2,
            'is_bulk' => 1,
            'created_by' => AuthId(),
            'created_at' => now(),
            'updated_at' => now(),
            'updated_by' => AuthId(),
        ];
    }

    private function storeError($data, $rowNumber, $message, $categoryName = null)
    {
        DB::table('pm_vishwakarma_bulk_registration_errors')
            ->insert([
                'id' => (string) Str::uuid(),
                'owner_name' => $data['owner_name'] ?? null,
                'store_name' => $data['store_name'] ?? null,
                'pmv_id' => $data['pmv_id'] ?? null,
                'mobile' => $data['mobile'] ?? null,
                'email' => $data['email'] ?? null,
                'pan' => $data['pan'] ?? null,
                'pin_code' => $data['pin_code'] ?? null,
                'address' => $data['address'] ?? null,
                'product_category_id' => $categoryName ?? 'N/A',
                'error_message' => "{$message}",
                'created_at' => now(),
                'updated_at' => now(),
                'created_by' => AuthId(),
                'updated_by' => AuthId(),
            ]);

        $this->errorCount++;
    }

    private function storeSuccess($data)
    {
        DB::table('pm_registrations')
            ->insert([
                ...$data,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

        $this->successCount++;
    }

    /* ================= DATA TABLE LIST ================= */

    public function getPmvDataList($request)
    {
        $columns = [
            'tbl.id',
            'tbl.owner_name',
            'tbl.store_name',
            'tbl.mobile',
            'tbl.email',
            'tbl.type',
            'tbl.is_bulk',
            'cat.name',
            'raw_created_at'
        ];

        // ✅ Get all user IDs including sub-users
        $userIds = $this->getUserIdsWithSubUsers();

        $query = DB::table('pm_registrations as tbl')
            ->leftJoin('pm_vishwakarma_categories as cat', 'tbl.type_of_business', '=', 'cat.id')
            ->select(
                'tbl.id',
                'tbl.owner_name',
                'tbl.store_name',
                'tbl.mobile',
                'tbl.email',
                'tbl.type',
                'tbl.is_bulk',
                'cat.name as category_name',
                'tbl.created_at as raw_created_at',
                DB::raw("DATE_FORMAT(tbl.created_at, '%d-%m-%Y') as created_at")
            )
            ->whereIn('tbl.created_by', $userIds); // ✅ Use whereIn with all user IDs

        /* ================= BULK FILTER ================= */
        if ($request->has('is_bulk') && $request->is_bulk !== '') {
            $query->where('tbl.is_bulk', $request->is_bulk);
        }

        /* ================= DATE FILTERS ================= */
        if (!empty($request->from_date)) {
            try {
                $from = \Carbon\Carbon::createFromFormat('d-m-Y', $request->from_date)->startOfDay();
                $query->where('tbl.created_at', '>=', $from);
            } catch (\Exception $e) {
                // Invalid date format
            }
        }

        if (!empty($request->to_date)) {
            try {
                $to = \Carbon\Carbon::createFromFormat('d-m-Y', $request->to_date)->endOfDay();
                $query->where('tbl.created_at', '<=', $to);
            } catch (\Exception $e) {
                // Invalid date format
            }
        }

        /* ================= TOTAL ================= */
        $totalData = DB::table('pm_registrations')
            ->whereIn('created_by', $userIds) // ✅ Use whereIn
            ->count();

        /* ================= SEARCH ================= */
        if (!empty($request->search['value'])) {
            $search = $request->search['value'];
            $query->where(function ($q) use ($search) {
                $q->where('tbl.owner_name', 'like', "%{$search}%")
                    ->orWhere('tbl.store_name', 'like', "%{$search}%")
                    ->orWhere('tbl.mobile', 'like', "%{$search}%")
                    ->orWhere('tbl.email', 'like', "%{$search}%")
                    ->orWhere('cat.name', 'like', "%{$search}%");
            });
        }

        $totalFiltered = $query->count();

        /* ================= ORDER ================= */
        if (!empty($request->order)) {
            $colIndex = $request->order[0]['column'];
            $colName = $columns[$colIndex] ?? 'raw_created_at';
            $dir = $request->order[0]['dir'];

            if ($colName === 'raw_created_at') {
                $query->orderBy('tbl.created_at', $dir);
            } else {
                $query->orderBy($colName, $dir);
            }
        } else {
            $query->orderBy('tbl.created_at', 'desc');
        }

        /* ================= PAGINATION ================= */
        $start = $request->start ?? 0;
        $length = $request->length ?? 10;

        $data = $query->offset($start)
            ->limit($length)
            ->get();

        return [
            "draw" => intval($request->draw),
            "recordsTotal" => $totalData,
            "recordsFiltered" => $totalFiltered,
            "data" => $data
        ];
    }

    private function prepareRow($row)
    {
        return [
            'owner_name' => $row['owner_name'] ?? '',
            'store_name' => $row['store_name'] ?? '',
            'pmv_id' => $row['pmv_id'] ?? '',
            'mobile' => $row['mobile'] ?? '',
            'email' => $row['email'] ?? '',
            'pan' => $row['pan'] ?? '',
            'pin_code' => $row['pin_code'] ?? '',
            'address' => $row['address'] ?? '',
            'product_category_name' => $row['product_category_name'] ?? ''
        ];
    }
}