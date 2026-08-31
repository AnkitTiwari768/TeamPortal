<?php

declare(strict_types=1);

namespace App\Web\ClaimForm;

use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\ClientController;
use App\Http\Api\V1\FileUpload\FileUpload;
use App\Web\Claim\CatalogueBulkZipRequest;
use App\Web\SNP\SNPMSMEService;
use App\Core\BaseRequest;
use App\Traits\HasFileUpload;
use App\Domain\Batch\BatchStatus;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use ZipArchive;
use Str;
use DB;




class CatalogueController extends ClientController
{
	use HasFileUpload;
	public $pdfUploads = [];
	private static string $module = 'ready-for-catalogue-creation.index';

	public function __construct(private SNPMSMEService $service, private CatalogueService $catalogusService) {}


	public function getNewClaimTypes()
	{
		return DB::table('claim_types')
			->where('slug', '!=', 'claim-for-catalogue-creation')
			->pluck('name', 'id')
			->toArray();
	}

	public function getClaimTypes($claimSlug)
	{
		return DB::table('claim_types')
			->where('slug', $claimSlug)
			->pluck('name', 'id')
			->toArray();
	}

	public function getClaimTypeId($claimSlug)
	{
		return DB::table('claim_types')
			->where('slug', $claimSlug)
			->value('id');
	}

	public function catalogueReadyMSME(): View
	{
		return view('catalogue.catalogue_ready')
			->with('title', 'Ready For Catalogue Creation')
			->with('lists', (object) $this->service->getDropdownList());
	}

	public function catalogueReadyMSMEList()
	{
		return $this->success($this->catalogusService->catalogueReadyMSMEList($is_uploaded = NULL));
	}

	public function restrictedMsme(): View
	{
		return view('catalogue.restricted_msme')
			->with('title', 'MSE Not Eligible')
			->with('is_restricted', 1)
			->with('lists', (object) $this->service->getDropdownList());
	}

	public function catalogueRestrictedMSMEList()
	{
		return $this->success($this->catalogusService->catalogueRestrictedMSMEList($is_uploaded = 1));
	}

	public function catalogueCreatedMSME(): View
	{
		$claimSlug = 'claim-for-catalogue-creation';
		$claim_types = $this->getClaimTypes($claimSlug);
		$claimTypeIdValue = $this->getClaimTypeId($claimSlug);

		return view('catalogue.catalogue_created', compact('claimSlug', 'claim_types', 'claimTypeIdValue'))
			->with('title', 'Catalogue Created List');
	}

	public function accountsCreatedMSME(): View
	{
		$claimSlug = 'claim-for-accounts-management';
		$claim_types = $this->getClaimTypes($claimSlug);
		$claimTypeIdValue = $this->getClaimTypeId($claimSlug);

		return view('catalogue.accounts_created', compact('claimSlug', 'claim_types', 'claimTypeIdValue'))
			->with('title', 'Account Created MSME List');
	}

	public function catalogueCreatedMSMEList()
	{
		// return $this->success($this->catalogusService->catalogueReadyMSMEList($is_uploaded = 1));
		return $this->success($this->service->getmyOnboardedMSMEList($select_snp = null, $is_msmeregistration = 2, $status = 1));
	}

	public function accountsCreatedMSMEList()
	{
		// return $this->success($this->catalogusService->catalogueReadyMSMEList($is_uploaded = 1));
		return $this->success($this->service->accountsCreatedMSMEList($select_snp = null, $is_msmeregistration = 2, $status = 1));
	}
	public function packagingCreatedMSMEList()
	{
		// return $this->success($this->catalogusService->catalogueReadyMSMEList($is_uploaded = 1));
		return $this->success($this->service->packagingClaimApprovedMSMEList($select_snp = null, $is_msmeregistration = 2, $status = 1));
	}

