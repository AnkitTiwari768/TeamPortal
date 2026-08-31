<?php

namespace App\Domain\UbpIntegration;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Http\Api\V1\Registration\RegistrationService;
use Illuminate\Support\Facades\Crypt;
use Carbon\Carbon;
use App\Models\User;
use App\Domain\UbpIntegration\UbpUdyamService;
use App\Domain\EmailTemplate\EmailTemplateService;
use App\Web\Notification\SendNotificationEvent;
use App\Web\Msme\MsmeResource;
use App\Traits\HasAttribute;
use App\Traits\DataTable;
use App\Core\BaseService;

class UbpService extends BaseService
{
    use DataTable, HasAttribute;

	protected $udyamService;

    public function __construct(UbpUdyamService $udyamService)
    {
        $this->udyamService = $udyamService;
    }
    /*public function generateToken(string $clientId, string $clientSecret): ?array
    {
        if ($clientId !== env('UBP_CLIENT_ID') || $clientSecret !== env('UBP_CLIENT_SECRET')) {
            return null;
        }

        $token = Str::random(60);
        return [
            'access_token' => $token,
            'expires_in' => 3600
        ];
    }*/

	public function generateToken(string $clientId, string $clientSecret): array
	{
		try {

			$errors = [];

			if ($clientId !== env('UBP_CLIENT_ID')) {
				$errors[] = 'Invalid client_id';
			}

			if ($clientSecret !== env('UBP_CLIENT_SECRET')) {
				$errors[] = 'Invalid client_secret';
			}

			if (!empty($errors)) {

				return [
					'status'  => false,
					'type'    => 'authentication_error',
					'message' => implode(', ', $errors)
				];
			}

			$token = Str::random(60);

			return [
				'status'  => true,
				'type'    => 'success',
				'message' => 'Access token generated successfully',
				'data'    => [
					'access_token' => $token,
					'token_type'   => 'Bearer',
					'expires_in'   => 3600
				]
			];

		} catch (\Exception $e) {

			return [
				'status'  => false,
				'type'    => 'exception',
				'message' => 'Something went wrong while generating token'
			];
		}
	}	


    public function getMasterData(): array
    {
        return [
            'states' => DB::table('states')->select('id', 'name')->get(),
            'districts' => DB::table('districts')->select('id', 'name', 'state_id')->get(),
            'sub_domains' => DB::table('sub_domains')->select('id', 'name')->get(),
            'attributes' => DB::table('attributes')->select('id', 'name')->get(),
        ];
    }
    public function getSnpList(): array
    {
        return DB::table('team_snp_scheme')
            ->where('status', 1)
            ->select('id', 'organization_name', 'short_description', 'state_id', 'transaction_type', 'sub_domain')
            ->get()
            ->toArray();
    }

    public function registerMsme(array $data): array
    {
        $registrationService = new RegistrationService();

        $registrationService->store($data);

        $tokenService = new UbpTokenService();
        $ssoToken = $tokenService->generateUserToken([
            'udyam_no' => $data['udyam_no'] ?? null,
            'mobile' => $data['mobile'],
            //'email' => $data['email']
        ]);

        return [
            'status' => true,
            'sso_login_url' => route('ubp.login', ['token' => $ssoToken])
        ];
    }


