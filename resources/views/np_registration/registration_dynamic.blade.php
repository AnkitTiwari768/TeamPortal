<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <meta name="timer_time" content="{{ \App\Http\Services\VerificationService::CODE_EXPIRATION_TIME }}" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta http-equiv="X-Frame-Options" content="deny">
    <meta http-equiv="Content-Security-Policy" content="frame-ancestors 'none';">
    <meta name="description" content="" />
    <meta name="author" content="" />
    <link href="{{ asset('assets/favicon.ico') }}" type="image/x-icon" rel="icon" />
    <link href="{{ asset('assets/favicon.ico') }}" type="image/x-icon" rel="shortcut icon" />
    <title>SNP Registration</title>
    <link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/css/styles.css') }}" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('assets/css/font-awesome.css?ver=' . time()) }}">
    <link rel="stylesheet" href="{{ asset('assets/css/response.css?ver=' . time()) }}">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <?php Session::forget(['mobile', 'email']);
    Session::forget(['mobile_value', 'email_value']); ?>

    <style>
        .hide-field {
            display: none;
        }
    </style>

</head>


@php
    $timelineDetails = [
        ['step' => 1, 'title' => 'Basic Details', 'status' => 'done'],
        ['step' => 2, 'title' => 'Configuration Details', 'status' => 'pending'],
        ['step' => 3, 'title' => 'Value Proposition for MSEs', 'status' => 'pending'],
        ['step' => 4, 'title' => 'Commercial Model', 'status' => 'pending'],
    ];

    $formElements = [
        [
            'section' => 'Role',
            'gridClass' => 'col-lg-12',
            'fields' => [
                [
                    'label' => 'Role',
                    'type' => 'select',
                    'name' => 'roles[]',
                    'required' => true,
                    'options' => $roleList->pluck('name', 'id')->toArray(),
                    'columnClass' => 'col-lg-12 mb-3',
                    'attributes' => [
                        'multiple' => true,
                        'class' => 'form-select',
                        'id' => 'roles',
                    ],
                ],
            ],
        ],
        [
            'section' => 'Basic details',
            'gridClass' => 'col-lg-12',
            'fields' => [
                [
                    'label' => 'Organization ID (as on ONDC)',
                    'type' => 'text',
                    'name' => 'organization_id',
                    'required' => true,
                    'columnClass' => 'col-lg-4 mb-3',
                    'attributes' => [
                        'class' => 'input-text numeric',
                        'maxlength' => 20,
                        'placeholder' => 'Organization ID',
                        'id' => 'organization_id',
                    ],
                ],
                [
                    'label' => 'Organization/Legal Name',
                    'type' => 'text',
                    'name' => 'organization_name',
                    'required' => true,
                    'columnClass' => 'col-lg-4 mb-3',
                    'attributes' => [
                        'class' => 'input-text txtnumerichypenapercend',
                        'maxlength' => 100,
                        'placeholder' => 'Legal Name',
                        'id' => 'organization_name',
                    ],
                ],
                [
                    'label' => 'Official/Primary Email ID',
                    'type' => 'text',
                    'name' => 'email',
                    'required' => true,
                    'columnClass' => 'col-lg-4 mb-3',
                    'attributes' => [
                        'class' => 'input-text alphanumeric',
                        'maxlength' => 100,
                        'id' => 'email',
                        'placeholder' => 'Official/Primary Email ID',
                    ],
                ],
                [
                    'label' => 'BPPId.ProviderID',
                    'type' => 'text',
                    'name' => 'bppid_providerid',
                    'required' => true,
                    'columnClass' => 'col-lg-4 mb-3',
                    'attributes' => [
                        'class' => 'input-text alphanumeric',
                        'maxlength' => 100,
                        'id' => 'bppid_providerid',
                        'placeholder' => 'BPPId.ProviderID',
                    ],
                ],
            ],
        ],
        [
            'section' => 'Contact details',
            'gridClass' => 'col-lg-12',
            'fields' => [
                [
                    'label' => 'Official/Primary Email ID',
                    'type' => 'text',
                    'name' => 'contact_details_email',
                    'required' => true,
                    'columnClass' => 'col-lg-4 mb-3',
                    'attributes' => [
                        'class' => 'input-text alphanumeric',
                        'maxlength' => 100,
                        'id' => 'contact_details_email',
                        'placeholder' => 'Official/Primary Email ID',
                    ],
                ],
                [
                    'label' => 'Primary Contact No',
                    'type' => 'text',
                    'name' => 'contact_details_primary_contact_no',
                    'required' => true,
                    'columnClass' => 'col-lg-4 mb-3',
                    'attributes' => [
                        'class' => 'input-text numeric',
                        'maxlength' => 15,
                        'id' => 'contact_details_primary_contact_no',
                        'placeholder' => 'Primary Contact No',
                    ],
                ],
                [
                    'label' => 'WhatsApp No/Channel',
                    'type' => 'text',
                    'name' => 'contact_details_whatsapp_no',
                    'required' => false,
                    'columnClass' => 'col-lg-4 mb-3',
                    'attributes' => [
                        'class' => 'input-text numeric',
                        'maxlength' => 15,
                        'id' => 'contact_details_whatsapp_no',
                        'placeholder' => 'WhatsApp No/Channel',
                    ],
                ],
                [
                    'label' => 'Website',
                    'type' => 'text',
                    'name' => 'contact_details_website',
                    'required' => true,
                    'columnClass' => 'col-lg-4 mb-3',
                    'attributes' => [
                        'class' => 'input-text alphanumeric',
                        'maxlength' => 200,
                        'id' => 'contact_details_website',
                        'placeholder' => 'Website URL',
                    ],
                ],
                [
                    'label' => 'App Store Link(s)',
                    'type' => 'text',
                    'name' => 'contact_details_app_store_links',
                    'required' => false,
                    'columnClass' => 'col-lg-4 mb-3',
                    'attributes' => [
                        'class' => 'input-text alphanumeric',
                        'maxlength' => 200,
                        'id' => 'contact_details_app_store_links',
                        'placeholder' => 'App Store Link(s)',
                    ],
                ],
                [
                    'label' => 'Social Media Handles',
                    'type' => 'text',
                    'name' => 'contact_details_social_media_handles',
                    'required' => false,
                    'columnClass' => 'col-lg-4 mb-3',
                    'attributes' => [
                        'class' => 'input-text alphanumeric',
                        'maxlength' => 200,
                        'id' => 'contact_details_social_media_handles',
                        'placeholder' => 'Social Media Handles',
                    ],
                ],
            ],
        ],
        [
            'section' => 'Authorised Person(s)',
            'gridClass' => 'col-lg-12',
            'fields' => [
                [
                    'label' => 'Name of Authorised Person(s)',
                    'type' => 'text',
                    'name' => 'authorized_person_details_name',
                    'required' => true,
                    'columnClass' => 'col-lg-4 mb-3',
                    'attributes' => [
                        'class' => 'input-text alphanumeric',
                        'maxlength' => 80,
                        'id' => 'authorized_person_details_name',
                        'placeholder' => 'Name of Authorised Person(s)',
                    ],
                ],
                [
                    'label' => 'Role/Designation',
                    'type' => 'select',
                    'name' => 'authorized_person_details_designation',
                    'required' => false,
                    'columnClass' => 'col-lg-4 mb-3',
                    'attributes' => [
                        'class' => 'form-select',
                        'id' => 'authorized_person_details_designation',
                    ],
                    'options' => $roleList->pluck('name', 'id')->toArray(),
                ],
                [
                    'label' => 'Phone/WhatsApp Number',
                    'type' => 'text',
                    'name' => 'authorized_person_details_phone',
                    'required' => false,
                    'columnClass' => 'col-lg-4 mb-3',
                    'attributes' => [
                        'class' => 'input-text numeric',
                        'maxlength' => 15,
                        'id' => 'authorized_person_details_phone',
                        'placeholder' => 'Phone/WhatsApp Number',
                    ],
                ],
                [
                    'label' => 'Email Address',
                    'type' => 'text',
                    'name' => 'authorized_person_details_email',
                    'required' => true,
                    'columnClass' => 'col-lg-4 mb-3',
                    'attributes' => [
                        'class' => 'input-text',
                        'maxlength' => 80,
                        'id' => 'authorized_person_details_email',
                        'placeholder' => 'Email Address',
                    ],
                ],
                [
                    'label' => 'Authorized Certificate',
                    'type' => 'file',
                    'name' => 'authorized_person_details_certificate',
                    'required' => false,
                    'columnClass' => 'col-lg-4 mb-3',
                    'attributes' => [
                        'class' => 'form-control upload-pdf',
                        'data-hidden-id' => '#authorized_person_details_certificate_id',
                        'data-hidden-file' => '#authorized_person_details_certificate',
                        'data-category-id' => 'authorized_person_certificate',
                        'id' => 'authorized_person_certificate_file',
                        'accept' => 'application/pdf',
                    ],
                ],
            ],
        ],
        [
            'section' => 'Configuration details',
            'gridClass' => 'col-lg-12',
            'fields' => [
                [
                    'label' => 'GST Number',
                    'type' => 'text',
                    'name' => 'configuration_details_gst_number',
                    'required' => true,
                    'columnClass' => 'col-lg-4 mb-3',
                    'attributes' => [
                        'class' => 'input-text',
                        'maxlength' => 15,
                        'id' => 'configuration_details_gst_number',
                        'placeholder' => 'GST Number',
                    ],
                ],
                [
                    'label' => 'PAN',
                    'type' => 'text',
                    'name' => 'configuration_details_pan',
                    'required' => true,
                    'columnClass' => 'col-lg-4 mb-3',
                    'attributes' => [
                        'class' => 'input-text',
                        'maxlength' => 10,
                        'id' => 'configuration_details_pan',
                        'placeholder' => 'PAN',
                    ],
                ],
                [
                    'label' => 'CIN',
                    'type' => 'text',
                    'name' => 'configuration_details_cin',
                    'required' => false,
                    'columnClass' => 'col-lg-4 mb-3',
                    'attributes' => [
                        'class' => 'input-text',
                        'maxlength' => 21,
                        'id' => 'configuration_details_cin',
                        'placeholder' => 'CIN',
                    ],
                ],
                [
                    'label' => 'Startup ID/DPIIT Recognition',
                    'type' => 'text',
                    'name' => 'configuration_details_startup_id',
                    'required' => false,
                    'columnClass' => 'col-lg-4 mb-3',
                    'attributes' => [
                        'class' => 'input-text',
                        'maxlength' => 50,
                        'id' => 'configuration_details_startup_id',
                        'placeholder' => 'Startup ID/DPIIT Recognition',
                    ],
                ],
                [
                    'label' => 'FSSAI Number',
                    'type' => 'text',
                    'name' => 'configuration_details_fssai_number',
                    'required' => false,
                    'columnClass' => 'col-lg-4 mb-3',
                    'attributes' => [
                        'class' => 'input-text',
                        'maxlength' => 14,
                        'id' => 'configuration_details_fssai_number',
                        'placeholder' => 'FSSAI Number',
                    ],
                ],
                [
                    'label' => 'IEC Number',
                    'type' => 'text',
                    'name' => 'configuration_details_iec_number',
                    'required' => false,
                    'columnClass' => 'col-lg-4 mb-3',
                    'attributes' => [
                        'class' => 'input-text',
                        'maxlength' => 10,
                        'id' => 'configuration_details_iec_number',
                        'placeholder' => 'IEC Number',
                    ],
                ],
            ],
        ],
        [
            'section' => 'Bank details',
            'gridClass' => 'col-lg-12',
            'fields' => [
                [
                    'label' => 'Bank Name',
                    'type' => 'text',
                    'name' => 'bank_details_bank_name',
                    'required' => true,
                    'columnClass' => 'col-lg-4 mb-3',
                    'attributes' => [
                        'class' => 'input-text alphaNumericSpace',
                        'maxlength' => 80,
                        'id' => 'bank_details_bank_name',
                        'placeholder' => 'Bank Name',
                    ],
                ],
                [
                    'label' => 'IFSC CODE',
                    'type' => 'text',
                    'name' => 'bank_details_ifsc_code',
                    'required' => true,
                    'columnClass' => 'col-lg-4 mb-3',
                    'attributes' => [
                        'class' => 'input-text alphaNumericSpace',
                        'maxlength' => 20,
                        'id' => 'bank_details_ifsc_code',
                        'placeholder' => 'IFSC CODE',
                        'maxlength' => 11,
                    ],
                ],
                [
                    'label' => 'A/C No',
                    'type' => 'text',
                    'name' => 'bank_details_account_number',
                    'required' => true,
                    'columnClass' => 'col-lg-4 mb-3',
                    'attributes' => [
                        'class' => 'input-text numeric',
                        'maxlength' => 30,
                        'id' => 'bank_details_account_number',
                        'placeholder' => 'A/C No',
                    ],
                ],
            ],
        ],
        [
            'section' => 'Role Selection (defined below)',
            'gridClass' => 'col-lg-12',
            'fields' => [
                [
                    'label' => '', // Empty label for the heading
                    'type' => 'html',
                    'name' => 'role_selection_heading',
                    'required' => false,
                    'columnClass' => 'col-lg-12 mb-3',
                    'attributes' => [
                        'html' => '<div id="roleDetailsContainer"></div>',
                    ],
                ],
            ],
        ],
        [
            'section' => 'Value Proposition for MSEs',
            'gridClass' => 'col-lg-12',
            'fields' => [
                [
                    'label' => 'Short Description',
                    'type' => 'text',
                    'name' => 'value_proposition_details_short_description',
                    'required' => true,
                    'columnClass' => 'col-lg-4 mb-3',
                    'attributes' => [
                        'class' => 'input-text alphaNumericSpace',
                        'maxlength' => 10,
                        'id' => 'value_proposition_details_short_description',
                        'placeholder' => 'Short Description',
                    ],
                ],
                [
                    'label' => 'What additional service do you offer MSMEs beyond connecting them to ONDC',
                    'type' => 'text',
                    'name' => 'value_proposition_details_additional_service',
                    'required' => true,
                    'columnClass' => 'col-lg-4 mb-3',
                    'attributes' => [
                        'class' => 'input-text alphaNumericSpace',
                        'maxlength' => 10,
                        'id' => 'value_proposition_details_additional_service',
                        'placeholder' => 'Enter',
                    ],
                ],
                [
                    'label' => 'Language Supported',
                    'type' => 'select',
                    'name' => 'value_proposition_details_language_supported',
                    'required' => true,
                    'columnClass' => 'col-lg-4 mb-3',
                    'attributes' => [
                        'class' => 'form-select',
                        'id' => 'value_proposition_details_language_supported',
                    ],
                    'options' => $lang->pluck('name', 'id')->toArray(),
                ],
                [
                    'label' => 'Team Scheme landing page',
                    'type' => 'text',
                    'name' => 'value_proposition_details_team_scheme_landing_page',
                    'required' => true,
                    'columnClass' => 'col-lg-4 mb-3',
                    'attributes' => [
                        'class' => 'input-text alphaNumericSpace',
                        'id' => 'value_proposition_details_team_scheme_landing_page',
                        'placeholder' => 'Team Scheme landing page',
                    ],
                ],
                [
                    'label' => 'Short Video Pitch',
                    'type' => 'text',
                    'name' => 'value_proposition_details_short_video_pitch',
                    'required' => true,
                    'columnClass' => 'col-lg-4 mb-3',
                    'attributes' => [
                        'class' => 'input-text alphaNumericSpace',
                        'id' => 'value_proposition_details_short_video_pitch',
                        'placeholder' => 'Short Video Pitch',
                    ],
                ],
                [
                    'label' => 'Flyer / Presentation',
                    'type' => 'file',
                    'name' => 'value_proposition_details_flyer',
                    'required' => true,
                    'columnClass' => 'col-lg-4 mb-3',
                    'attributes' => [
                        'class' => 'form-control upload-pdf',
                        'data-hidden-id' => '#value_proposition_details_flyer_id',
                        'data-hidden-file' => '#value_proposition_details_flyer',
                        'data-category-id' => 'flyer_presentation',
                        'id' => 'flyer_presentation_file',
                        'accept' => '.jpg,.jpeg,.png,.pdf',
                    ],
                ],
            ],
        ],
        [
            'section' => 'Commercial Model',
            'gridClass' => 'col-lg-12',
            'fields' => [
                [
                    'label' => 'Do you Charge a fee',
                    'type' => 'select',
                    'name' => 'commercial_model_details_fee_charge',
                    'required' => true,
                    'columnClass' => 'col-lg-4 mb-3',
                    'attributes' => [
                        'class' => 'form-select',
                        'id' => 'commercial_model_details_fee_charge',
                    ],
                    'options' => [
                        '' => 'Select',
                        'yes' => 'Yes',
                        'no' => 'No',
                    ],
                ],
                [
                    'label' => 'Flat fee/subscription',
                    'type' => 'select',
                    'name' => 'commercial_model_details_fee_type',
                    'required' => true,
                    'columnClass' => 'col-lg-4 mb-3',
                    'attributes' => [
                        'class' => 'form-select',
                        'id' => 'commercial_model_details_fee_type',
                    ],
                    'options' => [
                        '' => 'Select',
                        'flat_fee' => 'Flat fee',
                        'subscription' => 'Subscription',
                    ],
                ],
                [
                    'label' => 'INR Amount',
                    'type' => 'text',
                    'name' => 'commercial_model_details_flat_fee',
                    'required' => false,
                    'columnClass' => 'col-lg-4 mb-3 hide-field flat_fee_field',
                    'attributes' => [
                        'class' => 'input-text numeric',
                        'maxlength' => 10,
                        'id' => 'commercial_model_details_flat_fee',
                        'placeholder' => 'INR Amount',
                    ],
                    'conditional' => [
                        'field' => 'fee_type',
                        'value' => 'flat_fee',
                        'show' => true,
                    ],
                ],
                [
                    'label' => 'Select Subscription',
                    'type' => 'select',
                    'name' => 'commercial_model_details_subscription',
                    'required' => false,
                    'columnClass' => 'col-lg-4 mb-3 hide-field subscription_field',
                    'attributes' => [
                        'class' => 'form-select',
                        'id' => 'commercial_model_details_subscription',
                    ],
                    'options' => [
                        '' => 'Select',
                        'annual' => 'Annual',
                        'monthly' => 'Monthly',
                        'onetime' => 'One time',
                    ],
                    'conditional' => [
                        'field' => 'fee_type',
                        'value' => 'subscription',
                        'show' => true,
                    ],
                ],
                [
                    'label' => 'Commission Per Transaction',
                    'type' => 'text',
                    'name' => 'commercial_model_details_commission_per_transaction',
                    'required' => true,
                    'columnClass' => 'col-lg-4 mb-3',
                    'attributes' => [
                        'class' => 'input-text',
                        'id' => 'commercial_model_details_commission_per_transaction',
                        'placeholder' => 'Commission Per Transaction',
                    ],
                ],
                [
                    'label' => 'Other Fees',
                    'type' => 'text',
                    'name' => 'commercial_model_details_other_fees',
                    'required' => true,
                    'columnClass' => 'col-lg-4 mb-3',
                    'attributes' => [
                        'class' => 'input-text numeric',
                        'maxlength' => 10,
                        'id' => 'commercial_model_details_other_fees',
                        'placeholder' => 'Other Fees',
                    ],
                ],
                [
                    'label' => 'Special Offers/Discounts/Add-ons?',
                    'type' => 'text',
                    'name' => 'commercial_model_details_special_offer',
                    'required' => true,
                    'columnClass' => 'col-lg-4 mb-3',
                    'attributes' => [
                        'class' => 'input-text alphaNumericSpace',
                        'maxlength' => 10,
                        'id' => 'commercial_model_details_special_offer',
                        'placeholder' => 'Special Offers/Discounts/Add-ons?',
                    ],
                ],
                [
                    'label' => 'Special Offers for Team scheme',
                    'type' => 'text',
                    'name' => 'commercial_model_details_team_scheme_offers',
                    'required' => true,
                    'columnClass' => 'col-lg-4 mb-3',
                    'attributes' => [
                        'class' => 'input-text alphaNumericSpace',
                        'maxlength' => 10,
                        'id' => 'commercial_model_details_team_scheme_offers',
                        'placeholder' => 'Special Offers for Team scheme',
                    ],
                ],
                [
                    'label' => 'Link to Commercial Model',
                    'type' => 'select',
                    'name' => 'commercial_model_details_commercial_model_link',
                    'required' => true,
                    'columnClass' => 'col-lg-4 mb-3',
                    'attributes' => [
                        'class' => 'form-select',
                        'id' => 'commercial_model_details_commercial_model_link',
                    ],
                    'options' => [
                        '' => 'Select',
                        'website' => 'Website',
                        'landing_page' => 'Landing Page',
                        'document' => 'Document',
                    ],
                    // 'options' => static_common_list($lists?->snp_commercial ?? []),
                ],
            ],
        ],
        [
            'section' => 'Declaration',
            'gridClass' => 'col-lg-12',
            'fields' => [
                [
                    'label' =>
                        'I/ We hereby declare that the details furnished above are true and correct to the best of my/ our knowledge and belief. If the above information is found to be incorrect / misleading/ false, appropriate action as per the laws may be taken against me/ us. I/ We hereby authorize NSIC/ ONDC to share the relevant details for the purpose of scheme administration and support, in compliance with applicable laws. I / We hereby declare that I / We have read the Privacy Policy, Terms and Conditions, Disclaimer and Data Sharing Policy, Operating guidelines of TEAM Initiative, SOPs of TEAM Initiative and abide by it. NSIC reserves the right to change/ amend the SOPs with the approval of the Ministry of MSME as per the policy & procedural requirement as and when warranted and without giving any notice.',
                    'type' => 'checkbox',
                    'name' => 'declaration',
                    'required' => true,
                    'columnClass' => 'col-lg-12 mb-3',
                    'attributes' => [
                        'class' => 'form-check-input',
                        'id' => 'agreecheck',
                    ],
                ],
            ],
        ],
    ];
