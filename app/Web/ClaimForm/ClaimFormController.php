<?php

declare(strict_types=1);

namespace App\Web\ClaimForm;

use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\ClientController;
use App\Web\ApplicationWorkflow\ApplicationWorkflowService;
use App\Web\Claim\ClaimReviewStatus;
use App\Web\Claim\ClaimService;
use App\Web\Timeline\TimelineService;
use DB;
use App\Traits\HasCreateSubject;
use Illuminate\Support\Facades\Redis;


class ClaimFormController extends ClientController
{
  use HasCreateSubject;

  private static string $module = 'catalogue-created';
  private static string $tlmodule = 'transacted-live';

  public function __construct(private ClaimService $service) {}


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


  public function getDocumentCategoryId()
  {
    return DB::table('document_categories')->where('slug', 'ca_certificate')->first();
  }

  public function getDocumentDeclarationCategoryId()
  {
    return DB::table('document_categories')->where('slug', 'declaration-ondc')->first();
  }


  public function getMsmeDetails(string $id): ?object
  {

    return DB::table('team_msme_schemes')
      ->select(
        'team_msme_schemes.*',
        'av.attribute_value as transaction_type',
        's.name as state',
        'l.name as district',
        'cac.rate as product_amount',
        DB::raw("GROUP_CONCAT(DISTINCT sd.name) as subdomain_names"),
      )
      ->join('attribute_values as av', 'av.id', '=', 'team_msme_schemes.ondc_transaction_type_id')
      ->leftjoin('states as s', 's.id', '=', 'team_msme_schemes.state_id')
      ->leftjoin('locations as l', 'l.id', '=', 'team_msme_schemes.district_id')
      ->leftJoin('sub_domains as sd', function ($join) {
        $join->whereRaw("JSON_CONTAINS(team_msme_schemes.product_category_id, JSON_QUOTE(sd.id))");
      })
      ->leftJoin('catalogue_aov_categories as cac', 'cac.sub_domain_id', '=', 'sd.id')
      ->where('team_msme_schemes.id', $id)
      ->first();
  }


  public function index(): View
  {


    /*try {
		$pong = Redis::ping();
		dd($pong); // Should output "+PONG"
	} catch (\Exception $e) {
		dd('Redis not connected: '.$e->getMessage());
	} */

    $claimSlug = 'claim-for-catalogue-creation';
    $claim_types = $this->getClaimTypes($claimSlug);
    $claimTypeIdValue = $this->getClaimTypeId($claimSlug);
    $documentCategory = $this->getDocumentCategoryId();
    $documentDeclarationCategory = $this->getDocumentDeclarationCategoryId();
    $redirectUrl = 'claims';


    $tabs = $this->service->getClaimsDataTableDetails($claimSlug);

    $declaration = $this->service->getLowestClaimWorkflowDeclaration($claimSlug);
    //dd($declaration);

    // dd($tabs);
    $tabs = collect($tabs)->firstWhere('claim_type_slug', $claimSlug);
    $showRejectLabel = false;

    return view('claim-form.index', compact('claimSlug', 'showRejectLabel', 'redirectUrl', 'claim_types', 'claimTypeIdValue', 'tabs', 'declaration', 'documentCategory', 'documentDeclarationCategory'))->with('title', 'Claim for Catalogue Creation');
  }


  public function accountsClaim(): View
  {
    $claimSlug = 'claim-for-accounts-management';
    $claim_types = $this->getClaimTypes($claimSlug);
    $claimTypeIdValue = $this->getClaimTypeId($claimSlug);
    $documentCategory = $this->getDocumentCategoryId();

    $tabs = $this->service->getClaimsDataTableDetails($claimSlug);
    $tabs = collect($tabs)->firstWhere('claim_type_slug', $claimSlug);
    //dd($tabs);
    $declaration = $this->service->getLowestClaimWorkflowDeclaration($claimSlug);
    $documentDeclarationCategory = $this->getDocumentDeclarationCategoryId();
    $redirectUrl = 'accounts-claims';
    $showRejectLabel = false;
    return view('claim-form.index', compact('claimSlug', 'showRejectLabel', 'redirectUrl', 'claim_types', 'claimTypeIdValue', 'tabs', 'declaration', 'documentDeclarationCategory', 'documentCategory'))->with('title', 'Claim for Accounts Management');
  }
  public function packagingClaimList(): View
  {
    $claimSlug = 'claim-for-packaging';
    $claim_types = $this->getClaimTypes($claimSlug);
    $claimTypeIdValue = $this->getClaimTypeId($claimSlug);

    $tabs = $this->service->getClaimsDataTableDetails($claimSlug);
    $tabs = collect($tabs)->firstWhere('claim_type_slug', $claimSlug);
    // dd($tabs);
    $declaration = $this->service->getLowestClaimWorkflowDeclaration($claimSlug);
    $documentDeclarationCategory = $this->getDocumentDeclarationCategoryId();
    $documentCategory = $this->getDocumentCategoryId();
    $redirectUrl = 'packaging-support-claim';
    $showRejectLabel = false;
    return view('claim-form.index', compact('claimSlug', 'showRejectLabel', 'redirectUrl', 'claim_types', 'claimTypeIdValue', 'tabs', 'declaration', 'documentDeclarationCategory', 'documentCategory'))->with('title', 'Claim for Packaging Support');
  }

  public function logisticClaim(): View
  {
    $claimSlug = 'claim-for-transportation-and-logistic';
    $claim_types = $this->getClaimTypes($claimSlug);
    $claimTypeIdValue = $this->getClaimTypeId($claimSlug);
    $tabs = $this->service->getClaimsDataTableDetails($claimSlug);
    $tabs = collect($tabs)->firstWhere('claim_type_slug', $claimSlug);
    $declaration = $this->service->getLowestClaimWorkflowDeclaration($claimSlug);
    $documentDeclarationCategory = $this->getDocumentDeclarationCategoryId();
    $documentCategory = $this->getDocumentCategoryId();
    $redirectUrl = 'logistics-transportation-claim';
    $showRejectLabel = false;
    return view('claim-form.index', compact('claimSlug', 'showRejectLabel', 'redirectUrl', 'claim_types', 'claimTypeIdValue', 'tabs', 'declaration', 'documentCategory', 'documentDeclarationCategory'))->with('title', 'Claim for Transportation and Logistic');
  }

  public function demandGenerationClaim(): View
  {
    $claimSlug = 'claim-for-demand-generation';
    $claim_types = $this->getClaimTypes($claimSlug);
    $claimTypeIdValue = $this->getClaimTypeId($claimSlug);
    $tabs = $this->service->getClaimsDataTableDetails($claimSlug);
    $tabs = collect($tabs)->firstWhere('claim_type_slug', $claimSlug);
    $declaration = $this->service->getLowestClaimWorkflowDeclaration($claimSlug);
    $documentDeclarationCategory = $this->getDocumentDeclarationCategoryId();
    $documentCategory = $this->getDocumentCategoryId();
    $redirectUrl = 'demand-generation-claim';
    $showRejectLabel = false;
    // dd($tabs, $claimSlug, $claim_types, $claimTypeIdValue, $declaration, $documentDeclarationCategory, $documentCategory, $redirectUrl, $showRejectLabel);
    return view('claim-form.index', compact('claimSlug', 'showRejectLabel', 'redirectUrl', 'claim_types', 'claimTypeIdValue', 'tabs', 'declaration', 'documentCategory', 'documentDeclarationCategory'))->with('title', 'Claim for Demand Generation');
  }

  public function aiCataloguingClaim(): View
  {
    $claimSlug = 'claim-for-ai-cataloguing';
    $claim_types = $this->getClaimTypes($claimSlug);
    $claimTypeIdValue = $this->getClaimTypeId($claimSlug);
    $documentCategory = $this->getDocumentCategoryId();
    $documentDeclarationCategory = $this->getDocumentDeclarationCategoryId();
    $redirectUrl = 'ai-cataloguing-claim';

    $tabs = $this->service->getClaimsDataTableDetails($claimSlug);
    $tabs = collect($tabs)->firstWhere('claim_type_slug', $claimSlug);
    $declaration = $this->service->getLowestClaimWorkflowDeclaration($claimSlug);
    $showRejectLabel = false;

    return view('claim-form.index', compact(
      'claimSlug',
      'showRejectLabel',
      'redirectUrl',
      'claim_types',
      'claimTypeIdValue',
      'tabs',
      'declaration',
      'documentCategory',
      'documentDeclarationCategory'
    ))->with('title', 'Claim for AI Cataloguing');
  }


  public function queries($claimSlug): View
  {
    if ($claimSlug === 'claim-for-accounts-management') {
      $title = 'Batch Query for Account Management';
    } elseif ($claimSlug === 'claim-for-catalogue-creation') {
      $title = 'Batch Query for Catalogue Creation';
    } elseif ($claimSlug === 'claim-for-transportation-and-logistic') {
      $title = 'Batch Query for Transporation & Logistic';
    } else {
      $title = 'Batch Query for Packaging';
    }

    return view('claim-form.batch_query_list', compact('claimSlug', 'title'));
  }


  public function catalogueCreation($msmeId): View
  {

    $module_url = static::$module;
    $claimDocuments = $this->service->getClaimDocuments('claim-for-catalogue-creation');
    $claimSlug = 'claim-for-catalogue-creation';

    $msmeDetails = $this->getMsmeDetails($msmeId);

    $tax = getTotalTax();
    //dd($msmeDetails);

    return view('claim-form.catalogue-creation', compact('claimDocuments', 'module_url', 'claimSlug', 'msmeDetails', 'tax', 'msmeId'))->with('title', 'Catalogue Creation');

    //->with('lists', (object) $this->service->getDropdownList());
  }

  public function accountsAndManagementCreation($msmeId): View
  {
    $tax = getTotalTax();
    $module_url = static::$tlmodule;
    $claimDocuments = $this->service->getClaimDocuments('claim-for-accounts-management');
    $claimSlug = 'claim-for-accounts-management';
    $msmeDetails = $this->getMsmeDetails($msmeId);
    //dd($msmeDetails);
    return view('claim-form.catalogue-creation', compact('claimDocuments', 'module_url', 'claimSlug', 'msmeDetails', 'tax', 'msmeId'))->with('title', 'Claim for Accounts Management');

    //->with('lists', (object) $this->service->getDropdownList());
  }

  public function transportAndLogisticCreation($msmeId): View
  {
    $tax = getTotalTax();
    $module_url = static::$tlmodule;
    $claimDocuments = $this->service->getClaimDocuments('claim-for-transportation-and-logistic');
    //dd($claimDocuments);
    $claimSlug = 'claim-for-transportation-and-logistic';
    $msmeDetails = $this->getMsmeDetails($msmeId);
    return view('claim-form.catalogue-creation', compact('claimDocuments', 'module_url', 'claimSlug', 'msmeDetails', 'tax', 'msmeId'))->with('title', 'Claim for Transportation and Logistic');

    //->with('lists', (object) $this->service->getDropdownList());
  }

  public function packagingSupportClaim($msmeId): View
  {
    $tax = getTotalTax();
    $module_url = static::$tlmodule;
    $claimDocuments = $this->service->getClaimDocuments('claim-for-packaging');
    //dd($claimDocuments);
    $claimSlug = 'claim-for-packaging';
    $msmeDetails = $this->getMsmeDetails($msmeId);
    return view('claim-form.catalogue-creation', compact('claimDocuments', 'module_url', 'claimSlug', 'msmeDetails', 'tax', 'msmeId'))->with('title', 'Claim for Packaging Support');

    //->with('lists', (object) $this->service->getDropdownList());
  }

  public function demandGeneration($msmeId): View
  {

    $module_url = static::$module;
    $claimDocuments = $this->service->getClaimDocuments('claim-for-demand-generation');
    $claimSlug = 'claim-for-demand-generation';

    $msmeDetails = $this->getMsmeDetails($msmeId);

    //$tax=getTotalTax();
    //dd($tax);

    return view('claim-form.demand-generation', compact('claimDocuments', 'module_url', 'claimSlug', 'msmeDetails'))->with('title', 'Demand Generation');

    //->with('lists', (object) $this->service->getDropdownList());
  }

  public function packagingClaim($msmeId): View
  {
    dd('1');
    $module_url = static::$module;
    $claimDocuments = $this->service->getClaimDocuments('claim-for-packaging');
    $claimSlug = 'claim-for-packaging';

    $msmeDetails = $this->getMsmeDetails($msmeId);

    return view('claim-form.packaging-claim', compact('claimDocuments', 'module_url', 'claimSlug', 'msmeDetails'))->with('title', 'Packaging Claim');

    //->with('lists', (object) $this->service->getDropdownList());
  }

  public function edit(Request $request, string $claimId): View
  {
    $module_url = static::$module;
    //dd($this->service->getClaimViewDetails($claimId));
    [$claimDetails, $claimOrders, $claimDocuments] = $this->service->getClaimViewDetails($claimId);
    abort_if(empty($claimDetails) || !isset($claimDetails['claim_type_id']), 404, 'Claim not found.');
    // dd($claimDetails, $claimOrders, $claimDocuments);
    $tax = getTotalTax();

    $claimSlug = \DB::table('claim_types')->select('slug')->where('id', $claimDetails['claim_type_id'])->first()->slug;

    if (empty($claimDocuments)) {
      $claimDocuments = $this->service->getClaimDocuments($claimSlug);
      //dd($claimDocuments);
    }

    $msmeDetails = $this->service->getMsmeDetails($claimDetails['team_registration_id'] ?? '');

    $viewName = $claimSlug === 'claim-for-demand-generation' ? 'claim-form.demand-generation' : 'claim-form.catalogue-creation';
    $msmeId = $msmeDetails->id ?? '';

    return view($viewName, compact('claimId', 'claimDetails', 'claimOrders', 'claimDocuments', 'module_url', 'claimSlug', 'tax', 'msmeDetails', 'msmeId'))->with('title', 'Claim Edit');
  }

  public function show(Request $request, string $claimId): View
  {
    //dd($claimId);
    [$claimDetails, $claimOrders, $claimDocuments] = $this->service->getClaimViewDetails($claimId);
    abort_if(empty($claimDetails) || !isset($claimDetails['claim_type_id']), 404, 'Claim not found.');

    $batchId = DB::table('dy_batch_claims')->where('claim_id', $claimId)
    // ->whereNull('is_deleted',1)
    ->value('batch_id');
    $invoiceDocumentPath = DB::table("dy_attachments")
      ->where("entity_id", $batchId)
      ->where('attachment_type_id', '194a9d66-c4eb-4d35-b266-448638e07ca3')
      ->value("file_path");
    $invoiceDocument = null;
    if ($invoiceDocumentPath) {
      $invoiceDocument = url('storage/app/uploads/ca_certificate/' . $invoiceDocumentPath);
    }
    
    //dd($claimDetails,$claimOrders,$claimDocuments);
    // $claimDocuments = $this->service->getClaimDocumentsByClaimId($claimId);
    //dd($claimDetails,$claimOrders, $claimDocuments);
    $icon = 'arrow-back-w';
    $label = 'back';
    $claimSlug = \DB::table('claim_types')->select('slug')->where('id', $claimDetails['claim_type_id'])->first()->slug;


    $isForwarded = app(ApplicationWorkflowService::class)->isForwarded($claimDetails['id']);
    //dd($isForwarded);
    $roles = $this->getRoleTypes();
    return view('claim-form.show', compact('claimDetails', 'claimOrders', 'claimDocuments', 'isForwarded', 'roles', 'icon', 'label', 'claimSlug', 'invoiceDocument'))->with('title', 'Claim Details');
  }

  // public function batchClaimsShowById(Request $request, string $claimId): View
  // {
  //   //dd($claimId);
  //   // $authId=AuthId();
  //   // dd($authId);
  //   [$claimDetails, $claimOrders, $claimDocuments] = $this->service->getBatchClaimsViewDetails($claimId);
  //   $isForwarded = app(ApplicationWorkflowService::class)->isForwarded($claimDetails['id']);
  //   //dd($isForwarded);
  //   $roles = $this->getRoleTypes();
  //   return view('claim-form.show', compact('claimDetails', 'claimOrders', 'claimDocuments', 'isForwarded', 'roles'))->with('title', 'Claim Details');
  // }

  public function getRoleTypes()
  {
    $query = DB::table('roles');

    if (hasRole('nsic-finance')) {
      $query->whereNotIn('slug', [
        'nsic-pmu',
        'nsic-business',
        'nsic-finance',
        'administrator',
        'bnp',
        'mo-mse'
      ]);
    }

    if (hasRole('nsic')) {
      $query->whereNotIn('slug', [
        'nsic',
        'nsic-pmu',
        'nsic-business',
        'nsic-finance',
        'administrator',
        'bnp',
        'mo-mse'
      ]);
    }

    if (hasRole('ondc-admin')) {
      $query->whereNotIn('slug', [
        'nsic',
        'ondc-admin',
        'nsic-pmu',
        'nsic-business',
        'nsic-finance',
        'administrator',
        'bnp',
        'mo-mse'
      ]);
    }

    $result = $query->pluck('name', 'slug')->toArray();

    return $result;
  }


