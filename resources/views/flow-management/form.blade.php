@extends('components.admin.layout')

@section('page-content')
    <div class="container-fluid px-4 py-4">
        <div class="row">
            <div class="col-lg-12 col-md-12">
                <div class="card container-main-card">
                    <div class="card-header d-flex">
                        <div class="heading">
                            <h1>
                                @if (!empty($row->id))
                                    Edit WorkFlow
                                @else
                                    Add WorkFlow
                                @endif
                            </h1>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="#">Home</a></li>
                                    <li class="breadcrumb-item"><a href="#">
                                            @if (!empty($row->id))
                                                Edit WorkFlow
                                            @else
                                                Add WorkFlow
                                            @endif
                                        </a></li>
                                </ol>
                            </nav>
                        </div>
                        <div class="action-header ms-auto">
                            <!-- split button -->
                            <div class="btn-group drop-btn">
                                @include('components.admin.buttons.back-button')
                            </div>
                        </div>

                    </div>
                    <div class="card-body pt-1">
                        <form id="form">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="required">{{ __('Workflow Type') }}</label>
                                    {{ Form::select('workflow_type_id', $workflowTypes ?? [], $row['workflow_type_id'] ?? null, ['class' => 'form-control', 'id' => 'workflow_type_id']) }}
                                    <span class="text-danger form-error" id="workflow_type_id_error"></span>
                                </div>
                            </div>
                            <table class="table table-themed table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th>Level</th>
                                        <th>Role</th>
                                        <th>User</th>
                                        <th>Action</th>
                                        <th>{{ __('national_application.add-remove') }}</th>
                                    </tr>
                                </thead>
                                <tbody id="container_personality">
                                    <!-- Add rows dynamically using the template -->
                                </tbody>
                            </table>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="required">{{ __('menu.status') }}</label>
                                    {{ Form::select('is_active', status_list(), $row['is_active'] ?? null, ['class' => 'form-control', 'id' => 'is_active']) }}
                                    <span class="text-danger form-error" id="is_active_error"></span>
                                </div>
                            </div>
                            @include('components.admin.buttons.submit-button')
                            @include('components.admin.buttons.cancel-button')
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script id="add_more_personality_template" type="x-tmpl-mustache">
    <tr class="delete_personality_row" id="delete_personality_row@{{randomId}}">
	
	
	    <input type="hidden" class="form-control" name="flow[@{{randomId}}][id]" id="work_flow_id" value="@{{response.id}}"> 
        <td> 
            <input type="text" class="form-control" name="flow[@{{randomId}}][level]" id="level" value="@{{response.level}}"> 
            <div class="text-danger form-error" id="Level_@{{randomId}}_error"></div>
        </td> 
        <td>  
             {!! Form::select("flow[@{{randomId}}][role_id]", $roles, null, ['class' => 'form-select', 'id' => 'role_id@{{randomId}}', 'onchange' => "getUsersByRoleId('@{{randomId}}')"]) !!}
            <div class="text-danger form-error" id="Role_@{{randomId}}_error"></div>
        </td> 
        <td> 
		   <div id="userLists@{{randomId}}">Select User</div>
        </td> 
        <td> 
		   <div id="userPermissions@{{randomId}}">Select Permissions</div>
        </td> 
		
        <td>
         <a class="delete_personality_row_addMore" id="add_more_personality" href="javascript:">  <img src="{{url('assets/img-new/add-btn.svg')}}"></a>
            <div class="delete_personality_button text-right" style="display:none; margin-top: 10px;" id="component_more_1_@{{randomId}}">
                  <a class="remove_personality_component_more@{{randomId}}" id="remove_personality_component_more@{{randomId}}" href="javascript:">
                   <img src="{{url('assets/img-new/dlt-btn.svg')}}">
                </a>
            </div>
        </td>
    </tr>
