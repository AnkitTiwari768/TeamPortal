<?php

declare(strict_types=1);

namespace App\Web\MseBulkRegistration;

use Illuminate\Http\Request;
use App\Models\User;
use App\Exports\UsersExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Web\MseBulkRegistration\MseDownload;
use App\Http\Controllers\ClientController;



class MseDownloadController extends ClientController
{
	
	public function mseBulkExportUdyam(Request $request)
    {
        $bulks=\DB::table('team_msme_scheme_temps')->where('created_by',AuthId())->get();

        return Excel::download(new MseDownload($bulks), 'mse_bulk.xlsx');
    }
	
  
  
}
  
 
	