@endphp

<body id="mainbody" class="registration-page">
    <div id="cover-spin" style="display:none"></div>
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-3 p-0">
                <div class="left-img-wrap h-100 px-4 py-3">
                    <div class="img-section-left sticky-top">
                        <div class="card-header logo-box mb-5 mt-0">
                            <img src="{{ asset('assets/img-new/mti-logo.svg') }}" class="logo">
                        </div>

                        @isset($timelineDetails)
                            <div class="timeline">
                                <ul class="unstyled p-0 m-0 ">
                                    @foreach ($timelineDetails as $timeline)
                                        <li class="d-flex gap-3 mb-3 {{ $timeline['status'] }} ">
                                            <div class="icon">{{ $timeline['step'] }}</div> {{ $timeline['title'] }}
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endisset
                    </div>
                </div>
            </div>

            <div class="col-lg-9 right-form-content">
                <div class="inner-header pb-3 pt-4 ">
                    <div class="row">
                        <div class="col-lg-6">
                            <h2 class="fw-bolder fs-4">NP Registration</h2>
                        </div>
                        <div class="col-lg-6 text-end">
                            <button class="btn btn-stroked-theme"> <i class="fa fa-long-arrow-left me-2"
                                    aria-hidden="true"></i> Go to home</button>
                        </div>
                    </div>
                </div>
                <div class="signup-wrapper card mt-2">
                    <div class="inner-login-wrapper pb-4">
                        <div class="row">
                            <form method="post" id="formId" class="mt-2 pt-3 formId" enctype="multipart/form-data">
                                <div class="row">
                                    <div class="col-lg-12">
                                        <p>Fill all required<span style="color: #db0000ff;">*</span> fields and complete
                                            the registration process.</p>
                                    </div>
                                </div>

                                @isset($formElements)
                                    @foreach ($formElements as $formElement)
                                        <div class="row {{ Str::slug($formElement['section'], '-') }}-detail"
                                            @if (isset($formElement['conditional']) && $formElement['conditional']) data-conditional-field="{{ $formElement['conditional']['field'] }}"
                data-conditional-value="{{ $formElement['conditional']['value'] }}"
                style="display: none;" @endif>
                                            <div class="{{ $formElement['gridClass'] }} mb-3">
                                                <h2 class="form-title-heading font-20">
                                                    @if (in_array($formElement['section'], ['Value Proposition for MSEs', 'Commercial Model', 'Declaration']))
                                                        <img src="{{ asset('assets/img/bbasic.svg') }}" alt="">
                                                    @else
                                                        <img src="{{ asset('assets/img/bbasic.svg') }}" alt="">
                                                    @endif
                                                    {{ $formElement['section'] }}
                                                </h2>
                                            </div>

                                            @isset($formElement['fields'])
                                                @foreach ($formElement['fields'] as $field)
                                                    @php
                                                        $fieldId = str_replace(
                                                            ['[', ']'],
                                                            '_',
                                                            rtrim($field['name'], ']'),
                                                        );

                                                        // Handle nested array field names for data_get
                                                        $fieldNameForOld = str_replace(
                                                            ['[', ']'],
                                                            '.',
                                                            rtrim($field['name'], '[]'),
                                                        );
                                                        $fieldNameForOld = rtrim($fieldNameForOld, '.');

                                                        $fieldValue =
                                                            old($field['name']) ??
                                                            (isset($row) ? data_get($row, $fieldNameForOld) : '');

                                                        // For checkbox, check if value exists
                                                        if ($field['type'] === 'checkbox' && isset($row)) {
                                                            $fieldValue = data_get($row, $fieldNameForOld) == 1;
                                                        }

                                                        // For multiple select
                                                        if (
                                                            isset($field['attributes']['multiple']) &&
                                                            $field['attributes']['multiple'] &&
                                                            isset($row)
                                                        ) {
                                                            $fieldValue = is_array($fieldValue)
                                                                ? $fieldValue
                                                                : explode(',', $fieldValue);
                                                        }
                                                    @endphp

                                                    <div class="{{ $field['columnClass'] }}"
                                                        @if (isset($field['conditional']) && $field['conditional']) data-conditional-field="{{ $field['conditional']['field'] }}"
                            data-conditional-value="{{ $field['conditional']['value'] }}"
                            @if (!$field['conditional']['show'])
                                style="display: none;" @endif
                                                        @endif>
                                                        <div class="mb-0">
                                                            @if ($field['type'] !== 'checkbox')
                                                                <label
                                                                    class="form-label {{ $field['required'] ? 'required' : '' }}">
                                                                    {{ $field['label'] }}
                                                                </label>
                                                            @endif

                                                            @if ($field['type'] === 'select')
                                                                <select name="{{ $field['name'] }}"
                                                                    @foreach ($field['attributes'] as $attrKey => $attrValue)
                                        @if (!is_array($attrValue))
                                            {{ $attrKey }}="{{ $attrValue }}"
                                        @endif @endforeach>
                                                                    <option value="">Select</option>
                                                                    @if (isset($field['options']))
                                                                        @foreach ($field['options'] as $optionValue => $optionLabel)
                                                                            <option value="{{ $optionValue }}"
                                                                                @if (is_array($fieldValue) && in_array($optionValue, $fieldValue)) selected
                                                @elseif ($fieldValue == $optionValue)
                                                    selected @endif>
                                                                                {{ $optionLabel }}
                                                                            </option>
                                                                        @endforeach
                                                                    @endif
                                                                </select>
                                                            @elseif ($field['type'] === 'text')
                                                                <input type="text" name="{{ $field['name'] }}"
                                                                    @foreach ($field['attributes'] as $attrKey => $attrValue)
                                        @if (!is_array($attrValue))
                                            {{ $attrKey }}="{{ $attrValue }}"
                                        @endif @endforeach
                                                                    value="{{ $fieldValue }}">
                                                            @elseif ($field['type'] === 'file')
                                                                <input type="file"
                                                                    @foreach ($field['attributes'] as $attrKey => $attrValue)
                                        {{ $attrKey }}="{{ $attrValue }}" @endforeach>

                                                                <!-- Hidden fields for file uploads -->
                                                                <input type="hidden" name="{{ $field['name'] }}"
                                                                    id="{{ str_replace('#', '', $field['attributes']['data-hidden-id']) }}"
                                                                    value="" />

                                                                <input type="hidden"
                                                                    id="{{ str_replace('#', '', $field['attributes']['data-hidden-file']) }}"
                                                                    value="" />

                                                                {{-- @if (str_contains($field['name'], 'flyer'))
                                                                    <input type="hidden"
                                                                        name="value_proposition_details[flyer_id]"
                                                                        id="value_proposition_details_flyer_id"
                                                                        value="{{ old('value_proposition_details.flyer_id') ?? (isset($row) ? data_get($row, 'value_proposition_details.flyer_id') : '') }}">
                                                                @endif --}}

                                                                @if ($fieldValue)
                                                                    <div class="mt-2">
                                                                        <a href="{{ asset('storage/' . $fieldValue) }}"
                                                                            target="_blank" class="btn btn-sm btn-info">
                                                                            View Uploaded File
                                                                        </a>
                                                                    </div>
                                                                @endif
                                                            @elseif ($field['type'] === 'checkbox')
                                                                <div class="form-check">
                                                                    <input type="checkbox" class="form-check-input"
                                                                        name="{{ $field['name'] }}"
                                                                        @foreach ($field['attributes'] as $attrKey => $attrValue)
                                               {{ $attrKey }}="{{ $attrValue }}" @endforeach
                                                                        value="1"
                                                                        @if ($fieldValue) checked @endif>
                                                                    <label
                                                                        class="form-label label-2 ms-2 {{ $field['required'] ? 'required' : '' }}">
                                                                        {{ $field['label'] }}
                                                                    </label>
                                                                </div>
                                                            @elseif ($field['type'] === 'hidden')
                                                                <input type="hidden" name="{{ $field['name'] }}"
                                                                    @foreach ($field['attributes'] as $attrKey => $attrValue)
                                           {{ $attrKey }}="{{ $attrValue }}" @endforeach
                                                                    value="{{ $fieldValue }}">
                                                            @elseif ($field['type'] === 'html')
                                                                {!! $field['attributes']['html'] !!}
                                                            @endif

                                                            <span class="text-danger form-error font-12"
                                                                id="{{ $fieldId }}_error"></span>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            @endisset
                                        </div>
                                    @endforeach
                                    <div class="col-lg-7">
                                        <div class="action-bottom flex-row justify-content-center">
                                            <input type="hidden" name="form_status" id="form_status" value="">
                                            <button id="cancel-button" type="button"
                                                class="btn btn-secondary btn-themed mt-3">Cancel</button>
                                            <button id="signup-button" type="submit"
                                                class="btn btn-primary btn-themed mt-3">Submit</button>
                                        </div>
                                    </div>
                                @endisset
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('components.admin.popup.sms-email')
    @include('components.admin.file-upload')

    <script type="text/javascript">
        var BASE_URL = "{{ url('/') }}";
    </script>
    <script src="{{ asset('assets/js/jquery-3.7.0.min.js?ver=' . time()) }}"></script>
    <script src="{{ asset('assets/js/jquery-migrate-3.4.0.min.js?ver=' . time()) }}"></script>
    <script src="{{ asset('assets/js/script.js?ver=' . time()) }}"></script>
    <script type="text/javascript" src="{{ url('toastr/toastr.min.js?ver=' . time()) }}"></script>
    <script src="{{ asset('assets/js/crypto-js.min.js?ver=' . time()) }}"
        integrity="sha512-E8QSvWZ0eCLGk4km3hxSsNmGWbLtSCSUcewDQPQWZF6pEU8GlT8a5fF32wOl1i8ftdMhssTrF/OhyGWwonTcXA=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    @include('components.admin.bootstrap-dialog')
    <script src="{{ asset('assets/ffo-admin/js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/js/jquery-ui.js?ver=' . time()) }}"></script>
    <script src="{{ asset('assets/js/common.js?ver=' . time()) }}"></script>
    <script src="{{ asset('assets/js/validations.js?ver=' . time()) }}"></script>
    <script src="{{ asset('assets/js/mustache.min.js?ver=' . time()) }}"></script>
    <script src="{{ asset('assets/js/verification.js?ver=' . time()) }}"></script>
    <link href="{{ asset('assets/css/bootstrap.min.css?ver=' . time()) }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ url('toastr/toastr.min.css?ver=' . time()) }}">
    <script type="text/javascript" src="{{ asset('assets/js/jquery.bootstrap-duallistbox.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Handle conditional field visibility
            function handleConditionalFields() {
                document.querySelectorAll('[data-conditional-field]').forEach(function(element) {
                    const fieldName = element.getAttribute('data-conditional-field');
                    const expectedValue = element.getAttribute('data-conditional-value');
                    const field = document.querySelector(`[name="${fieldName}"]`);

                    if (field) {
                        const fieldValue = field.value;
                        const shouldShow = fieldValue === expectedValue;
                        element.style.display = shouldShow ? '' : 'none';

                        // Also hide/show required validation
                        const requiredInputs = element.querySelectorAll('[required]');
                        requiredInputs.forEach(input => {
                            input.required = shouldShow;
                        });
                    }
                });
            }

            // Listen for changes on fields that have dependent fields
            document.querySelectorAll('select, input[type="radio"], input[type="checkbox"]').forEach(function(
                field) {
                field.addEventListener('change', handleConditionalFields);
            });

            // Initial check
            handleConditionalFields();
        });
    </script>
    <script>
        var ONDC_DOMAIN_LIST = @json($domain_type ?? []);
        var ROLE_LIST = @json($roleList ?? []);
        var TRANSACTION_TYPES = @json(static_common_list($lists?->ondc_types ?? []));
        var PRODUCTION_STATUS = @json(static_common_list($lists?->production_status ?? []));
        var SERVICEABILITY_LIST = @json(static_common_list($lists?->serviceability ?? []));
        var STATES_LIST = @json(state_list() ?? []);
        var role_json = @json($roles ?? []);

        // Define the missing function
        function populateDomainDropdown(rowId, response) {
            const $select = $(`#role_row_${rowId} .ondc_category`);
            if (!$select.length) {
                console.error(`Domain dropdown not found for row ${rowId}`);
                return;
            }

            $select.empty().append('<option value="">Select</option>');

            if (ONDC_DOMAIN_LIST && ONDC_DOMAIN_LIST.length > 0) {
                ONDC_DOMAIN_LIST.forEach(item => {
                    const selected = (response.ondc_domain_mapping == item.ondc_domain_id) ? "selected" : "";
                    $select.append(`<option value="${item.id}" ${selected}>${item.name}</option>`);
                });
            }
        }

        function populateRoleDropdown(rowId, response = {}) {
            const $select = $(`#role_row_${rowId} .ondc_role_select`);

            if ($select.length === 0) {
                console.error(`Role dropdown not found for row ${rowId}`);
                return;
            }

            $select.html('<option value="">Select</option>');

            if (ROLE_LIST && ROLE_LIST.length > 0) {
                ROLE_LIST.forEach(role => {
                    const selected = (response.role == role.id) ? "selected" : "";
                    $select.append(`<option value="${role.id}" ${selected}>${role.name}</option>`);
                });
            }
        }

        function populateTransactionTypeDropdown(rowId, response) {
            const $select = $(`#role_row_${rowId} .transaction-type-select`);
            $select.empty().append('<option value="">Select</option>');

            if (TRANSACTION_TYPES) {
                Object.entries(TRANSACTION_TYPES).forEach(([key, value]) => {
                    const selected = (response.transaction_type == key) ? "selected" : "";
                    $select.append(`<option value="${key}" ${selected}>${value}</option>`);
                });
            }
        }

        function populateStatusDropdown(rowId, response) {
            const $select = $(`#role_row_${rowId} .status-select`);
            $select.empty().append('<option value="">Select</option>');

            if (PRODUCTION_STATUS) {
                Object.entries(PRODUCTION_STATUS).forEach(([key, value]) => {
                    const selected = (response.status == key) ? "selected" : "";
                    $select.append(`<option value="${key}" ${selected}>${value}</option>`);
                });
            }
        }

        function populateServiceabilityDropdown(rowId, response) {
            const $select = $(`#role_row_${rowId} .serviceability-select`);
            $select.empty().append('<option value="">Select</option>');

            if (SERVICEABILITY_LIST) {
                Object.entries(SERVICEABILITY_LIST).forEach(([key, value]) => {
                    const selected = (response.status == key) ? "selected" : "";
                    $select.append(`<option value="${key}" ${selected}>${value}</option>`);
                });
            }
        }

        function addRoleComponent(response = {}) {
            const randomId = response.random_id || response.id || Date.now();
            const source = $("#role_add_more_template").html();

            if (!source) {
                console.error("Role template not found");
                return;
            }

            Mustache.parse(source);
            const rendered = Mustache.render(source, {
                randomId: randomId,
                response: response
            });

            $("#roleDetailsContainer").append(rendered);

            // Call all population functions
            populateDomainDropdown(randomId, response);
            populateRoleDropdown(randomId, response);
            populateTransactionTypeDropdown(randomId, response);
            populateStatusDropdown(randomId, response);
            populateServiceabilityDropdown(randomId, response);

            // Initialize Select2 for multi-select fields
            // $(`#role_row_${randomId}`).select2({
            //     placeholder: "Select states",
            //     allowClear: true
            // });

            updateRoleButtons();
        }

        $(document).ready(function() {
            // Initialize Select2 for main roles field
            $('#roles').select2({
                placeholder: "Select Roles",
                allowClear: true
            });

            // Show/hide fee type fields
            $('#fee_type').on('change', function() {
                const val = $(this).val();
                if (val === 'flat_fee') {
                    $('#flat_fee_div').show();
                    $('#subscription_div').hide();
                } else if (val === 'subscription') {
                    $('#subscription_div').show();
                    $('#flat_fee_div').hide();
                } else {
                    $('#flat_fee_div').hide();
                    $('#subscription_div').hide();
                }
            });

            // Clear error messages on input
            $(document).on('focus input change', 'input, textarea, select', function() {
                const fieldId = $(this).attr('id');
                if (fieldId) {
                    $(`#${fieldId}_error`).text('');
                }
            });

            // Initialize role components
            if (role_json && role_json.length > 0) {
                $.each(role_json, function(i, res) {
                    addRoleComponent(res);
                });
            } else {
                addRoleComponent();
            }

            // Handle file uploads
            $('.upload-pdf').on('change', function() {
                const file = this.files[0];
                if (!file) return;

                const allowedTypes = ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png'];
                if (!allowedTypes.includes(file.type)) {
                    alert('Only PDF, JPG, JPEG, and PNG files are allowed.');
                    $(this).val('');
                    return;
                }

                const maxSize = 2 * 1024 * 1024; // 2MB
                if (file.size > maxSize) {
                    alert('File size should be less than 2MB.');
                    $(this).val('');
                    return;
                }

                const document_category_id = $(this).data('category-id');
                const hidden_file = $(this).data('hidden-file');
                const hidden_id = $(this).data('hidden-id');

                const formData = new FormData();
                formData.append('_token', '{{ csrf_token() }}');
                formData.append('document_category_id', document_category_id);
                formData.append('file', file);

                const $button = $(this);
                $button.prop('disabled', true);

                $.ajax({
                    url: "{{ url('upload-document-public') }}",
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.status) {
                            toastr.success(response.message);
                            $(hidden_file).val(response.data.file_name);
                            $(hidden_id).val(response.data.file_id);
                        } else {
                            toastr.error(response.message || 'Upload failed');
                            $button.val('');
                        }
                    },
                    error: function(xhr) {
                        toastr.error('File upload failed.');
                        $button.val('');
                    },
                    complete: function() {
                        $button.prop('disabled', false);
                    }
                });
            });

            // Cancel button handler
            $("#cancel-button").on("click", function() {
                if (confirm('Are you sure you want to cancel? All unsaved data will be lost.')) {
                    $("#formId")[0].reset();
                    $('.form-error').text('');
                    // Reset Select2 fields
                    $('#roles').val(null).trigger('change');
                    toastr.info('Form has been reset.');
                }
            });

            // Agreement checkbox handler
            $("#agreecheck").on("change", function() {
                if ($(this).is(":checked")) {
                    $("#agreecheck_error").text("");
                }
            });

            // Form submission
            $("#formId").on("submit", function(event) {
                event.preventDefault();

                if (!validateForm()) {
                    toastr.error('Please fill all required fields correctly');
                    return;
                }

                if (!$("#agreecheck").is(":checked")) {
                    $("#agreecheck_error").text("You must agree to the declaration");
                    toastr.error('Please agree to the declaration');
                    return;
                }

                const formData = new FormData(this);
                formData.append('_token', '{{ csrf_token() }}');
                @isset($id)
                    var url = "{{ url('/update-snp-registration/' . $id) }}";
                @else
                    var url = "{{ url('/register-network-provider') }}";
                @endisset

                $("#cover-spin").show();
                $("#signup-button").prop('disabled', true).text('Submitting...');

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(data) {
                        $("#cover-spin").hide();
                        $("#signup-button").prop('disabled', false).text('Submit');
                        console.log(data);
                        if (data.status) {
                            toastr.success(data.message || 'Registration successful!');
                            setTimeout(function() {
                                window.location.href = "{{ url('login') }}";
                            }, 2000);
                        } else {
                            if (data.errors) {
                                applyValidationErrors(data);
                                toastr.error('Please correct the errors in the form.');

                            } else {
                                toastr.error(data.message || 'Registration failed');
                            }
                        }
                    },
                    error: function(xhr) {
                        console.log(xhr.responseJSON.errors);
                        applyValidationErrors(xhr.responseJSON.errors);
                        $("#cover-spin").hide();
                        $("#signup-button").prop('disabled', false).text('Submit');
                        toastr.error('An error occurred. Please try again.');
                    }
                });
            });

            // Initialize fee type display
            $('#fee_type').trigger('change');
        });

        // Form validation function
        function validateForm() {
            let isValid = true;
            $('.form-error').text('');

            // Check required fields
            $('.required').each(function() {
                const $label = $(this);
                const $field = $label.closest('.mb-0').find('input, select, textarea');
                const fieldId = $field.attr('id');

                if ($field.length && fieldId) {
                    if ($field.is(':checkbox')) {
                        if (!$field.is(':checked')) {
                            $(`#${fieldId}_error`).text("This field is required");
                            isValid = false;
                        }
                    } else if ($field.is('select')) {
                        if ($field.val() === '' || $field.val() === null || $field.val() === undefined) {
                            $(`#${fieldId}_error`).text("This field is required");
                            isValid = false;
                        }
                    } else if ($field.is('input[type="file"]')) {
                        // File fields are optional unless they have required attribute
                        if ($field.attr('required') && !$field.val()) {
                            $(`#${fieldId}_error`).text("This field is required");
                            isValid = false;
                        }
                    } else {
                        if (!$field.val() || $field.val().trim() === '') {
                            $(`#${fieldId}_error`).text("This field is required");
                            isValid = false;
                        }
                    }
                }
            });

            // Validate email fields
            $('input[type="text"][id*="email"]').each(function() {
                const email = $(this).val();
                const fieldId = $(this).attr('id');
                if (email && !isValidEmail(email)) {
                    $(`#${fieldId}_error`).text("Please enter a valid email address");
                    isValid = false;
                }
            });

            // Validate phone numbers
            $('input.numeric[id*="phone"], input.numeric[id*="contact"]').each(function() {
                const phone = $(this).val();
                const fieldId = $(this).attr('id');
                if (phone && !isValidPhone(phone)) {
                    $(`#${fieldId}_error`).text("Please enter a valid phone number (10-15 digits)");
                    isValid = false;
                }
            });

            return isValid;
        }

        function isValidEmail(email) {
            const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return re.test(email);
        }

        function isValidPhone(phone) {
            const re = /^[0-9]{10,15}$/;
            return re.test(phone);
        }

        function applyValidationErrors(errors) {
            if (errors) {
                $.each(errors, function(field, messages) {
                    const fieldId = field.replace(/\./g, '_') + '_error';
                    $(`#${fieldId}`).text(messages[0]);
                });
            }
        }

        // Role management functions
        $(document).on("click", ".add_more_role", function() {
            addRoleComponent();
        });

        $(document).on("click", ".remove_role_row", function() {
            $(this).closest(".role_row").remove();
            updateRoleButtons();
        });

        function updateRoleButtons() {
            const $rows = $("#roleDetailsContainer .role_row");

            $rows.find(".add_more_role").hide();
            $rows.find(".delete_role_button").show();

            const $firstRow = $rows.first();
            if ($firstRow.length) {
                $firstRow.find(".add_more_role").show();
                $firstRow.find(".delete_role_button").hide();
            }
        }

        $(document).on("change", ".ondc_category", function() {
            const selectedId = $(this).val();
            const $row = $(this).closest('.role_row');
            const $mappingInput = $row.find(".ondc_mapping_input");

            if (!selectedId) {
                $mappingInput.val("");
                return;
            }

            $.ajax({
                url: "{{ url('/get-ondc-domain-id') }}",
                type: "POST",
                data: {
                    id: selectedId,
                    _token: $('meta[name="csrf-token"]').attr("content")
                },
                success: function(res) {
                    if (res.status && res.data && res.data.length > 0) {
                        $mappingInput.val(res.data[0].ondc_domain_id);
                    } else {
                        $mappingInput.val("");
                    }
                },
                error: function() {
                    $mappingInput.val("");
                }
            });
        });


        $("#commercial_model_details_fee_type").on("change", function() {
            var feeType = $(this).val();
            if (feeType === "flat_fee") {
                $(".flat_fee_field").show();
                $(".subscription_field").hide();
            } else if (feeType === "subscription") {
                $(".subscription_field").show();
                $(".flat_fee_field").hide();
            }
        });

        // Auto-fill required fields before form submission (runs on signup button click)
        $(document).on('click', '#signup-button', function() {
            // Fill label->field required pairs
            $('#formId .required').each(function() {
                var $label = $(this);
                var $field = $label.closest('.mb-0').find('input, select, textarea').first();
                if (!$field.length) return;

                // skip files
                if ($field.is(':file')) return;

                var val = $field.val();
                if (val !== null && val !== undefined && String(val).trim() !== '') return;

                // Selects: pick first non-empty option
                if ($field.is('select')) {
                    var firstOpt = $field.find('option').not('[value=""], option[value=""]').first().val();
                    if (firstOpt !== undefined) {
                        $field.val(firstOpt).trigger('change');
                    }
                    return;
                }

                var id = ($field.attr('id') || '').toLowerCase();
                var name = ($field.attr('name') || '').toLowerCase();

                // Heuristic defaults
                if (id.includes('email') || name.includes('email')) {
                    $field.val('test@example.com');
                } else if (id.match(/phone|contact|mobile/) || name.match(/phone|contact|mobile/)) {
                    $field.val('9999999999');
                } else if (id.match(/organization_id|account_number|flat_fee_amount|other_fees/) || name
                    .match(/organization_id|account_number|flat_fee_amount|other_fees/)) {
                    $field.val('123456');
                } else {
                    $field.val('Sample Value');
                }
            });

            // Ensure main roles select has a value
            if ($('#roles').length && (!$('#roles').val() || $('#roles').val().length === 0)) {
                var firstRole = $('#roles option').not('[value=""], option[value=""]').first().val();
                if (firstRole !== undefined) {
                    $('#roles').val([firstRole]).trigger('change');
                }
            }

            // Populate dynamic role rows (choose first option for each select, set serviceability)
            $('#roleDetailsContainer .role_row').each(function() {
                var $row = $(this);
                $row.find('select').each(function() {
                    var $s = $(this);
                    if (!$s.val() || $s.val() === '') {
                        var first = $s.find('option').not('[value=""], option[value=""]').first()
                            .val();
                        if (first !== undefined) {
                            $s.val(first).trigger('change');
                        }
                    }
                });

                var $service = $row.find('.serviceability-select');
                if ($service.length && (!$service.val() || $service.val().length === 0)) {
                    var firstState = $service.find('option').first().val();
                    if (firstState !== undefined) {
                        $service.val([firstState]).trigger('change');
                    }
                }
            });
        });
    </script>
    <script id="role_add_more_template" type="x-tmpl-mustache">
    <div class="role_row card p-3 mb-3" id="role_row_@{{randomId}}" style="border:1px solid #ddd; border-radius:8px;">
        <div class="row">
            <div class="col-md-4">
                <label class="form-label required">Domain / Product Type</label>
                <select name="role_selection_details[@{{randomId}}][domain]" 
                        class="form-select ondc_category"
                        data-row-id="@{{randomId}}">
                    <option value="">Select</option>
                </select>
            </div>

            <div class="col-md-4">
                <label>ONDC Domain Mapping</label>
                <input type="text" 
                    name="role_selection_details[@{{randomId}}][ondc_domain_mapping]" 
                    class="form-control ondc_mapping_input" readonly
                    value="@{{response.ondc_domain_mapping}}">
            </div>

            <div class="col-md-4">
                <label class="form-label required">Transaction Type</label>
                <select name="role_selection_details[@{{randomId}}][transaction_type]" class="form-select transaction-type-select">
                    <option value="">Select</option>
                </select>
            </div>

            <div class="col-md-4 mt-3">
                <label>Role</label>
                <select name="role_selection_details[@{{randomId}}][role]"
                        class="form-select ondc_role_select"
                        data-row-id="@{{randomId}}">
                    <option value="">Select</option>
                </select>
            </div>

            <div class="col-md-4 mt-3">
                <label>Status</label>
                <select name="role_selection_details[@{{randomId}}][status]" class="form-select status-select">
                    <option value="">Select</option>
                </select>
            </div>

            <div class="col-md-4 mt-3">
                <label>Serviceability</label>
                <select name="role_selection_details[@{{randomId}}][serviceability]" 
                        class="form-select serviceability-select">
                </select>
            </div>

            <div class="col-md-12 mt-3 d-flex justify-content-end">
                <a class="add_more_role btn btn-outline-success btn-sm me-2" 
                href="javascript:void(0)">Add</a>

                <a class="remove_role_row btn btn-outline-danger btn-sm delete_role_button"
                    data-id="@{{randomId}}"
                    href="javascript:void(0)"
                    style="display:none;">Remove</a>
            </div>
        </div>
    </div>
</script>
</body>

</html>