</script>
    <script>
        var personalities_json = <?php echo $allRows ?? '{}'; ?>;
        var personalities_details = (personalities_json && personalities_json.length > 0) ? personalities_json : '';
        var counter = 0;

        function addComponent(containerId, templateId, clickButtonId, deleteButtonClass, deleteRowPrefix, componentType,
            response) {
            var randomId = Date.now();

            function componentFunction(randomId, counter, response) {
                //console.log(response);
                var source = $("#" + templateId).html();
                Mustache.parse(source);
                var rendered = Mustache.render(source, {
                    randomId: randomId,
                    counter: counter,
                    response: response,
                });

                $("#" + containerId).append(rendered);

                console.log(response);

                //$(`#role_id${randomId}`).val(response.role_id).trigger('change');
                $(`#role_id${randomId}`).val(response.role_id);
                enableSubmit();

                enableDeleteButton(deleteButtonClass, deleteRowPrefix, componentType);
                deleteComponentMore(randomId, deleteRowPrefix, componentType, deleteButtonClass);


                getUsersByRoleId(randomId, response.role_id, response.permissions, response.user_id);
            }

            if (response != null && response.length) {
                $.each(response, function(key, responseData) {
                    componentFunction(key, counter, responseData);
                });
            } else {
                componentFunction(randomId, counter, response);
            }

            $("#" + clickButtonId).click(function() {
                randomId = Date.now();
                //console.log(".noc_document"+randomId+"_file_link");
                componentFunction(randomId, counter, response);
                $(".noc_document" + randomId + "_file_link").html(' ');
            });

            $(document).on('focus', '.form-control, .form-select', function() {
                // Find the closest parent 'td' and then find the '.form-error' element within it
                $(this).closest('td').find('.form-error').text('');
            });
        }


        function getUsersByRoleId(randomId, roleId, permissionId, userId) {

            var roleId = $("#role_id" + randomId).val();
            if (roleId != '') {
                //userLists
                $.ajax({
                    url: "{{ route('get-users') }}",
                    method: "GET",
                    data: {
                        roleId: roleId,
                        userId: userId,
                        randomId: randomId
                    },
                    success: function(response) {
                        $('#userLists' + randomId).html(response);
                    },
                    error: function(xhr) {
                        console.log('Error:', xhr.responseText);
                    }
                });

                //permissions list
                $.ajax({
                    url: "{{ route('get-permissions') }}",
                    method: "GET",
                    data: {
                        roleId: roleId,
                        permissionId: permissionId,
                        randomId: randomId
                    },
                    success: function(response) {
                        $('#userPermissions' + randomId).html(response);
                    },
                    error: function(xhr) {
                        console.log('Error:', xhr.responseText);
                    }
                });

            } else {
                $('#userPermissions' + randomId).html('Select Permissions');
            }
        }


        /*function getPermissionsByRoleIdAndUserId(randomId){
        	var roleId=$("#role_id"+randomId).val();
        	var userId=$("#user_id"+randomId).val();
        	
        	if(roleId !='' || userId !=''){
        		$.ajax({
        			url: "{{ route('get-permissions') }}",
        			method: "GET",
        			data:{roleId:roleId,userId:userId,randomId:randomId},
        			success: function (response) {
        				$('#userPermissions'+randomId).html(response);
        			},
        			error: function (xhr) {
        				console.log('Error:', xhr.responseText);
        			}
        		});
        	}
        }*/

        // Usage for Personality
        addComponent("container_personality", "add_more_personality_template", "add_more_personality",
            "delete_personality_row", "personality_component_more", "delete_personality", personalities_details);

        $("#form").on("submit", function(event) {
            event.preventDefault();
            var formData = $("#form").serializeArray();
            formData.push({
                name: "_token",
                value: "{{ csrf_token() }}"
            });
            var csrfToken = "{{ csrf_token() }}";

            @isset($id)
                var method = 'PUT';
                var url = "{{ url('/flow-management/' . $id) }}";
            @else
                var method = 'POST';
                var url = "{{ url('/flow-management') }}";
            @endisset

            var requestData = {
                url: url,
                method: method,
                body: formData,
            };

            sendRequest(requestData, "{{ url('flow-management') }}");
        });
    </script>
@endsection