	public function msmeBonusDetails(string $id)
	{

		$data =  DB::table('team_msme_schemes as tms')
			->select(
				'tms.id as msme_id',
				'tms.team_id',
				'tms.udyam_no',
				'claims.id as claim_id',
				'claims.claim_type_id',
				'claims.amount as claim_amount',
				'claims.claim_status',
				'claims.bpp_id',
				'claims.amount',
				DB::raw("DATE_FORMAT(claims.created_at, '%d-%m-%Y') as created_date")

			)
			->join('claims', 'claims.team_registration_id', '=', 'tms.team_id')
			->where('tms.id', $id)
			->where('claims.claim_status', BatchStatus::PAYMENT_COMPLETED->value)
			->orderBy('claims.created_at', 'desc')
			->get();
		//dd($data);
		if (!$data) {
			return response()->json([
				'success' => false,
				'message' => 'MSME details not found'
			], 404);
		}

		return response()->json([
			'success' => true,
			'data'    => $data
		], 200);
	}

	public function accountManagmentClaimApprovedMSME(): View
	{
		$module_url = 'claim-for-account-managment';
		$claim_types = $this->getNewClaimTypes();
		$documents = DB::table('document_categories')->select('*')->where('slug', 'bonus_query_document')->get()->toArray();
		$claimSlug = 'claim-for-accounts-management';
		$claim_types = $this->getClaimTypes($claimSlug);
		$claimTypeId = $this->getClaimTypeId($claimSlug);
		//dd($claim_types,$claimTypeIdValue);

		return view('catalogue.account-management-claim-approved', compact('claim_types', 'module_url', 'documents', 'claimSlug', 'claim_types', 'claimTypeId'))
			->with('title', 'Claim for acccount and managment');
	}

	public function packagingClaimApprovedMSME(): View
	{
		$module_url = 'packaging-support';
		$claim_types = $this->getNewClaimTypes();
		return view('catalogue.packaging-claim-approved', compact('claim_types', 'module_url'))
			->with('title', 'Claim for packaging support');
	}

	public function transportAndLogisticApprovedMSME(): View
	{
		$module_url = 'claim-transport-and-logistic';
		$slug = "claim-for-transportation-and-logistic";
		$claim_types = $this->getClaimTypes($slug);

		return view('catalogue.transport-logistic-approved', compact('claim_types', 'module_url'))
			->with('title', 'Claim for Transportation and Logistic');
	}

	public function CatalogueClaimApprovedMSMEList()
	{
		return $this->success($this->catalogusService->catalogueReadyMSMEList($is_uploaded = 2));
	}

	public function uploadCatalogue(Request $request)
	{
		return $this->uploadFileWithValidation(
			$request,
			'file',
			config('upload.catalogue_path'),
			BaseRequest::getPdfRules(),
			BaseRequest::getPdfRuleMessages()
		);
	}

	public function deleteCatalogue(Request $request)
	{
		return $this->catalogusService->deleteCatalogue($request->all());
	}

	public function catalogueUpdate(Request $request)
	{

		$validator = Validator::make($request->all(), [
			'catalogue' => 'required|string'
		]);

		if ($validator->fails()) {
			return response()->json(['errors' => $validator->errors()], 422);
		}

		return $this->catalogusService->updateCatalogue($request->all());
	}


	public function catalogueBulkUpload(Request $request)
	{
		return $this->uploadZip($request);
	}


	public function uploadZip($request)
	{
		$authId = AuthId();

		$validator = Validator::make($request->all(), CatalogueBulkZipRequest::rules(), CatalogueBulkZipRequest::messages());

		if ($validator->fails()) {
			return $this->error(errors: $validator->errors());
		}

		// store uploaded zip
		$file = $request->file('file');
		$zipPath = $file->storeAs("uploads/zips/$authId", $file->getClientOriginalName());
		$directoryPath = storage_path("uploads/zips/$authId");
		$this->recursiveZipPermission($directoryPath);


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
		return $this->processExtractedFiles($extractPath, $request, $authId);
	}


	protected function processExtractedFiles($extractPath, $request, $authId)
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

