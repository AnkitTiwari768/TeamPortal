<?php

declare(strict_types=1);

namespace App\Domain\NetworkProvider;

use App\Traits\DataTable;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class GetNetworkProviderListAction
{
    use DataTable;

    private array $columns = [
        2 => 'np.np_team_id',
        4 => 'np.organization_name',
    ];

    public function execute(?array $statuses = [])
    {
        [$limit, $order, $dir, $search, $page, $filters] = $this->getDataTableParams(columns: $this->columns);
        $search ??= $this->escape_special_characters($search);

        $query = DB::table('network_providers as np')
            ->select(
                'np.id',
                DB::raw(
                    "JSON_UNQUOTE(JSON_EXTRACT(np.authorized_person_details, '$[0].name')) 
                    AS authorized_person_name"
                ),
                'np.role_names',
                'np.np_team_id',
                'np.organization_id',
                'np.organization_name',
                'np.email',
                'np.bppid_providerid',
                'np.contact_details->primary_contact_no as primary_contact_no',
                'np.status',
                'np.created_at'
            );

        if ($statuses) {
            $query->whereIn('np.status', $statuses);
        }

        if (!empty($filters['from_date'])) {
            $from = Carbon::createFromFormat('d-m-Y', $filters['from_date'])->format('Y-m-d');
        }

        if (!empty($filters['to_date'])) {
            $to = Carbon::createFromFormat('d-m-Y', $filters['to_date'])->format('Y-m-d');
        }

        if (!empty($from) && !empty($to)) {
            $query->whereBetween('np.created_at', [$from, $to]);
        } elseif (!empty($from)) {
            $query->whereDate('np.created_at', '>=', $from);
        } elseif (!empty($to)) {
            $query->whereDate('np.created_at', '<=', $to);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {

                $q->where('np.organization_name', 'like', "%{$search}%")
                    ->orWhere('np.organization_id', 'like', "%{$search}%")
                    ->orWhere('np.email', 'like', "%{$search}%")
                    ->orWhere('np.bppid_providerid', 'like', "%{$search}%")
                    ->orWhere('np.status', 'like', "%{$search}%")
                    ->orWhere('np.contact_details->primary_contact_no', 'like', "%{$search}%")
                    ->orWhereRaw(
                        "JSON_UNQUOTE(JSON_EXTRACT(np.authorized_person_details, '$[0].name')) LIKE ?",
                        ["%{$search}%"]
                    )
                    ->orWhereDate('np.created_at', $search);
            });
        }

        if ($order === 'id') {
            $query->orderBy('np.created_at', 'desc');
        } else {
            $query->orderBy($order, $dir);
        }

        if ($page) {
            return $this->getDataTableResult(
                NetworkProviderListResource::collection($query->paginate($limit))
            );
        }

        return NetworkProviderListResource::collection($query->get());
    }
}
