@extends('components.admin.layout')
@section('page-content')
    <div class="container-fluid mt-2">
        <div class="row">
            <div class="col-md-12">
                <div class="card shadow mb-4">

                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-primary">{{ $title }}</h6>
                        @include('components.admin.buttons.back-button')
                    </div>

                    <div class="card-body">
                        <div class="row g-3">

                            @php
                                $basicDetails = [
                                    'organization_name'   => 'Organization Name',
                                    'entity_type'         => 'Entity Type',
                                    'entity_email'        => 'Entity Email',
                                    'number_of_members'   => 'Number Of Members',
                                    'registration_number' => 'Registration Number',
                                    'website'             => 'Website',
                                    'contact_number'      => 'Contact Number',
                                    'pan_number'          => 'PAN Number',
                                    'complete_address'    => 'Complete Address',
                                    'district_name'       => 'District Name',
                                    'state_name'          => 'State Name',
                                    'statusWithLabel'     => 'Status',
                                ];

                                $contactFields = [
                                    'name'        => 'Contact Person Name',
                                    'designation' => 'Designation',
                                    'phone'       => 'Contact Person Phone',
                                    'email'       => 'Contact Person Email',
                                ];
                            @endphp

                            <div class="col-12">
                                <h4>Basic Details</h4>
                            </div>

                            @foreach($basicDetails as $key => $label)
                                @if(!empty($data[$key]))
                                    <div class="col-md-4 mb-3">
                                        <strong>{{ $label }}:</strong><br>

                                        @if($key === 'statusWithLabel')
                                            {!! $data[$key] !!}
                                        @else
                                            {{ $data[$key] }}
                                        @endif
                                    </div>
                                @endif
                            @endforeach

                            @if(!empty($data['authorization_document']))
                                <div class="col-md-4 mb-3">
                                    <strong>Authorized Certificate:</strong><br>
                                    <a href="{{ asset('storage/app/' . $data['authorization_document']) }}" target="_blank">
                                        View File
                                    </a>
                                </div>
                            @endif

                            <div class="col-12 mt-3">
                                <h4>Contact Person Details</h4>
                            </div>

                            @php $contact = $data['contact_person'] ?? []; @endphp

                            @foreach($contactFields as $key => $label)
                                @if(!empty($contact[$key]))
                                    <div class="col-md-4 mb-3">
                                        <strong>{{ $label }}:</strong><br>
                                        {{ $contact[$key] }}
                                    </div>
                                @endif
                            @endforeach

                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection
