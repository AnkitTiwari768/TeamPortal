<?php
declare(strict_types=1);
namespace App\Web\MisReport;
use App\Traits\DataTable;
use App\Core\BaseService;
use App\Http\Services\CommonService;
use App\Web\Claim\ClaimResource;
use App\Web\User\UserService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use App\Domain\NetworkProvider\NetworkProviderStatus;
use App\Traits\HasAttribute;
use DB;
use Carbon\Carbon;


class NpService extends BaseService
{
	use DataTable, HasAttribute;
	private array $columns = [
        2 => 'np.np_team_id',
        4 => 'np.organization_name',
    ];

    public function execute()
    {
        [$limit, $order, $dir, $search, $page, $filters] = $this->getDataTableParams(columns: $this->columns);
		//dd($statuses);
        $search ??= $this->escape_special_characters($search);

        $query = DB::table('network_providers as np')
            ->leftJoin('roles as r', function ($join) {
                $join->whereRaw("JSON_CONTAINS(np.roles, JSON_QUOTE(r.id))");
            })
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
            $query->groupBy('np.id');
        // if ($statuses) {
        //     $query->whereIn('np.status', $statuses);
        // }

        if (!empty($filters['from_dates'])) {
            $from = Carbon::createFromFormat('d-m-Y', $filters['from_dates'])->format('Y-m-d');
        }

        if (!empty($filters['to_dates'])) {
            $to = Carbon::createFromFormat('d-m-Y', $filters['to_dates'])->format('Y-m-d');
        }

        if (!empty($from) && !empty($to)) {
            $query->whereBetween('np.created_at', [$from, $to]);
        } 
        /*elseif (!empty($from)) {
            $query->whereDate('np.created_at', '>=', $from);
        } elseif (!empty($to)) {
            $query->whereDate('np.created_at', '<=', $to);
        }*/

		if (!empty($filters['roles'])) {

			$roles = $filters['roles'];

			if (is_array($roles)) {
				$query->where(function ($q) use ($roles) {
					foreach ($roles as $role) {
						$q->orWhereJsonContains('np.roles', $role);
					}
				});
			} else {
				$query->whereJsonContains('np.roles', $roles);
			}
		}
		if (isset($filters['review_status']) && $filters['review_status'] !== '') {
			$statuses = $filters['review_status'];
			$query->where('np.status', $statuses);	
		}



        if ($search) {
            $query->where(function ($q) use ($search) {

                $q->where('np.organization_name', 'like', "%{$search}%")
                    ->orWhere('np.organization_id', 'like', "%{$search}%")
                    ->orWhere('np.email', 'like', "%{$search}%")
                    ->orWhere('np.bppid_providerid', 'like', "%{$search}%")
                    ->orWhere('np.status', 'like', "%{$search}%")
                    ->orWhere('np.np_team_id', 'like', "%{$search}%")
                    ->orWhere('np.contact_details->primary_contact_no', 'like', "%{$search}%")
                    ->orWhere('r.name', 'like', "%{$search}%")
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