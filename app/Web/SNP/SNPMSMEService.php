<?php

declare(strict_types=1);

namespace App\Web\SNP;

use App\Traits\DataTable;
use App\Core\BaseService;
use DB;
use App\Web\Msme\MsmeResource;
use App\Http\Services\CommonService;
use App\Traits\HasAttribute;

class SNPMSMEService extends BaseService
{
    use DataTable, HasAttribute;
    protected array $columns = [
        1 => 'team_id',
        2 => 'udyam_no',
        3 => 'mobile',
        4 => 'email',
        5 => 'entrepreneur_name',
        6 => 'enterprise_name',
        7 => 'organisation_type',
        8 => 'created_at'
    ];



     public function getmyMSMEList(array $selectedIds = [])
    {
      
        [$limit, $order, $dir, $search, $page, $filters] = $this->getDataTableParams();


        if (hasRole('snp')) {
            $snp = $this->getSnpDetail(AuthId());

            $stateIds = [];
            $transactionTypeIds = [];
            $subDomainIds = [];

            if (!empty($snp->state_id)) {
                $stateIds = json_decode($snp->state_id, true);
            }

            if (!empty($snp->transaction_type)) {
                $transactionTypeIds = json_decode($snp->transaction_type, true);
            }

            if (!empty($snp->sub_domain)) {
                $subDomainIds = json_decode($snp->sub_domain, true);
            }
        }


        $nationalId = '5bc85de0-0292-11f1-922a-00155d022d06';




        $startDate = $filters['from_date'] ?? null;
        $endDate = $filters['to_date'] ?? null;
        $search ??= $this->escape_special_characters($search);

        $query = DB::table('team_msme_schemes as ms')
            ->select('ms.id', 'ms.team_id', 'ms.udyam_no', 'ms.mobile', 'ms.email', 'ms.entrepreneur_name', 'ms.enterprise_name', 'ms.organisation_type', 'ms.msme_classification', 'ms.social_category', 'ms.created_at', 's.name as state_name', 'ia.organization_name as association_name', 'creator_snp.organization_name as creator_snp_name','av.attribute_value as transaction_type')
            ->leftjoin('states as s', 's.id', '=', 'ms.state_id')
            ->leftjoin('industrial_associations as ia', 'ia.user_id', '=', 'ms.created_by')
            ->leftjoin('team_snp_scheme as creator_snp', 'creator_snp.user_id', '=', 'ms.created_by')
            ->leftJoin('attribute_values as av', 'av.id', '=', 'ms.ondc_transaction_type_id')
            ->where('ms.select_snp', 0)
            ->whereNull('ms.bpp_id')
            ->whereNotNull('ms.major_activity')->where('ms.major_activity', '!=', '');
        //->where('ms.is_msme_registration',1)//new code added after msme registration changes
        if (hasRole('snp')) {

            $snpDetail = DB::table('network_providers')
                ->where('user_id', AuthId())
                ->first();

            $roleSelections = $snpDetail
                ? json_decode($snpDetail->role_selection_details, true)
                : [];

            if (!empty($roleSelections)) {

                $nationalId = DB::table('states')
                    ->where('slug', 'national')
                    ->value('id');

                $query->where(function ($query) use ($roleSelections, $nationalId) {

                    foreach ($roleSelections as $role) {

                      if (($role['role_name'] ?? '') !== 'Seller Network Participant (SNP)') {
                        continue;
                    }
                        $query->orWhere(function ($subQuery) use ($role, $nationalId) {

                            $subQuery->whereRaw(
                                'JSON_CONTAINS(ms.product_category_id, ?)',
                                ['"' . trim($role['domain']) . '"']
                            );

                            if (($role['transaction_type_name'] ?? '') !== 'Both') {
                                $subQuery->where(function($subQuery) use ($role) {
                                    $subQuery->where('ms.ondc_transaction_type_id', $role['transaction_type'])
                                        ->orWhere('ms.ondc_transaction_type_id', 'b44fb78b-d49e-11f0-922a-00155d022d06'); // Both
                                });
                            }
                            else 
                            {
                                    $subQuery->whereIn(
                                        'ms.ondc_transaction_type_id',
                                        ['9e7e1e8b-5578-11f0-81dc-00155d022d06', // B2B
                                        '36523ead-533d-11f0-81dc-00155d022d06', // B2C
                                        'b44fb78b-d49e-11f0-922a-00155d022d06' ] //Both
                                    );
                            }

                            if (
                                !empty($role['serviceability']) &&
                                $role['serviceability'] != $nationalId
                            ) {

                                $subQuery->whereIn(
                                    'ms.state_id',
                                    (array) $role['serviceability']
                                );
                            }
                        });
                    }
                });
            }
        }
      


        if ($startDate) {
            $startDate = date('Y-m-d', strtotime($startDate));
            $query->where('ms.created_at', '>=', $startDate);
        }

        if ($endDate) {
            $endDate = (new \DateTime($endDate))->modify('+1 days')->format('Y-m-d');
            $query->where('ms.created_at', '<=', $endDate);
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
        //$query->orderBy($order, $dir); 
        $query->orderBy('ms.created_at', 'desc');

        if ($page) {
            return $this->getDataTableResult(
                MsmeResource::collection($query->paginate($limit))
            );
        }
        return $query->get();
        //return MsmeResource::collection($query->get());
    }
    // public function getmyMSMEList(array $selectedIds = [])
    // {
      
    //     [$limit, $order, $dir, $search, $page, $filters] = $this->getDataTableParams();


    //     if (hasRole('snp')) {
    //         $snp = $this->getSnpDetail(AuthId());

    //         $stateIds = [];
    //         $transactionTypeIds = [];
    //         $subDomainIds = [];

    //         if (!empty($snp->state_id)) {
    //             $stateIds = json_decode($snp->state_id, true);
    //         }

    //         if (!empty($snp->transaction_type)) {
    //             $transactionTypeIds = json_decode($snp->transaction_type, true);
    //         }

    //         if (!empty($snp->sub_domain)) {
    //             $subDomainIds = json_decode($snp->sub_domain, true);
    //         }
    //     }


    //     $nationalId = '5bc85de0-0292-11f1-922a-00155d022d06';




    //     $startDate = $filters['from_date'] ?? null;
    //     $endDate = $filters['to_date'] ?? null;
    //     $search ??= $this->escape_special_characters($search);

    //     $query = DB::table('team_msme_schemes as ms')
    //         ->select('ms.id', 'ms.team_id', 'ms.udyam_no', 'ms.mobile', 'ms.email', 'ms.entrepreneur_name', 'ms.enterprise_name', 'ms.organisation_type', 'ms.msme_classification', 'ms.social_category', 'ms.created_at', 's.name as state_name', 'ia.organization_name as association_name', 'creator_snp.organization_name as creator_snp_name','av.attribute_value as transaction_type')
    //         ->leftjoin('states as s', 's.id', '=', 'ms.state_id')
    //         ->leftjoin('industrial_associations as ia', 'ia.user_id', '=', 'ms.created_by')
    //         ->leftjoin('team_snp_scheme as creator_snp', 'creator_snp.user_id', '=', 'ms.created_by')
    //         ->leftJoin('attribute_values as av', 'av.id', '=', 'ms.ondc_transaction_type_id')
    //         ->where('ms.select_snp', 0)
    //         ->whereNull('ms.bpp_id')
    //         ->whereNotNull('ms.major_activity')->where('ms.major_activity', '!=', '');
    //     //->where('ms.is_msme_registration',1)//new code added after msme registration changes
    //     if (hasRole('snp')) {

    //         $snpDetail = DB::table('network_providers')
    //             ->where('user_id', AuthId())
    //             ->first();

    //         $roleSelections = $snpDetail
    //             ? json_decode($snpDetail->role_selection_details, true)
    //             : [];

    //         if (!empty($roleSelections)) {

    //             $nationalId = DB::table('states')
    //                 ->where('slug', 'national')
    //                 ->value('id');

    //             $query->where(function ($query) use ($roleSelections, $nationalId) {

    //                 foreach ($roleSelections as $role) {

    //                     $query->orWhere(function ($subQuery) use ($role, $nationalId) {

    //                         $subQuery->whereRaw(
    //                             'JSON_CONTAINS(ms.product_category_id, ?)',
    //                             ['"' . trim($role['domain']) . '"']
    //                         );

    //                         if (($role['transaction_type_name'] ?? '') !== 'Both') {
    //                             $subQuery->where(
    //                                 'ms.ondc_transaction_type_id',
    //                                 $role['transaction_type']
    //                             );
    //                         }
    //                         if (
    //                             !empty($role['serviceability']) &&
    //                             $role['serviceability'] != $nationalId
    //                         ) {

    //                             $subQuery->whereIn(
    //                                 'ms.state_id',
    //                                 (array) $role['serviceability']
    //                             );
    //                         }
    //                     });
    //                 }
    //             });
    //         }
    //     }
      


    //     if ($startDate) {
    //         $startDate = date('Y-m-d', strtotime($startDate));
    //         $query->where('ms.created_at', '>=', $startDate);
    //     }

    //     if ($endDate) {
    //         $endDate = (new \DateTime($endDate))->modify('+1 days')->format('Y-m-d');
    //         $query->where('ms.created_at', '<=', $endDate);
    //     }

    //     if (isset($filters['ondc_transaction_type_id']) && !empty($filters['ondc_transaction_type_id'])) {
    //         $query->where('ondc_transaction_type_id', $filters['ondc_transaction_type_id']);
    //     }

    //     if (!empty($filters['product_category_id']) && is_array($filters['product_category_id'])) {
    //         foreach ($filters['product_category_id'] as $categoryId) {
    //             $query->orWhereJsonContains('product_category_id', $categoryId);
    //         }
    //     }

    //     if ($search) {
    //         $query->where(function ($query) use ($search) {
    //             $query->where('ms.team_id', 'like', "%$search%")
    //                 ->orwhere('ms.udyam_no', 'like', "%$search%")
    //                 ->orWhere('ms.mobile', 'like', "%$search%")
    //                 ->orWhere('ms.email', 'like', "%$search%")
    //                 ->orWhere('ms.entrepreneur_name', 'like', "%$search%")
    //                 ->orWhere('ms.enterprise_name', 'like', "%$search%")
    //                 ->orWhere('ms.organisation_type', 'like', "%$search%")
    //                 ->orWhere('ms.created_at', 'like', "%$search%")
    //                 ->orWhere('ia.organization_name', 'like', "%$search%")
    //                 ->orWhere('creator_snp.organization_name', 'like', "%$search%");

    //             if (str_contains(strtolower('Self'), strtolower($search))) {
    //                 $query->orWhere(function ($q) {
    //                     $q->whereNull('ia.organization_name')->whereNull('creator_snp.organization_name');
    //                 });
    //             }
    //         });
    //     }
    //     if (!empty($selectedIds)) {
    //         $query->whereIn('ms.id', $selectedIds);
    //     }

    //     if (empty($selectedIds)) {
    //         if ($search) {
    //             $query->where(function ($query) use ($search) {
    //                 $query->where('ms.team_id', 'like', "%$search%")
    //                     ->orWhere('ms.udyam_no', 'like', "%$search%")
    //                     ->orWhere('ms.mobile', 'like', "%$search%")
    //                     ->orWhere('ms.email', 'like', "%$search%")
    //                     ->orWhere('ms.entrepreneur_name', 'like', "%$search%")
    //                     ->orWhere('ms.enterprise_name', 'like', "%$search%")
    //                     ->orWhere('ms.organisation_type', 'like', "%$search%")
    //                     ->orWhere('ms.created_at', 'like', "%$search%")
    //                     ->orWhere('ia.organization_name', 'like', "%$search%")
    //                     ->orWhere('creator_snp.organization_name', 'like', "%$search%");

    //                 if (str_contains(strtolower('Self'), strtolower($search))) {
    //                     $query->orWhere(function ($q) {
    //                         $q->whereNull('ia.organization_name')->whereNull('creator_snp.organization_name');
    //                     });
    //                 }
    //             });
    //         }
    //     }
    //     //$query->orderBy($order, $dir); 
    //     $query->orderBy('ms.created_at', 'desc');

    //     if ($page) {
    //         return $this->getDataTableResult(
    //             MsmeResource::collection($query->paginate($limit))
    //         );
    //     }
    //     return $query->get();
    //     //return MsmeResource::collection($query->get());
    // }

    public function getMseDetails($msmeId)
    {
        return DB::table('team_msme_schemes')->where('id', $msmeId)->first();
    }


    public function getMseDetailsByUdyaMMobile($udyam_no, $mobile)
    {
        return DB::table('team_msme_schemes')->where('udyam_no', $udyam_no)->orWhere('mobile', $mobile)->first();
    }


    // public function getSnpDetail($id)
    // {
    //     $detail = DB::table('team_snp_scheme as snp')
    //         ->select('snp.id', 'snp.user_id', 'snp.sub_domain', 'snp.transaction_type', 'snp.state_id')
    //         ->where('snp.user_id', $id)
    //         ->first();
    //     return $detail;
    // }
    public function getSnpDetail($id)
        {
            $user = auth()->user();

            $userIds = [$id];
            if (!empty($user->parent_user_id)) {
                $userIds[] = $user->parent_user_id;
            }

            $detail = DB::table('team_snp_scheme as snp')
                ->select('snp.id', 'snp.user_id', 'snp.sub_domain', 'snp.transaction_type', 'snp.state_id')
                ->whereIn('snp.user_id', $userIds)
                ->first();

            return $detail;
        }




    public function getmyCghoosenMSMEList($select_snp, $is_msmeregistration, $status)
    {
        [$limit, $order, $dir, $search, $page, $filters] = $this->getDataTableParams();

        $startDate = $filters['from_date'] ?? null;
        $endDate = $filters['to_date'] ?? null;
        $search ??= $this->escape_special_characters($search);

        $query = DB::table('team_msme_schemes as ms')
            ->select(
                'ms.id',
                'ms.team_id',
                'ms.udyam_no',
                'ms.mobile',
                'ms.email',
                'ms.entrepreneur_name',
                'ms.enterprise_name',
                'ms.organisation_type',
                'ms.msme_classification',
                'ms.social_category',
                'ms.created_at',
                's.name as state_name',
                'ms.is_msme_registration',
                'ia.organization_name as association_name',
                'creator_snp.organization_name as creator_snp_name',
                'av.attribute_value as transaction_type'
            )
            ->join('team_snpmsme_mapping as tsm', 'tsm.msme_id', '=', 'ms.id')
            ->join('team_snp_scheme as tss', 'tsm.snp_id', '=', 'tss.id')
            ->leftJoin('states as s', 's.id', '=', 'ms.state_id')
            ->leftJoin('industrial_associations as ia', 'ia.user_id', '=', 'ms.created_by')
             ->leftJoin('attribute_values as av', 'av.id', '=', 'ms.ondc_transaction_type_id')
            ->leftJoin('team_snp_scheme as creator_snp', 'creator_snp.user_id', '=', 'ms.created_by');

        // if (hasRole('snp')) {
        //     $query->where('tss.user_id', (string) AuthId());
        // }

            if (hasRole('snp')) {
                $query->where(function ($q) {
                    $q->where('tss.user_id', (string) authId());

                    if (auth()->user()->parent_user_id) {
                        $q->orWhere('tss.user_id', (string) auth()->user()->parent_user_id);
                    }
                });
            }
        $query->where('ms.select_snp', 1);
        $query->where('tsm.status', $status);
        //$query->where('ms.is_msme_registration', '!=', $is_msmeregistration);
        $query->whereNotNull('ms.major_activity')->where('ms.major_activity', '!=', '');



        /*if (!is_null($select_snp)) { //new code after msme new registration
            $query->where('ms.select_snp', $select_snp);
        }

        $query->where('ms.is_msme_registration', $is_msmeregistration);*/ //new code after msme new registration

        /*->where(function ($q) {
            $q->where(function ($q1) {
                $q1->where('ms.select_snp', 1)
                    ->whereIn('ms.is_msme_registration', [1, 2]);
            })
            ->orWhere(function ($q2) {
                $q2->where('ms.select_snp', 0)
                    ->where('ms.is_msme_registration', 2);
            });
        });*/
        //dd($query->toSql());

        if ($startDate) {
            $startDate = date('Y-m-d', strtotime($startDate));
            $query->where('ms.created_at', '>=', $startDate);
        }

        if ($endDate) {
            $endDate = (new \DateTime($endDate))->modify('+1 days')->format('Y-m-d');
            $query->where('ms.created_at', '<=', $endDate);
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

        //$query->orderBy($order, $dir); 
        $query->orderBy('ms.updated_at', 'desc');

        if ($page) {
            return $this->getDataTableResult(
                MsmeResource::collection($query->paginate($limit))
            );
        }

        return MsmeResource::collection($query->get());
    }


    public function getmyOnboardedMSMEList11($select_snp, $is_msmeregistration, $status)
    {
        [$limit, $order, $dir, $search, $page, $filters] = $this->getDataTableParams();

        $startDate = $filters['from_date'] ?? null;
        $endDate = $filters['to_date'] ?? null;
        $search ??= $this->escape_special_characters($search);

        $query = DB::table('team_msme_schemes as ms')
            ->select(
                'ms.id',
                'ms.team_id',
                'ms.udyam_no',
                'ms.mobile',
                'ms.email',
                'ms.entrepreneur_name',
                'ms.enterprise_name',
                'ms.organisation_type',
                'ms.msme_classification',
                'ms.social_category',
                'ms.created_at',
                's.name as state_name',
                'ms.is_msme_registration',
                'ms.bpp_id',
                'ms.major_activity',
                'ia.organization_name as association_name',
                'creator_snp.organization_name as creator_snp_name',
                'av.attribute_value as transaction_type'
            )
            ->join('team_snpmsme_mapping as tsm', 'tsm.msme_id', '=', 'ms.id')
            ->join('team_snp_scheme as tss', 'tsm.snp_id', '=', 'tss.id')
            ->leftJoin('states as s', 's.id', '=', 'ms.state_id')
            ->leftJoin('industrial_associations as ia', 'ia.user_id', '=', 'ms.created_by')
             ->leftJoin('attribute_values as av', 'av.id', '=', 'ms.ondc_transaction_type_id')
            ->leftJoin('team_snp_scheme as creator_snp', 'creator_snp.user_id', '=', 'ms.created_by');


        // if (hasRole('snp')) {
        //     $query->where('tss.user_id', (string) AuthId());
        // }

        if (hasRole('snp')) {
            $query->where(function ($q) {
                $q->where('tss.user_id', (string) authId());

                if (auth()->user()->parent_user_id) {
                    $q->orWhere('tss.user_id', (string) auth()->user()->parent_user_id);
                }
            });
        }
        $query->where('tsm.status', $status);
        $query->whereNotNull('ms.major_activity')->where('ms.major_activity', '!=', 'Trading');

        //->where('ms.select_snp', 1)// comment after msme new registration

        /*if (!is_null($select_snp)) { //new code after msme new registration
            $query->where('ms.select_snp', $select_snp);
        }

        $query->where('ms.is_msme_registration', $is_msmeregistration);*/ //new code after msme new registration

        /*->where(function ($q) {
            $q->where(function ($q1) {
                $q1->where('ms.select_snp', 1)
                    ->whereIn('ms.is_msme_registration', [1, 2]);
            })
            ->orWhere(function ($q2) {
                $q2->where('ms.select_snp', 0)
                    ->where('ms.is_msme_registration', 2);
            });
        });*/
        //dd($query->toSql());

        if ($startDate) {
            $startDate = date('Y-m-d', strtotime($startDate));
            $query->where('ms.created_at', '>=', $startDate);
        }

        if ($endDate) {
            $endDate = (new \DateTime($endDate))->modify('+1 days')->format('Y-m-d');
            $query->where('ms.created_at', '<=', $endDate);
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
                    ->orWhere('ms.udyam_no', 'like', "%$search%")
                    ->orWhere('ms.mobile', 'like', "%$search%")
                    ->orWhere('ms.email', 'like', "%$search%")
                    ->orWhere('ms.bpp_id', 'like', "%$search%")
                    ->orWhere('ms.entrepreneur_name', 'like', "%$search%")
                    ->orWhere('ms.enterprise_name', 'like', "%$search%")
                    ->orWhere('ms.organisation_type', 'like', "%$search%")
                    ->orWhere('ms.created_at', 'like', "%$search%")
                    ->orWhere('s.name', 'like', "%$search%")
                    ->orWhere('ms.msme_classification', 'like', "%$search%")
                    ->orWhereRaw("DATE_FORMAT(ms.created_at, '%d-%m-%Y') LIKE ?", ["%$search%"])
                    ->orWhere('ia.organization_name', 'like', "%$search%")
                    ->orWhere('creator_snp.organization_name', 'like', "%$search%");

                if (str_contains(strtolower('Self'), strtolower($search))) {
                    $query->orWhere(function ($q) {
                        $q->whereNull('ia.organization_name')->whereNull('creator_snp.organization_name');
                    });
                }
            });
        }

        //$query->orderBy($order, $dir); 
        $query->whereNull('ms.is_catalogue_claim_generated');
        $query->orderBy('ms.updated_at', 'desc');

        if ($page) {
            return $this->getDataTableResult(
                MsmeResource::collection($query->paginate($limit))
            );
        }

        return MsmeResource::collection($query->get());
    }