    public function store(array $payload, ?string $roleId = null)
	{
		$udyamData = $this->udyamService->fetchDetails($payload['udyam_no'], $payload['mobile']);

		return DB::transaction(function () use ($payload, $udyamData) {
			
			$userId = uuid();
			$user_mapping = [
				'id' => $userId,
				'username' => $payload['email'],
				'first_name' => $payload['entrepreneur_name'],
				'mobile' => $payload['mobile'],
				'email' => $payload['email'],
				'status' => ($payload['select_snp'] == 1) ? 1 : 0,
				'is_msme' => 1,
				'is_user_sso' => $payload['is_user_sso'] ?? null,
				'sso_type' => $payload['sso_type'] ?? null,
				'created_at' => currentDateTime()
			];
			// dd($msmeData,$user_mapping);
			DB::table('users')->insert($user_mapping);
			
			$uuid = uuid();
			$msmeData = [
				'id' => $uuid,
				'user_id' => $userId,
				//'team_id' => 'TEAM' . rand(10000, 99999),
				'team_id' =>  getNextTeamId() ?: null,
				'entrepreneur_name' => $payload['entrepreneur_name'],
				'enterprise_name' => $payload['enterprise_name'],
				'udyam_no' => $payload['udyam_no'] ?? null,
				'mobile' => $payload['mobile'],
				'email' => $payload['email'],
				'product_category_id' => json_encode($payload['product_category_id']),
				'product_details' => $payload['product_details'] ?? null,
				'major_activity' => $payload['major_activity'],
				'msme_classification' => $payload['msme_classification'],
				'turnover' => $payload['turnover'],
				'pan_no' => $payload['pan_no'],
				//'state_id' => $payload['state_id'],
				'state_id' => $this->getStateId('states', $udyamData['BasicDetail']['LG_ST_Code']),
				'district_id' => $this->getStateId('locations', $udyamData['BasicDetail']['LG_DT_Code']),
				'enterprise_details' => json_encode($udyamData['BasicDetail']['EnterpriseDetail']),
				'activity_details' => json_encode($udyamData['ActivityDetail']),
				'ondc_transaction_type_id' => $payload['ondc_transaction_type_id'],
				'select_snp' => $payload['select_snp'],
				'status' => 0,
				'agree' => 1,
				'is_user_sso' => $payload['is_user_sso'] ?? null,
				'sso_type' => $payload['sso_type'] ?? null,
				'is_msme_registration' => 1,
				'created_at' => currentDateTime(),
				'updated_at' => currentDateTime()
			];

			DB::table('team_msme_schemes')->insert($msmeData); 

			if ($payload['select_snp'] == 1) {
				$snp_mapping = [
					'id' => uuid(),
					'snp_id' => $payload['snp_id'],
					'msme_id' => $uuid,
					'created_at' => currentDateTime()
				];

				DB::table('team_snpmsme_mapping')->insert($snp_mapping);


				/*$snpName = DB::table('team_snp_scheme')->where('id', $payload['snp_id'])->value('snp_name');
			
				$organizationName = DB::table('team_snp_scheme')->where('user_id', $payload['user_id'] ?? null)->value('organization_name');

				app(EmailTemplateService::class)->send(
					templateKey: 'snp-selection',
					toEmail: $msmeData['email'],
					data: [
						'snp_name' => $organizationName ? $organizationName : $snpName,
						'helpdesk_number' => config('settings.helpdesk_number'),
						'email' =>  config('settings.helpdesk_email'),
						'year' => (string) date('Y')
					]
				);*/
			}

			return $payload['select_snp'];
		});
	}

	public function getStateId($table, $state_code)
	{
		return DB::table($table)->where('code', $state_code)->first()->id;
	}



