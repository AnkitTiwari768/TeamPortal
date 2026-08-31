<?php

declare(strict_types=1);

namespace App\Web\MseBulkRegistration;

use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\ClientController;
use Maatwebsite\Excel\Facades\Excel;
use App\Web\MseBulkRegistration\MseBulkImportUdyam;
use DB;


class MseBulkRegistrationUdyamController extends ClientController
{
	
	
  public function importBulkUdyam(): View
  {
    return view('msme.import_udyam')->with('title', 'MSE Bulk Registration');
  }
  
 
	
	public function msme_bulk_import_udyam(Request $request)
	{
		
		$request->validate([
			'file' => 'required|mimes:xlsm,xlsx,xls|mimetypes:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
		]);

		try {
			Excel::import(new MseBulkImportUdyam, $request->file('file'));
		} 
		catch (ValidationException $e) {

			$errorRows = [];

			foreach ($e->failures() as $failure) {
				$errorRows[] = [
					'row'       => $failure->row(),          // Excel row number
					'column'    => $failure->attribute(),    // Column name
					'errors'    => $failure->errors(),       // Error messages
					'values'    => $failure->values(),       // Row values
				];
			}

			return response()->json([
				'status' => 'error',
				'errors' => $errors
			], 422);
		}
		
		return response()->json(['message' => 'Mse bulk registration successfully.']);

		//return back()->with('success', 'Data imported successfully.');
	}
}