  public function PaymentUpdate(Request $request)
  {

    $validator = Validator::make($request->all(), [
      'batch_id' => 'required|string',
      'pfms_number' => 'required|string|max:255',
      //'amount' => 'required|numeric',
    ]);

    if ($validator->fails()) {
      return response()->json(['errors' => $validator->errors()], 422);
    }

    $batch_id = $request->input('batch_id');
    $pfmsNumber = $request->input('pfms_number');
    //$amount = $request->input('amount');
    $comment = $request->input('comment');

    try {
      DB::beginTransaction();

      DB::table('batches')->where('id', $batch_id)
        ->update([
          'pfms_number'  => $pfmsNumber,
          'status_id' => ClaimReviewStatus::PAYMENT_COMPLETED->value,
          'updated_by'   => auth()->user()->id,
          'updated_at'   => now(),
        ]);

      DB::table('batch_review_statuses')->where('batch_id', $batch_id)->where('role_id', '!=', '9ffe00c0-efe0-48a1-835e-3de599dff33e')
        ->update([
          'status_id' => ClaimReviewStatus::PAYMENT_COMPLETED->value,
          'updated_by'   => auth()->user()->id,
          'updated_at'   => now(),
        ]);

      $claims = DB::table('batch_claims')->join('claims', 'claims.id', '=', 'batch_claims.claim_id')
        ->where('batch_id', $batch_id)->where('status', ClaimReviewStatus::APPROVED->value)->pluck('claim_id')->toArray();

      $updatedClaims = [
        'pfms_number'  => $pfmsNumber,
        'status'       => ClaimReviewStatus::PAYMENT_COMPLETED->value,
        'review_status' => ClaimReviewStatus::PAYMENT_COMPLETED->value,
        //'ca_review_status' => ClaimReviewStatus::PAYMENT_COMPLETED->value,
        'ondc_review_status' => ClaimReviewStatus::PAYMENT_COMPLETED->value,
        'nsic_review_status' => ClaimReviewStatus::PAYMENT_COMPLETED->value,
        'nsicfinance_review_status' => ClaimReviewStatus::PAYMENT_COMPLETED->value,
        'nsicfinance_review_status_updated_at' => now(),
        'nsicfinance_review_status_updated_by' => auth()->user()->id,
        'updated_by'   => auth()->user()->id,
        'updated_at'   => now(),
        'remarks'   => $comment
      ];

      $result = DB::table('claims')->whereIn('id', $claims)->update($updatedClaims);

      $updatedBatchClaims = [
        'status_id'       => ClaimReviewStatus::PAYMENT_COMPLETED->value,
        'ondc_review_status' => ClaimReviewStatus::PAYMENT_COMPLETED->value,
        'nsic_review_status' => ClaimReviewStatus::PAYMENT_COMPLETED->value,
        'nsicfinance_review_status' => ClaimReviewStatus::PAYMENT_COMPLETED->value,
        'updated_by'   => auth()->user()->id,
        'updated_at'   => now(),
      ];

      $result = DB::table('batch_claims')->where('batch_id', $batch_id)->whereIn('claim_id', $claims)->update($updatedBatchClaims);



      if (!$result) {
        throw new \Exception('Failed to update claim.');
      }

      // 2. Add timeline entry
      /* $statusName = ClaimReviewStatus::getName((int) ClaimReviewStatus::PAYMENT_COMPLETED->value);
      TimelineService::addApprovalDocument(
        serviceId: $claim_id,
        subject: $this->createSubject(ClaimReviewStatus::PAYMENT_COMPLETED->value),
        comment: $comment ?? null,
        status: $statusName
      );*/

      DB::commit();

      return response()->json([
        'status'  => true,
        'message' => 'Payment details updated successfully.'
      ]);
    } catch (\Throwable $e) {
      DB::rollBack();
      return response()->json([
        'status'  => false,
        'message' => 'Transaction failed: ' . $e->getMessage()
      ], 500);
    }
  }


  // public function deleteAddMoreOrder(Request $request)
  // {
  //   dd($request->id);
  //     $id = $request->id;
  //     if($id){
  //       DB::table('claim_orders')->where('id', $id)->delete();
  //     }
  //     return response()->json([
  //         'status' => true,
  //         'message' => 'Order row deleted successfully'
  //     ]);
  // }


}
