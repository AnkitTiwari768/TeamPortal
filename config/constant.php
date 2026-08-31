<?php
return [
	  'DRAFT' => 1,
	  'ACTIVE' => 1,
	  'INACTIVE' => 0,
	  'YES' => 1,
	  'NO' => 0,
	  'MALE' => 1,
	  'FEMALE' => 2,
	  'OTHER' => 3,
	  
	 'NA' => 'N/A',
	
	'PENDING'=>0,
	'APPROVE'=>1,
	'REJECT'=>2,
	'REVERT'=>3,

	'SUCCESS'=>1,
	'FAILED'=>2,
	'PROCESSING'=>3,
	

	'UDYAM_TOKEN' => 'urteam-bnRlYW1AbTd1MUs=',


	'mysql_datetime_hm' => '%d-%b-%Y %h:%i %p',
	'mysql_datetime_hms' => '%d-%b-%Y %h:%i:%s %p',
	'mysql_date' => '%d-%b-%Y',
	'mysql_time' => '%h:%i:%s',
	
	'mysql_app_date' => '%d-%b-%Y',
	
	'app_datetime_hm' => 'd-M-Y h:i A',
	'app_datetime_hms' => 'd-M-Y h:i:s A',
	'app_date' => 'd-M-Y',
	'app_time' => 'h:i:s',
	
	'FROM' => 'ram.yagya@uneecops.com',
	//'vendor_role_id' => '99eac027-e502-4008-b052-18184b96bd43',
	
	'MINLENGTH'=>1,
	'MAXLENGTH'=>100,
	'MAXLENGTH1'=>25,
	'MAXLENGTH2'=>50,
	'MAXLENGTH3'=>150,
	'MAXLENGTH4'=>200,
	'TEXTAREA_LENGTH'=>250,
	'CODELENGTH'=>6,
	'PINCODE_LENGTH'=>6,
	'PASSWORDLENGTH'=>20,
	'MOBILE_LENGTH'=>10,
	'EMAIL_LENGTH'=>50,
	'LIMIT_LENGTH'=>10,

	// Account constants
	'B2C_RATE'=> 5,
	'B2B_PER_TXN'=> 250,
	'MAX_INCENTIVE'=> 5000,


	// Logistics constants
	'B2C_LOGISTICS_RATE_PER_ORDER' => 50,
	'B2B_LOGISTICS_RATE_PER_ORDER' => 200,
	'LOGISTICS_B2B_MAX_INCENTIVE' => 2000,
	'LOGISTICS_B2C_MAX_INCENTIVE' => 500,
	
	
	'upload_base_path' => 'storage/app/',
	'user_image_file_path' => 'uploads/photos',
	
	'FOREIGN_LIVE' =>'c93894f3-0c4d-11ef-8177-00155d022d06',
	'FOREIGN_LIVE_ANIMATION' =>'3d8679f8-0c4e-11ef-8177-00155d022d06',
	'COPRODUCTION' =>'e781cbc1-0c4d-11ef-8177-00155d022d06',
	'COPRODUCTION_ANIMATION' =>'5c502cd0-0c4e-11ef-8177-00155d022d06',

	'EXCHANGE_RATE_API_URL' =>'https://api.currencyapi.com/v3/latest?apikey=',
	'EXCHANGE_RATE_API_KEY' =>'cur_live_xVOdT4jHF8tePvtRqymfgxmacpMFKy1CqgIBFSss',
];