	public function getUbpUserList(array $selectedIds = [])
    {
        $columns = [
            2 => 'ms.team_id',
            3 => 'ms.udyam_no',
            4 => 'ms.mobile',
            5 => 'ms.email',
            6 => 's.name',
            7 => 'ms.enterprise_name',
            8 => 'av.attribute_value',
            9 => 'ms.created_at',
        ];
       
        [$limit, $order, $dir, $search, $page, $filters] = $this->getDataTableParams();

        $nationalId = '5bc85de0-0292-11f1-922a-00155d022d06';
        $startDate = $filters['from_dates'] ?? null;
        $endDate = $filters['to_dates'] ?? null;
        $search ??= $this->escape_special_characters($search);

        $query = DB::table('team_msme_schemes as ms')
            ->select('ms.id', 'ms.team_id', 'ms.udyam_no', 'ms.mobile', 'ms.email', 'ms.entrepreneur_name', 'ms.enterprise_name', 'ms.organisation_type', 'ms.msme_classification', 'ms.social_category', 'ms.created_at', 's.name as state_name', 'ia.organization_name as association_name', 'creator_snp.organization_name as creator_snp_name','av.attribute_value as transaction_type','ms.is_user_sso')
            ->leftjoin('states as s', 's.id', '=', 'ms.state_id')
            ->leftjoin('industrial_associations as ia', 'ia.user_id', '=', 'ms.created_by')
            ->leftjoin('team_snp_scheme as creator_snp', 'creator_snp.user_id', '=', 'ms.created_by')
            ->leftJoin('attribute_values as av', 'av.id', '=', 'ms.ondc_transaction_type_id')
            // ->where('ms.select_snp', 0)
			->where('ms.is_user_sso',1)
            ->whereNull('ms.bpp_id')
            ->whereNotNull('ms.major_activity')->where('ms.major_activity', '!=', '');

        if (!empty($filters['from_dates'])) {
           	 	$from = Carbon::createFromFormat('d-m-Y', $filters['from_dates'])->format('Y-m-d');
		}

		if (!empty($filters['to_dates'])) {
			$to = Carbon::createFromFormat('d-m-Y', $filters['to_dates'])->format('Y-m-d');
		}

		if (!empty($from) && !empty($to)) {
			$query->whereBetween('ms.created_at', [$from . ' 00:00:00',$to . ' 23:59:59']);
		} elseif (!empty($from)) {
			$query->whereDate('ms.created_at', '>=', $from);
		} elseif (!empty($to)) {
			$query->whereDate('ms.created_at', '<=', $to);
		}

        if (isset($filters['ondc_transaction_type_id']) && !empty($filters['ondc_transaction_type_id'])) {
            $query->where('ondc_transaction_type_id', $filters['ondc_transaction_type_id']);
        }

        if (!empty($filters['product_category_id']) && is_array($filters['product_category_id'])) {
            foreach ($filters['product_category_id'] as $categoryId) {
                $query->orWhereJsonContains('product_category_id', $categoryId);
            }
        }

        if ($search) {
            $query->where(function ($query) use ($search) {
                $query->where('ms.team_id', 'like', "%$search%")
                    ->orwhere('ms.udyam_no', 'like', "%$search%")
                    ->orWhere('ms.mobile', 'like', "%$search%")
                    ->orWhere('ms.email', 'like', "%$search%")
                    ->orWhere('ms.entrepreneur_name', 'like', "%$search%")
                    ->orWhere('ms.enterprise_name', 'like', "%$search%")
                    ->orWhere('ms.organisation_type', 'like', "%$search%")
                    ->orWhere('ms.created_at', 'like', "%$search%")
                    ->orWhere('ia.organization_name', 'like', "%$search%")
                    ->orWhere('creator_snp.organization_name', 'like', "%$search%");

                if (str_contains(strtolower('Self'), strtolower($search))) {
                    $query->orWhere(function ($q) {
                        $q->whereNull('ia.organization_name')->whereNull('creator_snp.organization_name');
                    });
                }
            });
        }
        if (!empty($selectedIds)) {
            $query->whereIn('ms.id', $selectedIds);
        }

        if (empty($selectedIds)) {
            if ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('ms.team_id', 'like', "%$search%")
                        ->orWhere('ms.udyam_no', 'like', "%$search%")
                        ->orWhere('ms.mobile', 'like', "%$search%")
                        ->orWhere('ms.email', 'like', "%$search%")
                        ->orWhere('ms.entrepreneur_name', 'like', "%$search%")
                        ->orWhere('ms.enterprise_name', 'like', "%$search%")
                        ->orWhere('ms.organisation_type', 'like', "%$search%")
                        ->orWhere('ms.created_at', 'like', "%$search%")
                        ->orWhere('ia.organization_name', 'like', "%$search%")
                        ->orWhere('creator_snp.organization_name', 'like', "%$search%");

                    if (str_contains(strtolower('Self'), strtolower($search))) {
                        $query->orWhere(function ($q) {
                            $q->whereNull('ia.organization_name')->whereNull('creator_snp.organization_name');
                        });
                    }
                });
            }
        }

        if ($order == 'id') {
            $query->orderBy('ms.created_at', $dir);
        }

        if ($page) {
            return $this->getDataTableResult(
                MsmeResource::collection($query->paginate($limit))
            );
        }
        return $query->get();
    }

}
