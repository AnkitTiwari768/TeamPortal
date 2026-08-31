<form method="post" id="formId" class="mt-2 pt-3 formId" enctype="multipart/form-data">
    <div class="row">
        <input type="hidden" name="id" value="{{ $row['id'] ?? '' }}">

        <div class="row">
            <div class="col-lg-12">
                <p><span style="color: #db0000ff;">*</span> Fields are mandatory to fill
                    in the form.</p>
            </div>
        </div>

        <div class="row role-detail">
            <div class="col-lg-12 mb-3">
                <h2 class="form-title-heading">
                    <img src="{{ asset('assets/img/bbasic.svg') }}" alt="">
                    Role
                </h2>
            </div>

            @php
                $roles = [];
                if (isset($row['roles']) && !empty($row['roles'])) {
                    $roles = $row['roles'];
                } elseif (isset($userRoles) && !empty($userRoles)) {
                    $roles = $userRoles;
                } else {
                    $roles = [];
                }

            @endphp
            <div class="col-lg-12 mb-3">
                <div class="mb-0">
                    <div class="d-flex gap-3">
                        <label class="form-label required">
                            Role
                        </label>
                        <a class="tooltip-ins" href="#" data-bs-toggle="tooltip" title="Select your organization’s role on ONDC"><i class="fa fa-question-circle" aria-hidden="true"></i></a>
                    </div>
                    <select name="roles[]" multiple class="form-select select2 w-100" id="roles">
                        @foreach ($roleList as $role)
                            <option value="{{ $role->id }}" @if (!empty($roles) && (in_array($role->id, $roles) || in_array($role->slug, $roles))) selected @endif>
                                {{ $role->name }}
                            </option>
                        @endforeach
                    </select>


                    <span class="text-danger form-error font-12" id="roles__error"></span>
                </div>
            </div>
        </div>
        <div class="row basic-details-detail">
            <div class="col-lg-12 mb-3">
                <h2 class="form-title-heading font-20">
                    <img src="{{ asset('assets/img/bbasic.svg') }}" alt="">
                    Basic details
                </h2>
            </div>


            <div class="col-lg-4 mb-3">
                <div class="mb-0">
                    <div class="d-flex gap-3">
                        <label class="form-label required">
                            Organization ID (as on ONDC)
                        </label>
                        <a class="tooltip-ins" href="#" data-bs-toggle="tooltip" title="Enter ONDC assigned organization ID."><i class="fa fa-question-circle" aria-hidden="true"></i></a>

                    </div>
                    <input type="text" name="organization_id" class="form-control" maxlength="20"
                        placeholder="Organization ID"
                        value="{{ old('organization_id', $row['organization_id'] ?? ($snpData['organization_id'] ?? '')) }}">

                    <span class="text-danger form-error font-12" id="organization_id_error"></span>
                </div>
            </div>

            <div class="col-lg-4 mb-3">
                <div class="mb-0">
                    <div class="d-flex gap-3">
                        <label class="form-label required">
                            Organization/Legal Name
                        </label>
                        <a class="tooltip-ins" href="#" data-bs-toggle="tooltip" title="Enter registered legal name."><i class="fa fa-question-circle" aria-hidden="true"></i></a>
                    </div>
                    <input type="text" name="organization_name" class="form-control" maxlength="100"
                        placeholder="Legal Name"
                        value="{{ old('organization_name', $row['organization_name'] ?? ($snpData['organization_name'] ?? '')) }}">

                    <span class="text-danger form-error font-12" id="organization_name_error"></span>
                </div>
            </div>

            <div class="col-lg-4 mb-3">
                <div class="mb-0">
                    <div class="d-flex gap-3">
                        <label class="form-label required">
                            Official/Primary Email ID
                        </label>
                        <a class="tooltip-ins" href="#" data-bs-toggle="tooltip" title="Enter official organization email.">
                        <i class="fa fa-question-circle" aria-hidden="true"></i></a>
                    </div>
                    <input type="email" name="email" class="form-control" maxlength="100"
                        placeholder="Official/Primary Email ID"
                        value="{{ old('email', $row['email'] ?? ($snpData['email'] ?? '')) }}">

                    <span class="text-danger form-error font-12" id="email_error"></span>
                </div>
            </div>

            <div class="col-lg-4 mb-3">
                <div class="mb-0">
                    <div class="d-flex gap-3">
                        <label class="form-label required">
                            BPPId.ProviderID
                        </label>
                        <a class="tooltip-ins" href="#" data-bs-toggle="tooltip" title="Enter BPP ID">
                        <i class="fa fa-question-circle" aria-hidden="true"></i></a>
                    </div>
                    <input type="text" name="bppid_providerid" class="form-control" maxlength="100"
                        placeholder="BPPId.ProviderID"
                        value="{{ old('bppid_providerid', $row['bppid_providerid'] ?? '') }}">

                    <span class="text-danger form-error font-12" id="bppid_providerid_error"></span>
                </div>
            </div>
        </div>
        <div class="row contact-details-detail">
            <div class="col-lg-12 mb-3">
                <h2 class="form-title-heading font-20">
                    <img src="{{ asset('assets/img/bbasic.svg') }}" alt="">
                    Contact details
                </h2>
            </div>

            @php

                $contactDetails = $row['contact_details'] ?? [];

                if (isset($contactDetails['email']) && !empty($contactDetails['email'])) {
                    $primaryEmail = $contactDetails['email'];
                } elseif (isset($snpData['email']) && !empty($snpData['email'])) {
                    $primaryEmail = $snpData['email'];
                } else {
                    $primaryEmail = '';
                }

                if (isset($contactDetails['primary_contact_no']) && !empty($contactDetails['primary_contact_no'])) {
                    $primaryContact = $contactDetails['primary_contact_no'];
                } elseif (isset($snpData['mobile']) && !empty($snpData['mobile'])) {
                    $primaryContact = $snpData['mobile'];
                } else {
                    $primaryContact = '';
                }

            @endphp
            <div class="col-lg-4 mb-3">
                <div class="mb-0">
                    <div class="d-flex gap-3">
                        <label class="form-label required">
                            Official/Primary Email ID
                        </label>
                        <a class="tooltip-ins" href="#" data-bs-toggle="tooltip" title="Enter primary communication email.">
                        <i class="fa fa-question-circle" aria-hidden="true"></i></a>
                    </div>
                    <input type="email" name="contact_details_email" class="form-control" maxlength="100"
                        placeholder="Official/Primary Email ID"
                        value="{{ old('contact_details_email', $primaryEmail) }}">

                    <span class="text-danger form-error font-12" id="contact_details_email_error"></span>
                </div>
            </div>

            <div class="col-lg-4 mb-3">
                <div class="mb-0">
                    <div class="d-flex gap-3">
                        <label class="form-label required">
                            Primary Contact No
                        </label>
                        <a class="tooltip-ins" href="#" data-bs-toggle="tooltip" title="Enter main contact number.">
                        <i class="fa fa-question-circle" aria-hidden="true"></i></a>
                    </div>
                    <input type="text" name="contact_details_primary_contact_no" class="form-control" maxlength="10"
                        placeholder="Primary Contact No"
                        value="{{ old('contact_details_primary_contact_no', $primaryContact) }}">

                    <span class="text-danger form-error font-12" id="contact_details_primary_contact_no_error"></span>
                </div>
            </div>

            <div class="col-lg-4 mb-3">
                <div class="mb-0">
                    <div class="d-flex gap-3">
                        <label class="form-label ">
                            WhatsApp No/Channel
                        </label>
                        <a class="tooltip-ins" href="#" data-bs-toggle="tooltip" title="Enter WhatsApp/contact channel.">
                        <i class="fa fa-question-circle" aria-hidden="true"></i></a>
                    </div>
                    <input type="text" name="contact_details_whatsapp_no" class="form-control" maxlength="10"
                        placeholder="WhatsApp No/Channel"
                        value="{{ old('contact_details_whatsapp_no', $contactDetails['whatsapp_no'] ?? '') }}">

                    <span class="text-danger form-error font-12" id="contact_details_whatsapp_no_error"></span>
                </div>
            </div>

            <div class="col-lg-4 mb-3">
                <div class="mb-0">
                    <div class="d-flex gap-3">
                        <label class="form-label required">
                            Website
                        </label>
                        <a class="tooltip-ins" href="#" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Enter official website URL.">
                        <i class="fa fa-question-circle" aria-hidden="true"></i></a>
                    </div>
                    <input type="url" name="contact_details_website" class="form-control" maxlength="200"
                        placeholder="Website URL"
                        value="{{ old('contact_details_website', $contactDetails['website'] ?? '') }}">

                    <span class="text-danger form-error font-12" id="contact_details_website_error"></span>
                </div>
            </div>

            <div class="col-lg-4 mb-3">
                <div class="mb-0">
                    <div class="d-flex gap-3">
                        <label class="form-label ">
                            App Store Link(s)
                        </label>
                        <a class="tooltip-ins" href="#" data-bs-toggle="tooltip" title="Provide app links (if available).">
                        <i class="fa fa-question-circle" aria-hidden="true"></i></a>
                    </div>
                    <input type="url" name="contact_details_app_store_links" class="form-control"
                        maxlength="200" placeholder="App Store Link(s)"
                        value="{{ old('contact_details_app_store_links', $contactDetails['app_store_links'] ?? '') }}">

                    <span class="text-danger form-error font-12" id="contact_details_app_store_links_error"></span>
                </div>
            </div>

            <div class="col-lg-4 mb-3">
                <div class="mb-0">
                    <div class="d-flex gap-3">
                        <label class="form-label ">
                            Social Media Handles
                        </label>
                        <a class="tooltip-ins" href="#" data-bs-toggle="tooltip" title="Enter social profile links.">
                        <i class="fa fa-question-circle" aria-hidden="true"></i></a>
                    </div>
                    <input type="text" name="contact_details_social_media_handles" class="form-control"
                        maxlength="200" placeholder="Social Media Handles"
                        value="{{ old('contact_details_social_media_handles', $contactDetails['social_media_handles'] ?? '') }}">

                    <span class="text-danger form-error font-12"
                        id="contact_details_social_media_handles_error"></span>
                </div>
            </div>
        </div>
        <!-- Authorized Person Details (Single Entry) -->
        <div class="row authorised-persons-detail mb-4">
            <div class="col-lg-12 mb-3">
                <h2 class="form-title-heading font-20">
                    <img src="{{ asset('assets/img/bbasic.svg') }}" alt="">
                    Authorised Person(s)
                </h2>
            </div>

            @php
                $authPersonDetails = $row['authorized_person_details'][0] ?? [];
            @endphp

            <div class="col-lg-12">
                <div class="authorized-person-section">
                    <div class="row">
                        <div class="col-lg-4 mb-3">
                            <div class="mb-0">
                                <div class="d-flex gap-3">
                                    <label class="form-label required">Name of Authorised
                                        Person(s)</label>
                                        <a class="tooltip-ins" href="#" data-bs-toggle="tooltip" title="Enter authorized person name.">
                                        <i class="fa fa-question-circle" aria-hidden="true"></i></a>
                                </div>
                                <input type="text" name="authorized_person_details_name" class="form-control"
                                    maxlength="80" placeholder="Name of Authorised Person(s)"
                                    value="{{ old('authorized_person_details.name', $authPersonDetails['name'] ?? ($snpData['snp_name'] ?? '')) }}">
                                <span class="text-danger form-error font-12"
                                    id="authorized_person_details_name_error"></span>
                            </div>
                        </div>

                        <div class="col-lg-4 mb-3">
                            <div class="mb-0">
                                <div class="d-flex gap-3">
                                    <label class="form-label required">Role/Designation</label>
                                    <a class="tooltip-ins" href="#" data-bs-toggle="tooltip" title="Enter designation.">
                                    <i class="fa fa-question-circle" aria-hidden="true"></i></a>
                                </div>
                                <input type="text" name="authorized_person_details_designation"
                                    class="form-control" maxlength="100"
                                    placeholder="Designation Authorised Person(s)"
                                    value="{{ old('authorized_person_details.designation', $authPersonDetails['designation'] ?? ($snpData['designation'] ?? '')) }}">
                                <span class="text-danger form-error font-12"
                                    id="authorized_person_details_designation_error"></span>
                            </div>
                        </div>

                        <div class="col-lg-4 mb-3">
                            <div class="mb-0">
                                <div class="d-flex gap-3">
                                    <label class="form-label required">Phone/WhatsApp
                                        Number</label>
                                    <a class="tooltip-ins" href="#" data-bs-toggle="tooltip" title="Enter contact number.">
                                    <i class="fa fa-question-circle" aria-hidden="true"></i></a>
                                </div>
                                <input type="text" name="authorized_person_details_phone" class="form-control"
                                    maxlength="10" placeholder="Phone/WhatsApp Number"
                                    value="{{ old('authorized_person_details.phone', $authPersonDetails['phone'] ?? '') }}">
                                <span class="text-danger form-error font-12"
                                    id="authorized_person_details_phone_error"></span>
                            </div>
                        </div>

                        <div class="col-lg-4 mb-3">
                            <div class="mb-0">
                                <div class="d-flex gap-3">
                                    <label class="form-label required">Email Address</label>
                                    <a class="tooltip-ins" href="#" data-bs-toggle="tooltip" title="Enter Email ID of Authorized Person.">
                                    <i class="fa fa-question-circle" aria-hidden="true"></i></a>
                                </div>
                                <input type="email" name="authorized_person_details_email" class="form-control"
                                    maxlength="80" placeholder="Email Address"
                                    value="{{ old('authorized_person_details.email', $authPersonDetails['email'] ?? '') }}">
                                <span class="text-danger form-error font-12"
                                    id="authorized_person_details_email_error"></span>
                            </div>
                        </div>

                        <div class="col-lg-4 mb-3">
                            <div class="mb-0">
                                <div class="d-flex gap-3">
                                    <label class="form-label required">Authorized Certificate</label>
                                    <a class="tooltip-ins" href="#" data-bs-toggle="tooltip" title="Upload authorization proof.">
                                    <i class="fa fa-question-circle" aria-hidden="true"></i></a>
                                </div>
                                <input type="file" class="form-control upload-pdf"
                                    data-hidden-id="#authorized_person_details_certificate_id"
                                    data-hidden-file="#authorized_person_details_certificate"
                                    data-category-id="authorized_person_certificate"
                                    id="authorized_person_certificate_file" accept="application/pdf">

                                <!-- Hidden fields for file uploads -->
                                <input type="hidden" name="authorized_person_details_certificate"
                                    id="authorized_person_details_certificate_id"
                                    value="{{ old('authorized_person_details.certificate_id', $authPersonDetails['certificate_id'] ?? '') }}">

                                <input type="hidden" id="authorized_person_details_certificate" value="">
                                <span class="text-danger form-error font-12"
                                    id="authorized_person_details_certificate_error"></span>
                            </div>
                        </div>

                        @isset($row['authorized_person_certificate'])
                            <div class="col-md-4">
                                <label><strong>Authorized Certificate:</strong></label>
                                <a href="{{ $row['authorized_person_certificate'] ?? '' }}"
                                    download="{{ $row['authorized_person_certificate_name'] ?? '' }}">
                                    {{ $row['authorized_person_certificate_name'] ?? '' }}
                                </a>
                            </div>
                        @endisset
                    </div>
                </div>
            </div>
        </div>

        <div class="row configuration-details-detail">
            <div class="col-lg-12 mb-3">
                <h2 class="form-title-heading font-20">
                    <img src="{{ asset('assets/img/bbasic.svg') }}" alt="">
                    Configuration details
                </h2>
            </div>
            @php
                $configDetails = $row['configuration_details'] ?? [];
            @endphp

            <div class="col-lg-4 mb-3">
                <div class="mb-0">
                    <div class="d-flex gap-3">
                        <label class="form-label required">
                            GST Number
                        </label>
                        <a class="tooltip-ins" href="#" data-bs-toggle="tooltip" title="Enter GST number.">
                        <i class="fa fa-question-circle" aria-hidden="true"></i></a>
                    </div>
                    <input type="text" name="configuration_details_gst_number" class="form-control"
                        maxlength="15" placeholder="GST Number"
                        value="{{ old('configuration_details_gst_number', $configDetails['gst_number'] ?? ($snpData['gst_number'] ?? '')) }}">

                    <span class="text-danger form-error font-12" id="configuration_details_gst_number_error"></span>
                </div>
            </div>

            <div class="col-lg-4 mb-3">
                <div class="mb-0">
                    <div class="d-flex gap-3">
                        <label class="form-label required">
                            PAN
                        </label>
                        <a class="tooltip-ins" href="#" data-bs-toggle="tooltip" title="Enter PAN.">
                        <i class="fa fa-question-circle" aria-hidden="true"></i></a>
                    </div>
                    <input type="text" name="configuration_details_pan" class="form-control" maxlength="10"
                        placeholder="PAN"
                        value="{{ old('configuration_details_pan', $configDetails['pan'] ?? ($snpData['pan'] ?? '')) }}">

                    <span class="text-danger form-error font-12" id="configuration_details_pan_error"></span>
                </div>
            </div>

            <div class="col-lg-4 mb-3">
                <div class="mb-0">
                    <div class="d-flex gap-3">
                        <label class="form-label ">
                            CIN
                        </label>
                        <a class="tooltip-ins" href="#" data-bs-toggle="tooltip" title="Enter CIN (if applicable).">
                        <i class="fa fa-question-circle" aria-hidden="true"></i></a>
                    </div>
                    <input type="text" name="configuration_details_cin" class="form-control" maxlength="21"
                        placeholder="CIN"
                        value="{{ old('configuration_details_cin', $configDetails['cin'] ?? '') }}">

                    <span class="text-danger form-error font-12" id="configuration_details_cin_error"></span>
                </div>
            </div>

            <div class="col-lg-4 mb-3">
                <div class="mb-0">
                    <div class="d-flex gap-3">
                        <label class="form-label ">
                            Startup ID/DPIIT Recognition
                        </label>
                        <a class="tooltip-ins" href="#" data-bs-toggle="tooltip" title="Enter DPIIT number (if any).">
                        <i class="fa fa-question-circle" aria-hidden="true"></i></a>
                    </div>
                    <input type="text" name="configuration_details_startup_id" class="form-control"
                        maxlength="50" placeholder="Startup ID/DPIIT Recognition"
                        value="{{ old('configuration_details_startup_id', $configDetails['startup_id'] ?? '') }}">

                    <span class="text-danger form-error font-12" id="configuration_details_startup_id_error"></span>
                </div>
            </div>

            <div class="col-lg-4 mb-3">
                <div class="mb-0">
                    <div class="d-flex gap-3">
                        <label class="form-label ">
                            FSSAI Number
                        </label>
                        <a class="tooltip-ins" href="#" data-bs-toggle="tooltip" title="Enter FSSAI license (if applicable).">
                        <i class="fa fa-question-circle" aria-hidden="true"></i></a>
                    </div>
                    <input type="text" name="configuration_details_fssai_number" class="form-control"
                        maxlength="14" placeholder="FSSAI Number"
                        value="{{ old('configuration_details_fssai_number', $configDetails['fssai_number'] ?? '') }}">

                    <span class="text-danger form-error font-12" id="configuration_details_fssai_number_error"></span>
                </div>
            </div>

            <div class="col-lg-4 mb-3">
                <div class="mb-0">
                    <div class="d-flex gap-3">
                        <label class="form-label ">
                            IEC Number
                        </label>
                        <a class="tooltip-ins" href="#" data-bs-toggle="tooltip" title="Enter IEC (if applicable).">
                        <i class="fa fa-question-circle" aria-hidden="true"></i></a>
                    </div>
                    <input type="text" name="configuration_details_iec_number" class="form-control"
                        maxlength="10" placeholder="IEC Number"
                        value="{{ old('configuration_details_iec_number', $configDetails['iec_number'] ?? '') }}">

                    <span class="text-danger form-error font-12" id="configuration_details_iec_number_error"></span>
                </div>
            </div>
        </div>
        <div class="row bank-details-detail">
            <div class="col-lg-12 mb-3">
                <h2 class="form-title-heading font-20">
                    <img src="{{ asset('assets/img/bbasic.svg') }}" alt="">
                    Bank details
                </h2>
            </div>

            @php
                $bankDetails = $row['bank_details'] ?? [];
            @endphp
            <div class="col-lg-4 mb-3">
                <div class="mb-0">
                    <div class="d-flex gap-3">
                        <label class="form-label required">
                            Bank Name
                        </label>
                        <a class="tooltip-ins" href="#" data-bs-toggle="tooltip" title="Enter bank name.">
                        <i class="fa fa-question-circle" aria-hidden="true"></i></a>
                    </div>
                    <input type="text" name="bank_details_bank_name" class="form-control" maxlength="80"
                        placeholder="Bank Name"
                        value="{{ old('bank_details_bank_name', $bankDetails['bank_name'] ?? ($snpData['bank_name'] ?? '')) }}">

                    <span class="text-danger form-error font-12" id="bank_details_bank_name_error"></span>
                </div>
            </div>

            <div class="col-lg-4 mb-3">
                <div class="mb-0">
                    <div class="d-flex gap-3">
                        <label class="form-label required">
                            IFSC CODE
                        </label>
                        <a class="tooltip-ins" href="#" data-bs-toggle="tooltip" title="Enter IFSC code.">
                        <i class="fa fa-question-circle" aria-hidden="true"></i></a>
                    </div>
                    <input type="text" name="bank_details_ifsc_code" class="form-control" maxlength="11"
                        placeholder="IFSC CODE"
                        value="{{ old('bank_details_ifsc_code', $bankDetails['ifsc_code'] ?? '') }}">

                    <span class="text-danger form-error font-12" id="bank_details_ifsc_code_error"></span>
                </div>
            </div>

            <div class="col-lg-4 mb-3">
                <div class="mb-0">
                    <div class="d-flex gap-3">
                        <label class="form-label required">
                            A/C No
                        </label>
                        <a class="tooltip-ins" href="#" data-bs-toggle="tooltip" title="Enter account number.">
                        <i class="fa fa-question-circle" aria-hidden="true"></i></a>
                    </div>
                    <input type="text" name="bank_details_account_number" class="form-control integer"
                        maxlength="18" placeholder="A/C No"
                        value="{{ old('bank_details_account_number', $bankDetails['account_number'] ?? ($snpData['account_no'] ?? '')) }}">

                    <span class="text-danger form-error font-12" id="bank_details_account_number_error"></span>
                </div>
            </div>
        </div>
        <div class="row role-selection-defined-below-detail">
            <div class="col-lg-12 mb-3">
                <h2 class="form-title-heading font-20">
                    <img src="{{ asset('assets/img/bbasic.svg') }}" alt="">
                    Role Selection (defined below)
                </h2>
            </div>


            <div class="col-lg-12 mb-3">
                <div class="mb-0">
                    <label class="form-label ">

                    </label>

                    <div id="roleDetailsContainer">

                    </div>

                    <span class="text-danger form-error font-12" id="role_selection_heading_error"></span>
                </div>
            </div>
        </div>
        <div class="row value-proposition-for-mses-detail">
            <div class="col-lg-12 mb-3">
                <h2 class="form-title-heading font-20">
                    <img src="{{ asset('assets/img/bbasic.svg') }}" alt="">
                    Value Proposition for MSEs
                </h2>
            </div>
            @php
                $valuePropDetails = $row['value_proposition_details'] ?? [];
            @endphp

            <div class="row d-flex align-items-end">
                <div class="col-lg-4 mb-3">
                    <div class="mb-0">
                        <div class="d-flex gap-3">
                            <label class="form-label required">
                                Short Description
                            </label>
                            <a class="tooltip-ins" href="#" data-bs-toggle="tooltip" title="Brief about services.">
                            <i class="fa fa-question-circle" aria-hidden="true"></i></a>
                        </div>
                        <input type="text" name="value_proposition_details_short_description" class="form-control"
                            placeholder="Short Description"
                            value="{{ old('value_proposition_details_short_description', $valuePropDetails['short_description'] ?? '') }}">

                        <span class="text-danger form-error font-12"
                            id="value_proposition_details_short_description_error"></span>
                    </div>
                </div>

                <div class="col-lg-4 mb-3">
                    <div class="mb-0">
                        <div class="d-flex gap-3">
                            <label class="form-label">
                                What additional service do you offer MSMEs beyond connecting them to
                                ONDC
                            </label>
                            <a class="tooltip-ins" href="#" data-bs-toggle="tooltip" title="Extra services offered.">
                            <i class="fa fa-question-circle" aria-hidden="true"></i></a>
                        </div>
                        <input type="text" name="value_proposition_details_additional_service" class="form-control"
                            placeholder="Enter additional service"
                            value="{{ old('value_proposition_details_additional_service', $valuePropDetails['additional_service'] ?? ($valuePropDetails['additional_services'] ?? '')) }}">

                        <span class="text-danger form-error font-12"
                            id="value_proposition_details_additional_service_error"></span>
                    </div>
                </div>

                <div class="col-lg-4 mb-3">
                    <div class="mb-0">
                        <div class="d-flex gap-3">
                            <label class="form-label required">
                                Language Supported
                            </label>
                            <a class="tooltip-ins" href="#" data-bs-toggle="tooltip" title="Select preferred language.">
                            <i class="fa fa-question-circle" aria-hidden="true"></i></a>
                        </div>
                        <select name="value_proposition_details_language_supported" class="form-select">
                            <option value="">Select</option>
                            @foreach ($lang as $language)
                                <option value="{{ $language->id }}"
                                    {{ old('value_proposition_details_language_supported', $valuePropDetails['language_supported'] ?? '') == $language->id ? 'selected' : '' }}>
                                    {{ $language->name }}
                                </option>
                            @endforeach
                        </select>

                        <span class="text-danger form-error font-12"
                            id="value_proposition_details_language_supported_error"></span>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-4 mb-3">
                    <div class="mb-0">
                        <div class="d-flex gap-3">
                            <label class="form-label">
                                Team Scheme landing page
                            </label>
                            <a class="tooltip-ins" href="#" data-bs-toggle="tooltip" title="Enter page URL (if any).">
                            <i class="fa fa-question-circle" aria-hidden="true"></i></a>
                        </div>
                        <input type="url" name="value_proposition_details_team_scheme_landing_page"
                            class="form-control" placeholder="Team Scheme landing page"
                            value="{{ old('value_proposition_details_team_scheme_landing_page', $valuePropDetails['team_scheme_landing_page'] ?? '') }}">

                        <span class="text-danger form-error font-12"
                            id="value_proposition_details_team_scheme_landing_page_error"></span>
                    </div>
                </div>

                <div class="col-lg-4 mb-3">
                    <div class="mb-0">
                        <div class="d-flex gap-3">
                            <label class="form-label">
                                Short Video Pitch
                            </label>
                            <a class="tooltip-ins" href="#" data-bs-toggle="tooltip" title="Enter video link.">
                            <i class="fa fa-question-circle" aria-hidden="true"></i></a>
                        </div>
                        <input type="url" name="value_proposition_details_short_video_pitch" class="form-control"
                            placeholder="Short Video Pitch URL"
                            value="{{ old('value_proposition_details_short_video_pitch', $valuePropDetails['short_video_pitch'] ?? '') }}">

                        <span class="text-danger form-error font-12"
                            id="value_proposition_details_short_video_pitch_error"></span>
                    </div>
                </div>

                <div class="col-lg-4 mb-3">
                    <div class="mb-0">
                        <div class="d-flex gap-3">
                            <label class="form-label">
                                Flyer / Presentation
                            </label>
                            <a class="tooltip-ins" href="#" data-bs-toggle="tooltip" title="Upload document.">
                            <i class="fa fa-question-circle" aria-hidden="true"></i></a>
                        </div>
                        <input type="file" class="form-control upload-pdf"
                            data-hidden-id="#value_proposition_details_flyer_id"
                            data-hidden-file="#value_proposition_details_flyer" data-category-id="flyer_presentation"
                            id="flyer_presentation_file" accept=".jpg,.jpeg,.png,.pdf">

                        <!-- Hidden fields for file uploads -->
                        <input type="hidden" name="value_proposition_details_flyer"
                            id="value_proposition_details_flyer_id"
                            value="{{ old('value_proposition_details_flyer', $valuePropDetails['flyer_id'] ?? '') }}">


                        <input type="hidden" id="value_proposition_details_flyer" value="">




                        <span class="text-danger form-error font-12" id="value_proposition_details_flyer_error"></span>
                    </div>
                </div>
            </div>
            @isset($row['flyer_document'])
                <div class="col-md-4">
                    <label><strong>Authorized Certificate:</strong></label>
                    <a href="{{ $row['flyer_document'] ?? '' }}" download="{{ $row['flyer_document_name'] ?? '' }}">
                        {{ $row['flyer_document_name'] ?? '' }}
                    </a>
                </div>
            @endisset
        </div>
        <div class="row commercial-model-detail">
            <div class="col-lg-12 mb-3">
                <h2 class="form-title-heading font-20">
                    <img src="{{ asset('assets/img/bbasic.svg') }}" alt="">
                    Commercial Model
                </h2>
            </div>

            @php
                $commModelDetails = $row['commercial_model_details'] ?? [];
            @endphp
            <div class="col-lg-4 mb-3">
                <div class="mb-0">
                    <div class="d-flex gap-3">
                        <label class="form-label required">
                            Do you Charge a fee
                        </label>
                        <a class="tooltip-ins" href="#" data-bs-toggle="tooltip" title="Select Yes/No.">
                        <i class="fa fa-question-circle" aria-hidden="true"></i></a>
                    </div>
                    <select name="commercial_model_details_fee_charge" class="form-select" id="fee_charge">
                        <option value="">Select</option>
                        <option value="yes"
                            {{ old('commercial_model_details_fee_charge', $commModelDetails['fee_charge'] ?? '') == 'yes' ? 'selected' : '' }}>
                            Yes</option>
                        <option value="no"
                            {{ old('commercial_model_details_fee_charge', $commModelDetails['fee_charge'] ?? '') == 'no' ? 'selected' : '' }}>
                            No</option>
                    </select>

                    <span class="text-danger form-error font-12"
                        id="commercial_model_details_fee_charge_error"></span>
                </div>
            </div>

            <div class="col-lg-4 mb-3" id="feeTypeContainer" style="display:none;">
                <div class="mb-0">
                    <div class="d-flex gap-3">
                        <label class="form-label">
                            Flat fee/subscription
                        </label>
                        <a class="tooltip-ins" href="#" data-bs-toggle="tooltip" title="Enter offers (if any).">
                        <i class="fa fa-question-circle" aria-hidden="true"></i></a>
                    </div>
                    <select name="commercial_model_details_fee_type" class="form-select" id="fee_type">
                        <option value="">Select</option>
                        <option value="flat_fee"
                            {{ old('commercial_model_details_fee_type', $commModelDetails['fee_type'] ?? '') == 'flat_fee' ? 'selected' : '' }}>
                            Flat fee</option>
                        <option value="subscription"
                            {{ old('commercial_model_details_fee_type', $commModelDetails['fee_type'] ?? '') == 'subscription' ? 'selected' : '' }}>
                            Subscription</option>
                    </select>

                    <span class="text-danger form-error font-12" id="commercial_model_details_fee_type_error"></span>
                </div>
            </div>

            <div class="col-lg-4 mb-3 flat_fee_field" id="flat_fee_div">
                <div class="mb-0">
                    <label class="form-label ">
                        INR Amount
                    </label>

                    <input type="text" name="commercial_model_details_flat_fee" class="form-control integer"
                        placeholder="INR Amount"
                        value="{{ old('commercial_model_details_flat_fee', $commModelDetails['flat_fee'] ?? '') }}">

                    <span class="text-danger form-error font-12" id="commercial_model_details_flat_fee_error"></span>
                </div>
            </div>

            <div class="col-lg-4 mb-3 subscription_field" id="subscription_div">
                <div class="mb-0">
                    <label class="form-label ">
                        Select Subscription
                    </label>

                    <select name="commercial_model_details_subscription" class="form-select">
                        <option value="">Select</option>
                        <option value="annual"
                            {{ old('commercial_model_details_subscription', $commModelDetails['subscription'] ?? '') == 'annual' ? 'selected' : '' }}>
                            Annual</option>
                        <option value="monthly"
                            {{ old('commercial_model_details_subscription', $commModelDetails['subscription'] ?? '') == 'monthly' ? 'selected' : '' }}>
                            Monthly</option>
                        <option value="one-time"
                            {{ old('commercial_model_details_subscription', $commModelDetails['subscription'] ?? '') == 'onetime' ? 'selected' : '' }}>
                            One time</option>
                    </select>

                    <span class="text-danger form-error font-12"
                        id="commercial_model_details_subscription_error"></span>
                </div>
            </div>

            <div class="col-lg-4 mb-3" id="commissionPerTransactionContainer" style="display:none;">
                <div class="mb-0">
                    <label class="form-label required">
                        Commission Per Transaction
                    </label>

                    <input type="text" name="commercial_model_details_commission_per_transaction"
                        class="form-control" placeholder="Commission Per Transaction"
                        value="{{ old('commercial_model_details_commission_per_transaction', $commModelDetails['commission_per_transaction'] ?? '') }}">
                    <span class="text-danger form-error font-12"
                        id="commercial_model_details_commission_per_transaction_error"></span>
                </div>
            </div>

            <div class="col-lg-4 mb-3" id="otherFeesContainer" style="display:none;">
                <div class=" mb-0">
                    <label class="form-label">
                        Other Fees
                    </label>

                    <input type="number" name="commercial_model_details_other_fees" class="form-control"
                        placeholder="Other Fees"
                        value="{{ old('commercial_model_details_other_fees', $commModelDetails['other_fees'] ?? '') }}">

                    <span class="text-danger form-error font-12"
                        id="commercial_model_details_other_fees_error"></span>
                </div>
            </div>

            <div class="col-lg-4 mb-3" id="specialOfferContainer" style="display:none;">
                <div class="mb-0">
                    <label class="form-label">
                        Special Offers/Discounts/Add-ons?
                    </label>

                    <input type="text" name="commercial_model_details_special_offer" class="form-control"
                        placeholder="Special Offers/Discounts/Add-ons?"
                        value="{{ old('commercial_model_details_special_offer', $commModelDetails['special_offers'] ?? '') }}">

                    <span class="text-danger form-error font-12"
                        id="commercial_model_details_special_offer_error"></span>
                </div>
            </div>

            <div class="col-lg-4 mb-3">
                <div class="mb-0">
                    <div class="d-flex gap-3">
                        <label class="form-label">
                            Special Offers for Team scheme
                        </label>
                         <a class="tooltip-ins" href="#" data-bs-toggle="tooltip" title="Enter offers (if any).">
                        <i class="fa fa-question-circle" aria-hidden="true"></i></a>
                    </div>
                    <input type="text" name="commercial_model_details_team_scheme_offers" class="form-control"
                        placeholder="Special Offers for Team scheme"
                        value="{{ old('commercial_model_details_team_scheme_offers', $commModelDetails['team_scheme_offers'] ?? '') }}">

                    <span class="text-danger form-error font-12"
                        id="commercial_model_details_team_scheme_offers_error"></span>
                </div>
            </div>

            <div class="col-lg-4 mb-3">
                <div class="mb-0">
                    <div class="d-flex gap-3">
                        <label class="form-label required">
                            Link to Commercial Model
                        </label>
                        <a class="tooltip-ins" href="#" data-bs-toggle="tooltip" title="Enter pricing link.">
                        <i class="fa fa-question-circle" aria-hidden="true"></i></a>
                    </div>
                    <input type="text" name="commercial_model_details_commercial_model_link" class="form-control"
                        placeholder="Link to Commercial Model"
                        value="{{ old('commercial_model_details_commercial_model_link', $commModelDetails['commercial_model_link'] ?? '') }}">

                    <span class="text-danger form-error font-12"
                        id="commercial_model_details_commercial_model_link_error"></span>
                </div>
            </div>
        </div>
        <div class="row declaration-detail">
            <div class="col-lg-12 mb-3">
                <h2 class="form-title-heading font-20">
                    <img src="{{ asset('assets/img/bbasic.svg') }}" alt="">
                    Declaration
                </h2>
            </div>


            <div class="col-lg-12 mb-3">
                <div class="mb-0">
                    
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" name="declaration" id="agreecheck"
                            value="1"
                            {{ old('declaration', isset($row['id']) ? 'checked' : '') ? 'checked' : '' }}>
                            <div class="d-flex gap-3">
                        <label class="form-label label-2 ms-2 required">
                            I/ We hereby declare that the details furnished above are true and
                            correct to the best of my/ our knowledge and belief. If the above
                            information is found to be incorrect / misleading/ false,
                            appropriate action as per the laws may be taken against me/ us. I/
                            We hereby authorize NSIC/ ONDC to share the relevant details for the
                            purpose of scheme administration and support, in compliance with
                            applicable laws. I / We hereby declare that I / We have read the
                            Privacy Policy, Terms and Conditions, Disclaimer and Data Sharing
                            Policy, Operating guidelines of TEAM Initiative, SOPs of TEAM
                            Initiative and abide by it. NSIC reserves the right to change/ amend
                            the SOPs with the approval of the Ministry of MSME as per the policy
                            &amp; procedural requirement as and when warranted and without
                            giving any notice.
                        </label>
                        
                        <a class="tooltip-ins" href="#" data-bs-toggle="tooltip" title="Confirm details are correct.">
                        <i class="fa fa-question-circle" aria-hidden="true"></i></a>
                    </div>
                    </div>

                    <span class="text-danger form-error font-12" id="declaration_error"></span>
                </div>
            </div>
        </div>
        <div class="col-lg-7">
            <div class="action-bottom flex-row justify-content-center">
                <input type="hidden" name="form_status" id="form_status" value="">
                <button id="cancel-button" type="button" class="btn btn-secondary btn-themed mt-3">Cancel</button>
                <button id="signup-button" type="submit" class="btn btn-primary btn-themed mt-3">Submit</button>
            </div>
        </div>

    </div>
</form>
