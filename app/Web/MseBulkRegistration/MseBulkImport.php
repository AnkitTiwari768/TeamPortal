<?php

namespace App\Web\MseBulkRegistration;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Facades\DB;

class MseBulkImport implements ToCollection, WithHeadingRow, WithValidation
{
    private $emails = [];
    private $mobiles = [];
    private $udyams = [];

    public function collection(Collection $rows)
    {
	
		//dd($rows);
        DB::transaction(function () use ($rows) {

            foreach ($rows as $payload) {
				
				/*if (empty($payload['email']) || empty($payload['mobile'])) {
					continue;
				}*/
				
                $uuid = uuid();
                $userId = uuid();

                $msmeData = [
                    'id'                        => $uuid,
                    'user_id'                   => $userId,
                    'team_id'                   =>  getNextTeamId() ?: null,
                    'udyam_no'                  => $payload['udyam_no'],
                    'mobile'                    => $payload['mobile'],
                    'email'                     => $payload['email'],
                    'enterprise_name'           => $payload['enterprise_name'],
                    'entrepreneur_name'         => $payload['entrepreneur_name'],
                    'state_id'                  => $this->getStateId('states', slugify($payload['state_id'])),
                    'ondc_transaction_type_id'  => $this->getOndcTransactionTypeId('attribute_values', slugify($payload['ondc_transaction_type_id'])),
                    'product_category_id'       => $this->getSubdomainTypes('sub_domains', $payload['product_category_id']),
                    'product_details'           => $payload['product_details'],
					'select_snp' =>1,
                    'is_msme_registration'      => 1,
                    'status'                    => 0,
                    'created_at'                => currentDateTime(),
                    'updated_at'                => currentDateTime(),
                ];

                DB::table('team_msme_schemes')->insert($msmeData);

                $userMapping = [
                    'id'            => $userId,
                    'username'      => $payload['email'],
                    'mobile'        => $payload['mobile'],
                    'email'         => $payload['email'],
                    'status'        => 1,
                    'is_msme'       => 1,
                    'created_at'    => currentDateTime(),
                ];


                DB::table('users')->insert($userMapping);
				
				
				$snp_mapping=[
					'id'=>uuid(),
					'snp_id'=>$this->getSnpId(),
					'msme_id'=>$uuid,
					'created_at'=>currentDateTime()
				];
				
				DB::table('team_snpmsme_mapping')->insert($snp_mapping);
			
				
            }
        });
    }
	
	


    // VALIDATION RULES
    public function rules(): array
    {
        return [

            '*.email' => [
                'required',
                'email',
                'unique:users,email', // DB unique check
                function ($attribute, $value, $fail) {
                    if (in_array($value, $this->emails)) {
                        $fail("Duplicate email inside Excel file: $value");
                    }
                    $this->emails[] = $value;
                }
            ],

            '*.mobile' => [
                'required',
                'digits:10',
                'unique:users,mobile',
                function ($attribute, $value, $fail) {
                    if (in_array($value, $this->mobiles)) {
                        $fail("Duplicate mobile inside Excel file: $value");
                    }
                    $this->mobiles[] = $value;
                }
            ],

            /*'*.udyam_no' => [
                'required',
                'unique:team_msme_schemes,udyam_no',
                function ($attribute, $value, $fail) {
                    if (in_array($value, $this->udyams)) {
                        $fail("Duplicate Udyam no inside Excel file: $value");
                    }
                    $this->udyams[] = $value;
                }
            ],*/
			
			'*.udyam_no' => [
				'nullable',
				'unique:team_msme_schemes,udyam_no',
				function ($attribute, $value, $fail) {
					// If value is null → skip validation
					if (empty($value)) {
						return;
					}

					// Check duplicate inside Excel
					if (in_array($value, $this->udyams)) {
						$fail("Duplicate Udyam No inside Excel file: $value");
					}

					// Push to array after checking
					$this->udyams[] = $value;
				}
			],


        ];
    }
	
	

    public function customValidationMessages()
    {
        return [
            '*.email.required' => 'Email is required.',
            '*.email.email'    => 'Invalid email format.',
            '*.email.unique'   => 'This email already exists in database.',

            '*.mobile.required' => 'Mobile number is required.',
            '*.mobile.unique'   => 'This mobile already exists.',
            '*.mobile.digits'   => 'Mobile must be 10 digits.',

            '*.udyam_no.nullable' => 'Udyam No is optional.',
            '*.udyam_no.unique'   => 'This Udyam number already exists.',
        ];
    }


    // -------- Helper functions (already in your system) -------
    public function getStateId($table, $slug)
    {
        return DB::table($table)->where('slug', $slug)->value('id');
    }


    public function getOndcTransactionTypeId($table, $slug)
    {
        return DB::table($table)->where('code', $slug)->value('id');
    }
	
	
	public function getSnpId()
	{
		return DB::table("team_snp_scheme")->where('user_id', AuthId())->value('id');
	}
	
	
	public function getSubdomainTypes($table,$code){
		$sub = explode(",", $code);

		// Apply slugify to each value
		$slugify = array_map(function($item) {
			return slugify($item);
		}, $sub);

		// Fetch IDs
		$ids = DB::table($table)
			->whereIn('code', $slugify)
			->pluck('id');

		return json_encode($ids);
	}
	
	
	

}