		// ✅ Validation checks
		if ($pdfFilesValidations->isEmpty()) {
			return response()->json([
				'message' => 'No PDF files found in the zip.',
			], 422);
		}


		// Process PDFs (loop only PDFs)
		foreach ($files as $file) {
			if (strtolower($file->getExtension()) === 'pdf') {
				$this->savePdf($file);
			}
		}

		// Import pdf if found
		$insertPdf = $this->insertCatalouePdf();
		if ($insertPdf) {
			$fileDeleteZipPath = "storage/app/uploads/zips/$authId";
			if (is_dir($fileDeleteZipPath)) {
				foreach (glob($fileDeleteZipPath . '/*.zip') as $zipFile) {
					unlink($zipFile);
				}
			}

			$fileDeleteExtractedPath = "storage/app/uploads/extracted/$authId";

			if (File::exists($fileDeleteExtractedPath)) {
				foreach (File::directories($fileDeleteExtractedPath) as $dir) {
					File::deleteDirectory($dir);
				}
				foreach (File::files($fileDeleteExtractedPath) as $file) {
					File::delete($file);
				}
			}

			return $insertPdf;

			/*return response()->json([
					'message' => 'Catalogue pdf file uploaded successfully.',
				]);*/
		}
	}


	protected function savePdf($file)
	{

		$extension = $file->getExtension();
		$originalName = $file->getFilename(); // e.g. invoice123.pdf
		$systemName = Str::uuid() . "." . $extension;
		$size = $file->getSize();

		$path = Storage::putFileAs("uploads/catalogue", $file, $systemName);

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

		$this->pdfUploads[$originalName] = $upload->file_system_name;

		return $upload;
	}


	public function recursiveZipPermission($path, $permission = 0777)
	{
		if (!file_exists($path)) {
			mkdir($path, $permission, true);
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

	public function insertCatalouePdf()
	{
		$pdfUploads = $this->pdfUploads;
		$missingTeams = [];
		$updatedTeams = [];
		$alreadyUploaded = [];

		foreach ($pdfUploads as $pdfName => $pdfUploadFileName) {
			$originalFileName = pathinfo($pdfName, PATHINFO_FILENAME);
			$originalFileNameExploade = explode("_", $originalFileName);

			$teamRegistrationId = $originalFileNameExploade[0];

			if ($teamRegistrationId) {
				$exists = \DB::table('team_msme_schemes')
					->where('team_id', $teamRegistrationId)
					->exists();

				if ($exists) {
					$uexists = \DB::table('team_msme_schemes')
						->where('team_id', $teamRegistrationId)
						->whereNull('is_uploaded')
						->exists();

					if ($uexists) {
						\DB::table('team_msme_schemes')
							->where('team_id', $teamRegistrationId)
							->update([
								'catalogue_document' => $pdfUploadFileName,
								'catalogue_document_original_name' => $pdfName,
								'is_uploaded' => 1,
								'updated_at' => now(),
							]);
						$updatedTeams[] = $teamRegistrationId;
					} else {
						$alreadyUploaded[] = $teamRegistrationId;
					}
				} else {
					$missingTeams[] = $teamRegistrationId;
				}
			}
		}

		$msg = '';

		if (!empty($alreadyUploaded)) {
			$msg .= 'Team ID(s) already uploaded: ' . implode(", ", $alreadyUploaded) . '. ';
		}

		if (!empty($updatedTeams) || !empty($missingTeams)) {
			if (!empty($updatedTeams)) {
				$msg .= 'Updated Team ID(s): ' . implode(", ", $updatedTeams) . '. ';
			}
			if (!empty($missingTeams)) {
				$msg .= 'Missing Team ID(s): ' . implode(", ", $missingTeams) . '. ';
			}
		}

		if (!empty($missingTeams) || !empty($alreadyUploaded)) {
			return response()->json([
				'message' => $msg,
			], 207);
		}

		return response()->json([
			'message' => 'All Team IDs ' . implode(",", $updatedTeams) . ' updated successfully',
		]);
	}
}
