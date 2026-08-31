<?php

namespace App\Web\Claim;

use App\Web\Claim\ClaimBulkImportRequest;
use App\Web\Claim\ClaimBulkImportZipRequest;
use App\Web\Claim\ClaimBulkImportDto;
use App\Web\Claim\ClaimBulkImportAction;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Traits\HasFileUpload;
use App\Traits\HasResponses;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use ZipArchive;
use Str;
use App\Http\Api\V1\FileUpload\FileUpload;

class ClaimBulkImportController extends Controller
{
    use HasFileUpload, HasResponses;
	public $pdfUploads = [];
	
    public function import(Request $request)
    {
       /* $validator = Validator::make($request->all(), ClaimBulkImportRequest::rules(), ClaimBulkImportRequest::messages());

        if ($validator->fails()) {
            return $this->error(errors: $validator->errors());
        }

        $file = $this->uploadFile($request->file('file'), config('upload.claim_bulk_import_file_path'));

        $fileData = $file->toArray();
        $fileName = $fileData['file_system_name'];

        $import = new ClaimBulkImportAction($fileName, $validator->validated()['claim_type_id']);
        $excel =  Excel::import($import, $request->file('file'));

        if ($import->getRowCount() > 0) {
            return $this->success(
                message: 'Claims imported successfully.',
                data: [
                    'isImported' => $excel,
                    'fileName' => $fileName
                ]
            );
        }*/

        return $this->uploadZip($request);
    }
	
	
	public function uploadZip($request)
    {
		$authId=AuthId();
		
		$validator = Validator::make($request->all(), ClaimBulkImportZipRequest::rules(), ClaimBulkImportZipRequest::messages());

        if ($validator->fails()) {
            return $this->error(errors: $validator->errors());
        }

        // store uploaded zip
        $file = $request->file('file');
		$zipPath = $file->storeAs("uploads/zips/$authId", $file->getClientOriginalName());
        $directoryPath =storage_path("uploads/zips/$authId");
		$this->recursiveZipPermission($directoryPath);
		
		
        // extract zip
        /*$zip = new ZipArchive;
        $zip->open(storage_path('app/' . $zipPath));
        $extractPath = storage_path('app/uploads/extracted');
        $zip->extractTo($extractPath);
        $zip->close();*/
		
		$zip = new \ZipArchive;
			if ($zip->open(storage_path('app/' . $zipPath)) === true) {
				$extractPath = storage_path("app/uploads/extracted/$authId");

				// Make sure directory exists
				if (!file_exists($extractPath)) {
					mkdir($extractPath, 0777, true);
				}

				// Extract files
				$zip->extractTo($extractPath);
				$zip->close();

				// Recursively set permissions safely
				$iterator = new \RecursiveIteratorIterator(
					new \RecursiveDirectoryIterator($extractPath, \RecursiveDirectoryIterator::SKIP_DOTS),
					\RecursiveIteratorIterator::SELF_FIRST
				);

				foreach ($iterator as $item) {
					$path = $item->getPathname();

					// Only chmod if PHP has rights
					if (is_writable($path)) {
						if ($item->isDir()) {
							@chmod($path, 0777); // folder
						} else {
							@chmod($path, 0777); // file
						}
					}
				}

				// Root folder
				if (is_writable($extractPath)) {
					@chmod($extractPath, 0777);
				}

		}

		
        // process files
        return $this->processExtractedFiles($extractPath,$request,$authId);
    }
	
	
	protected function processExtractedFiles($extractPath,$request,$authId)
    {
		   $files = File::allFiles($extractPath);
		   
		   foreach ($files as $file) {
				$fileName = $file->getFilename();

				// if more than one dot in filename => possible double extension
				if (substr_count($fileName, '.') > 1) {
					return response()->json([
						'message' => "Invalid file $fileName. Double extensions are not allowed.",
					], 422);
				}
			}
		   
		   // Separate PDFs and Excel files
			$pdfFilesValidations = collect($files)->filter(function ($file) {
				return strtolower($file->getExtension()) === 'pdf';
			});

			$excelFilesValidation = collect($files)->filter(function ($file) {
				return in_array(strtolower($file->getExtension()), ['xls']);
			});

			// ✅ Validation checks
			if ($pdfFilesValidations->isEmpty()) {
				return response()->json([
					'message' => 'No PDF files found in the zip.',
				], 422);
			}
			

			if ($excelFilesValidation->isEmpty()) {
				return response()->json([
					'message' => 'No Excel file found in the zip.',
				], 422);
			}
			
			

			if ($excelFilesValidation->count() > 1) {
				return response()->json([
					'message' => 'Multiple Excel files found in the zip. Only one is allowed.',
				], 422);
			}
			
			$excelFile = $excelFilesValidation->first();
			$rows = Excel::toArray(new \stdClass, $excelFile);
			$totalRows = count($rows[0]) - 1;
			
			if($totalRows > 50){
				return response()->json([
					'message' => 'Number of rows is not greater than 50 in excel sheet',
				], 422);
			}

		   
			// Process PDFs (loop only PDFs)
			foreach ($files as $file) {
				if (strtolower($file->getExtension()) === 'pdf') {
					$this->savePdf($file);
				}
			}
			
			//dd($this->pdfUploads);
			
			// Get Excel file (only one in folder)
			$excelFile = collect($files)->first(function ($file) {
				return in_array(strtolower($file->getExtension()), ['xls']);
			});
			
			
			// Import Excel if found
			if ($excelFile) {
				   $import = new ClaimBulkImportAction($this->pdfUploads,$excelFile->getPathname(), $request->claim_type_id);
				   
				
					$excel =  Excel::import($import, $excelFile->getPathname());
                   
					if ($import->getRowCount() > 0) {
						$fileDeleteZipPath ="storage/app/uploads/zips/$authId";
						if (is_dir($fileDeleteZipPath)) {
								foreach (glob($fileDeleteZipPath . '/*.zip') as $zipFile) {
									unlink($zipFile);
								}
							}
							
							$fileDeleteExtractedPath ="storage/app/uploads/extracted/$authId";
							
							if (File::exists($fileDeleteExtractedPath)) {
								foreach (File::directories($fileDeleteExtractedPath) as $dir) {
									File::deleteDirectory($dir);
								}
								foreach (File::files($fileDeleteExtractedPath) as $file) {
									File::delete($file);
								}
							}
							
							
							

	
						/*$fileDeleteExtractedPath ="storage/app/uploads/extracted/$authId/upload";
						if (is_dir($fileDeleteExtractedPath)) {
							$extensions = ['pdf', 'xls'];
							foreach ($extensions as $ext) {
								foreach (glob($fileDeleteExtractedPath . "/*.$ext") ?: [] as $file) {
									if (is_file($file)) {
										@unlink($file);
									}
								}
							}
						}*/
							
						return response()->json([
							'message' => 'Claims imported successfully.',
						]);
					}
			}
			
			

		
		/*$files = File::allFiles($extractPath);
        foreach ($files as $file) {
            $extension = strtolower($file->getExtension());

            if ($extension === 'pdf') {
                $this->savePdf($file); // store in DB + runtime array
            }

            if (in_array($extension, ['xls'])) {
                //Excel::import(new ClaimsImport($this->pdfUploads), $file->getPathname());
            }
        }*/
	
    }
	
	
	protected function savePdf($file)
    {
		
        $extension = $file->getExtension();
        $originalName = $file->getFilename(); // e.g. invoice123.pdf
        $systemName = Str::uuid() . "." . $extension;
        $size = $file->getSize();

        $path = Storage::putFileAs("uploads/claim-documents", $file, $systemName);

        $upload = FileUpload::create([
		    'id' => uuid(),
            'file_name' => $originalName,       // original name for mapping
            'file_path' => $path,
            'file_system_name' => $systemName,
            'file_type' => File::mimeType($file),
            'file_size' => $size,
            'file_extension' => $extension,
			'created_at' => currentDateTime(),
			'created_by' => AuthId()
        ]);

        // store runtime mapping: [ "invoice123.pdf" => 5 ]
        $this->pdfUploads[$originalName] = $upload->id;

        return $upload;
    }
	
	
	public function recursiveZipPermission($path, $permission = 0777)
	{
		if (!file_exists($path)) {
			mkdir($path,$permission, true);
		}

		$dir = new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS);
		$files = new \RecursiveIteratorIterator($dir, \RecursiveIteratorIterator::SELF_FIRST);

		foreach ($files as $file) {
			@chmod($file->getPathname(), $permission);
		}

		// Finally set folder itself
		@chmod($path, $permission);

		return true;
	}
	
	
}
