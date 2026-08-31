<?php 
declare(strict_types=1);
namespace App\Pmv;
use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\ClientController; 
use Illuminate\Support\Str;


class PmvController extends ClientController
{
  
    public function index()
    {
		
		if (($handle = fopen(storage_path('app/Vishwakarma_Onboarded_Store_List_Final_23rd_Feb26.csv'), "r")) !== FALSE) {
			fgetcsv($handle);
			
			while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
				\DB::table('pm_registrations')->insert([
				    'id'=>Str::uuid(),
					'store_name' => $data[0],
					'owner_name' => $data[1],
					'mobile' => $data[2],
					'email' => $data[3],
					'address' => $data[4],
					'pin_code' => $data[5],
				]);
			}
			fclose($handle);
		}
        
    }
    

    
}