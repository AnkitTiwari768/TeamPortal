<?php

declare(strict_types=1);

namespace App\Web\MseBulkRegistration;

use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\ClientController;
use Maatwebsite\Excel\Facades\Excel;
use App\Web\MseBulkRegistration\MseBulkImport;
use DB;


class MseBulkRegistrationController extends ClientController
{
	
  public function index(): View
  {
    return view('msme.import')->with('title', 'MSE Bulk Registration');
  }
  
  
  /*public function msme_bulk_import(Request $request)
	{
		$request->validate([
			'file' => 'required|mimes:xlsx,xls'
		]);

		Excel::import(new MseBulkImport, $request->file('file'));

		return back()->with('success', 'Excel data imported successfully!');
	}*/
	
	
	public function msme_bulk_import(Request $request)
	{
		$request->validate([
			'file' => 'required|mimes:xlsx,csv,xls'
		]);

		try {
			Excel::import(new MseBulkImport, $request->file('file'));
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