    public function accountsCreatedMSMEList($select_snp, $is_msmeregistration, $status)
    {
        [$limit, $order, $dir, $search, $page, $filters] = $this->getDataTableParams();

        $startDate = $filters['from_date'] ?? null;
        $endDate = $filters['to_date'] ?? null;
        $search ??= $this->escape_special_characters($search);

        $query = DB::table('team_msme_schemes as ms')
            ->select(
                'ms.id',
                'ms.team_id',
                'ms.udyam_no',
                'ms.mobile',
                'ms.email',
                'ms.entrepreneur_name',
                'ms.enterprise_name',
                'ms.organisation_type',
                'ms.msme_classification',
                'ms.social_category',
                'ms.created_at',
                's.name as state_name',
                'ms.is_msme_registration',
                'ms.major_activity'
            )
            ->join('team_snpmsme_mapping as tsm', 'tsm.msme_id', '=', 'ms.id')
            ->join('team_snp_scheme as tss', 'tsm.snp_id', '=', 'tss.id')
            ->leftJoin('states as s', 's.id', '=', 'ms.state_id');


        // if (hasRole('snp')) {
        //     $query->where('tss.user_id', (string) AuthId());
        // }

             if (hasRole('snp')) {
                $query->where(function ($q) {
                    $q->where('tss.user_id', (string) authId());

                    if (auth()->user()->parent_user_id) {
                        $q->orWhere('tss.user_id', (string) auth()->user()->parent_user_id);
                    }
                });
            }
        // $query->where('tsm.status', $status);
        // $query->whereNotNull('ms.major_activity')->where('ms.major_activity', '!=', 'Trading');

        //->where('ms.select_snp', 1)// comment after msme new registration

        /*if (!is_null($select_snp)) { //new code after msme new registration
            $query->where('ms.select_snp', $select_snp);
        }

        $query->where('ms.is_msme_registration', $is_msmeregistration);*/ //new code after msme new registration

        /*->where(function ($q) {
            $q->where(function ($q1) {
                $q1->where('ms.select_snp', 1)
                    ->whereIn('ms.is_msme_registration', [1, 2]);
            })
            ->orWhere(function ($q2) {
                $q2->where('ms.select_snp', 0)
                    ->where('ms.is_msme_registration', 2);
            });
        });*/
        //dd($query->toSql());

        if ($startDate) {
            $startDate = date('Y-m-d', strtotime($startDate));
            $query->where('ms.created_at', '>=', $startDate);
        }

        if ($endDate) {
            $endDate = (new \DateTime($endDate))->modify('+1 days')->format('Y-m-d');
            $query->where('ms.created_at', '<=', $endDate);
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
                    ->orWhere('ms.udyam_no', 'like', "%$search%")
                    ->orWhere('ms.mobile', 'like', "%$search%")
                    ->orWhere('ms.email', 'like', "%$search%")
                    ->orWhere('ms.entrepreneur_name', 'like', "%$search%")
                    ->orWhere('ms.enterprise_name', 'like', "%$search%")
                    ->orWhere('ms.organisation_type', 'like', "%$search%")
                    ->orWhere('ms.created_at', 'like', "%$search%")
                    ->orWhere('s.name', 'like', "%$search%")
                    ->orWhere('ms.msme_classification', 'like', "%$search%")
                    ->orWhereRaw("DATE_FORMAT(ms.created_at, '%d-%m-%Y') LIKE ?", ["%$search%"]);
            });
        }

        $query->where('ms.is_catalogue_claim_approved', true);

        //$query->orderBy($order, $dir); 
        $query->orderBy('ms.updated_at', 'desc');

        if ($page) {
            return $this->getDataTableResult(
                MsmeResource::collection($query->paginate($limit))
            );
        }

        return MsmeResource::collection($query->get());
    }

    public function packagingClaimApprovedMSMEList($select_snp, $is_msmeregistration, $status)
    {
        [$limit, $order, $dir, $search, $page, $filters] = $this->getDataTableParams();

        $startDate = $filters['from_date'] ?? null;
        $endDate = $filters['to_date'] ?? null;
        $search ??= $this->escape_special_characters($search);

        $query = DB::table('team_msme_schemes as ms')
            ->select(
                'ms.id',
                'ms.team_id',
                'ms.udyam_no',
                'ms.mobile',
                'ms.email',
                'ms.entrepreneur_name',
                'ms.enterprise_name',
                'ms.organisation_type',
                'ms.msme_classification',
                'ms.social_category',
                'ms.created_at',
                's.name as state_name',
                'ms.is_msme_registration',
                'ms.major_activity'
            )
            ->join('team_snpmsme_mapping as tsm', 'tsm.msme_id', '=', 'ms.id')
            ->join('team_snp_scheme as tss', 'tsm.snp_id', '=', 'tss.id')
            ->leftJoin('states as s', 's.id', '=', 'ms.state_id');


        if (hasRole('snp')) {
            $query->where('tss.user_id', (string) AuthId());
        }

        // $query->where('tsm.status', $status);
        // $query->whereNotNull('ms.major_activity')->where('ms.major_activity', '!=', 'Trading');

        //->where('ms.select_snp', 1)// comment after msme new registration

        /*if (!is_null($select_snp)) { //new code after msme new registration
            $query->where('ms.select_snp', $select_snp);
        }

        $query->where('ms.is_msme_registration', $is_msmeregistration);*/ //new code after msme new registration

        /*->where(function ($q) {
            $q->where(function ($q1) {
                $q1->where('ms.select_snp', 1)
                    ->whereIn('ms.is_msme_registration', [1, 2]);
            })
            ->orWhere(function ($q2) {
                $q2->where('ms.select_snp', 0)
                    ->where('ms.is_msme_registration', 2);
            });
        });*/
        //dd($query->toSql());

        if ($startDate) {
            $startDate = date('Y-m-d', strtotime($startDate));
            $query->where('ms.created_at', '>=', $startDate);
        }

        if ($endDate) {
            $endDate = (new \DateTime($endDate))->modify('+1 days')->format('Y-m-d');
            $query->where('ms.created_at', '<=', $endDate);
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
                    ->orWhere('ms.udyam_no', 'like', "%$search%")
                    ->orWhere('ms.mobile', 'like', "%$search%")
                    ->orWhere('ms.email', 'like', "%$search%")
                    ->orWhere('ms.entrepreneur_name', 'like', "%$search%")
                    ->orWhere('ms.enterprise_name', 'like', "%$search%")
                    ->orWhere('ms.organisation_type', 'like', "%$search%")
                    ->orWhere('ms.created_at', 'like', "%$search%")
                    ->orWhere('s.name', 'like', "%$search%")
                    ->orWhere('ms.msme_classification', 'like', "%$search%")
                    ->orWhereRaw("DATE_FORMAT(ms.created_at, '%d-%m-%Y') LIKE ?", ["%$search%"]);
            });
        }

        $query->where('ms.is_catalogue_claim_approved', true);
        $query->whereNull('ms.is_packaging_claim_generated');

        //$query->orderBy($order, $dir); 
        $query->orderBy('ms.updated_at', 'desc');

        if ($page) {
            return $this->getDataTableResult(
                MsmeResource::collection($query->paginate($limit))
            );
        }

        return MsmeResource::collection($query->get());
    }

    public function getMseToBeValidatedList($select_snp, $is_msmeregistration, $status)
    {
        [$limit, $order, $dir, $search, $page, $filters] = $this->getDataTableParams();

        $startDate = $filters['from_date'] ?? null;
        $endDate = $filters['to_date'] ?? null;
        $search ??= $this->escape_special_characters($search);

        $query = DB::table('team_msme_schemes as ms')
            ->select(
                'ms.id',
                'ms.team_id',
                'ms.udyam_no',
                'ms.mobile',
                'ms.email',
                'ms.entrepreneur_name',
                'ms.enterprise_name',
                'ms.organisation_type',
                'ms.msme_classification',
                'ms.social_category',
                'ms.created_at',
                's.name as state_name',
                'ms.is_msme_registration'
            )
            ->join('team_snpmsme_mapping as tsm', 'tsm.msme_id', '=', 'ms.id')
            ->leftJoin('team_snp_scheme as tss', 'tsm.snp_id', '=', 'tss.id')
            ->leftJoin('states as s', 's.id', '=', 'ms.state_id')
             //->where('tss.user_id', (string) AuthId());
             ->where(function ($q) {
                $q->where('tss.user_id', (string) AuthId());

                if (auth()->user()->parent_user_id) {
                    $q->orWhere('tss.user_id', (string) auth()->user()->parent_user_id);
                }
            });
        //->where('ms.select_snp', 1)// comment after msme new registration
        $query->where('ms.status', $status);
        $query->where('ms.is_msme_registration', $is_msmeregistration); //new code after msme new registration

        if (!is_null($select_snp)) { //new code after msme new registration
            $query->where('ms.select_snp', $select_snp);
        }


        /*->where(function ($q) {
            $q->where(function ($q1) {
                $q1->where('ms.select_snp', 1)
                    ->whereIn('ms.is_msme_registration', [1, 2]);
            })
            ->orWhere(function ($q2) {
                $q2->where('ms.select_snp', 0)
                    ->where('ms.is_msme_registration', 2);
            });
        });*/
        //dd($query->toSql());

        if ($startDate) {
            $startDate = date('Y-m-d', strtotime($startDate));
            $query->where('ms.created_at', '>=', $startDate);
        }

        if ($endDate) {
            $endDate = (new \DateTime($endDate))->modify('+1 days')->format('Y-m-d');
            $query->where('ms.created_at', '<=', $endDate);
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
                    ->orWhere('ms.udyam_no', 'like', "%$search%")
                    ->orWhere('ms.mobile', 'like', "%$search%")
                    ->orWhere('ms.email', 'like', "%$search%")
                    ->orWhere('ms.entrepreneur_name', 'like', "%$search%")
                    ->orWhere('ms.enterprise_name', 'like', "%$search%")
                    ->orWhere('ms.organisation_type', 'like', "%$search%")
                    ->orWhere('ms.created_at', 'like', "%$search%");
            });
        }

        //$query->orderBy($order, $dir); 
        $query->orderBy('ms.updated_at', 'desc');

        if ($page) {
            return $this->getDataTableResult(
                MsmeResource::collection($query->paginate($limit))
            );
        }

        return MsmeResource::collection($query->get());
    }


    public function getValidatedMseList($select_snp, $is_msmeregistration, $status)
    {
        [$limit, $order, $dir, $search, $page, $filters] = $this->getDataTableParams();

        $startDate = $filters['from_date'] ?? null;
        $endDate = $filters['to_date'] ?? null;
        $search ??= $this->escape_special_characters($search);

        $query = DB::table('team_msme_schemes as ms')
            ->select(
                'ms.id',
                'ms.team_id',
                'ms.udyam_no',
                'ms.mobile',
                'ms.email',
                'ms.entrepreneur_name',
                'ms.enterprise_name',
                'ms.organisation_type',
                'ms.msme_classification',
                'ms.social_category',
                'ms.created_at',
                's.name as state_name',
                'ms.is_msme_registration'
            )
            ->join('team_snpmsme_mapping as tsm', 'tsm.msme_id', '=', 'ms.id')
            ->join('team_snp_scheme as tss', 'tsm.snp_id', '=', 'tss.id')
            ->leftJoin('states as s', 's.id', '=', 'ms.state_id')
          //  ->where('tss.user_id', (string) AuthId())
          ->where(function ($q) {
                $q->where('tss.user_id', (string) AuthId());

                if (auth()->user()->parent_user_id) {
                    $q->orWhere('tss.user_id', (string) auth()->user()->parent_user_id);
                }
            })
            //->where('ms.select_snp', 1)// comment after msme new registration
            ->where('tsm.status', $status);
        if (!is_null($select_snp)) { //new code after msme new registration
            $query->where('ms.select_snp', $select_snp);
        }

        $query->where('ms.is_msme_registration', $is_msmeregistration); //new code after msme new registration

        /*->where(function ($q) {
            $q->where(function ($q1) {
                $q1->where('ms.select_snp', 1)
                    ->whereIn('ms.is_msme_registration', [1, 2]);
            })
            ->orWhere(function ($q2) {
                $q2->where('ms.select_snp', 0)
                    ->where('ms.is_msme_registration', 2);
            });
        });*/
        //dd($query->toSql());

        if ($startDate) {
            $startDate = date('Y-m-d', strtotime($startDate));
            $query->where('ms.created_at', '>=', $startDate);
        }

        if ($endDate) {
            $endDate = (new \DateTime($endDate))->modify('+1 days')->format('Y-m-d');
            $query->where('ms.created_at', '<=', $endDate);
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
                    ->orWhere('ms.udyam_no', 'like', "%$search%")
                    ->orWhere('ms.mobile', 'like', "%$search%")
                    ->orWhere('ms.email', 'like', "%$search%")
                    ->orWhere('ms.entrepreneur_name', 'like', "%$search%")
                    ->orWhere('ms.enterprise_name', 'like', "%$search%")
                    ->orWhere('ms.organisation_type', 'like', "%$search%")
                    ->orWhere('ms.created_at', 'like', "%$search%");
            });
        }

        //$query->orderBy($order, $dir); 
        $query->orderBy('ms.updated_at', 'desc');

        if ($page) {
            return $this->getDataTableResult(
                MsmeResource::collection($query->paginate($limit))
            );
        }

        return MsmeResource::collection($query->get());
    }



    public function getMseSelfRegistrationAndSeekDeskSupportList($select_snp, $is_msmeregistration, $status)
    {
        [$limit, $order, $dir, $search, $page, $filters] = $this->getDataTableParams();

        $startDate = $filters['from_date'] ?? null;
        $endDate = $filters['to_date'] ?? null;
        $search ??= $this->escape_special_characters($search);

        $query = DB::table('team_msme_schemes as ms')
            ->select(
                'ms.id',
                'ms.team_id',
                'ms.udyam_no',
                'ms.mobile',
                'ms.email',
                'ms.entrepreneur_name',
                'ms.enterprise_name',
                'ms.organisation_type',
                'ms.msme_classification',
                'ms.social_category',
                'ms.created_at',
                's.name as state_name',
                'ms.is_msme_registration'
            )
            ->leftJoin('team_snpmsme_mapping as tsm', 'tsm.msme_id', '=', 'ms.id')
            ->leftJoin('team_snp_scheme as tss', 'tsm.snp_id', '=', 'tss.id')
            ->leftJoin('states as s', 's.id', '=', 'ms.state_id');
        //->where('tss.user_id', (string) AuthId())
        //->where('ms.select_snp', 1)// comment after msme new registration
        $query->where('ms.status', $status);
        $query->where('ms.is_msme_registration', $is_msmeregistration); //new code after msme new registration
        $query->whereNotNull('ms.major_activity')
                 ->where('ms.major_activity', '!=', '');

        if (!is_null($select_snp)) { //new code after msme new registration
            $query->where('ms.select_snp', $select_snp);
        }


        /*->where(function ($q) {
            $q->where(function ($q1) {
                $q1->where('ms.select_snp', 1)
                    ->whereIn('ms.is_msme_registration', [1, 2]);
            })
            ->orWhere(function ($q2) {
                $q2->where('ms.select_snp', 0)
                    ->where('ms.is_msme_registration', 2);
            });
        });*/
        //dd($query->toSql());

        if ($startDate) {
            $startDate = date('Y-m-d', strtotime($startDate));
            $query->where('ms.created_at', '>=', $startDate);
        }

        if ($endDate) {
            $endDate = (new \DateTime($endDate))->modify('+1 days')->format('Y-m-d');
            $query->where('ms.created_at', '<=', $endDate);
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
                    ->orWhere('ms.udyam_no', 'like', "%$search%")
                    ->orWhere('ms.mobile', 'like', "%$search%")
                    ->orWhere('ms.email', 'like', "%$search%")
                    ->orWhere('ms.entrepreneur_name', 'like', "%$search%")
                    ->orWhere('ms.enterprise_name', 'like', "%$search%")
                    ->orWhere('ms.organisation_type', 'like', "%$search%")
                    ->orWhere('ms.created_at', 'like', "%$search%");
            });
        }

        //$query->orderBy($order, $dir); 
        $query->orderBy('ms.updated_at', 'desc');

        if ($page) {
            return $this->getDataTableResult(
                MsmeResource::collection($query->paginate($limit))
            );
        }

        return MsmeResource::collection($query->get());
    }


    public function getDropdownList()
    {
        $commonService = new CommonService();
        return [
            'ondc_types' => $this->listOf('types-of-transactions-preferred-on-ondc'),
            'sub_domains' => $commonService->getDropdownNewList('sub_domains', 'status', 'ASC', 'name', array('id', 'name')),
        ];
    }
}
