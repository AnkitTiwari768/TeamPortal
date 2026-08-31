@extends('components.admin.layout')

@section('page-content')

<!-- <div class="container-fluid"> 

    <div class="row">
        <div class="col-md-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">   
				 <h6 class="m-0 font-weight-bold text-primary">
                        @isset($id)
                            {{ __('page.edit_page') }}
                        @else 
                            {{ __('page.add_page') }}
                        @endisset
                    </h6>
				@include('components.admin.buttons.back-button')
                </div>
                <div class="card-body"> -->
                	<div class="container-fluid px-4 py-4">     
	<div class="row">
		<div class="col-lg-12 col-md-12">
			<div class="card container-main-card">
				<div class="card-header d-flex">
					<div class="heading">
						 <h1>
                        @isset($id)
                            {{ __('page.edit_page') }}
                        @else 
                            {{ __('page.add_page') }}
                        @endisset
                    </h1>
						<nav aria-label="breadcrumb">
							<ol class="breadcrumb">
								<li class="breadcrumb-item"><a href="#">Home</a></li> 
								<li class="breadcrumb-item"><a href="#">@isset($id)
                            {{ __('page.edit_page') }}
                        @else 
                            {{ __('page.add_page') }}
                        @endisset</a></li>
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
                                <label class="required">{{ __('page.menu_title') }} </label>
                               <?php 
							   $menu_id=isset($row['menu_id'])?$row['menu_id']:'';
								//$menus=parent_backened_menu();
								$parents=array();
								foreach ($parent_backened_menu as $menu){
									$parents[] = $menu;
								}
								
								
								//$tree = buildTree($parents);
								function printTree($buildTree, $r = 0, $p = null, $menu_id = null) {
									foreach ($buildTree as $i => $t) {
										
										$dash = ($t['id'] == 0) ? '' : str_repeat('-', $r) .' ';
										
										if ($t['id'] == $menu_id) {
											
											printf("\t<option value='%s' selected>%s%s</option>\n", $t['id'], $dash, $t['title_en']);
										}
										else {
											printf("\t<option value='%s'>%s%s</option>\n", $t['id'], $dash, $t['title_en']);
										}
										if ($t['id'] == $p) {
											$r = 0;
										}
										if (isset($t['_children'])) {
											printTree($t['_children'], $r+1, $t['id'],$menu_id);
										}
									}
								}

							
									print("<select class='form-control' name='menu_id' id='menu_id'>\n");
										printf("<option value=''>Select (चुनें)</option>");
										printTree($buildTree,0,null,$row['menu_id'] ?? null);
									print("</select>");
								?>
                                <span class="text-danger form-error" id="menu_id_error"></span>
                            </div>
							<div class="col-md-6 mb-3">
                            </div>
							<div class="col-md-6 mb-3">
                                <label class="required">{{ __('page.title_en') }}</label>
                                <input type="text" class="form-control" name="title_en" id="title_en" placeholder="{{ __('page.title_en') }} {{ __('page.title_en_mr') }}"
                                    @if (!empty($row['title_en']))
                                        value="{{ $row['title_en'] }}"
                                    @endif
                                >
                                <span class="text-danger form-error" id="title_en_error"></span>
                            </div>
							
							<div class="col-md-6 mb-3">
                                <label class="required">{{ __('page.title_mr') }}</label>
                                <input type="text" class="form-control" name="title_mr" id="title_mr" placeholder="{{ __('page.title_mr') }} {{ __('page.title_mr_mr') }}"
                                    @if (!empty($row['title_mr']))
                                        value="{{ $row['title_mr'] }}"
                                    @endif
                                >
                                <span class="text-danger form-error" id="title_mr_error"></span>
                            </div>
							 
							
							<div class="col-md-10 mb-3">
                                <label class="required">{{ __('page.description_en') }}</label>
								<textarea class="form-control _editor_content" name="description_en" id="description_en"> @if (!empty($row['description_en'])){{ $row['description_en'] }} @endif</textarea>
                                <span class="text-danger form-error" id="description_en_error"></span>
                            </div>
							
							<div class="col-md-10 mb-3">
                                <label class="required">{{ __('page.description_mr') }}</label>
								<textarea class="form-control _editor_content" name="description_mr" id="description_mr"> @if (!empty($row['description_mr'])){{ $row['description_mr'] }} @endif</textarea>
                                <span class="text-danger form-error" id="description_mr_error"></span>
                            </div>
							
                           
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
    
<script type="text/javascript" src="{{ asset('assets/js/tinymce.min.js')}}" referrerpolicy="origin"></script>

    <script>
    	 tinymce.init({
		selector: "._editor_content",
		theme: "silver",
		width: 1024,
		height: 450,
		convert_urls: false,
		element_format :"html",
		encoding: "xml",
		plugins: [
			 "advlist autolink link image lists charmap print preview hr pagebreak",
			 "searchreplace wordcount visualblocks visualchars insertdatetime nonbreaking",
			 "table contextmenu directionality emoticons paste textcolor code fullscreen "
	   ],
	   toolbar1: "undo redo | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | styleselect | fullscreen",
	   toolbar2: "link unlink | image | forecolor backcolor  | print preview code | fontsizeselect",
		fontsize_formats: "8px 9px 10px 11px 12px 13px 14px 15px 16px 17px 18px 19px 20px 21px 22px 23px 24px 25px 26px 27px 28px 29px 30px 31px 32px 33px 34px 35px 36px 37px 38px 39px 40px 41px 42px 43px 44px 45px 46px 47px 48px",
	   image_advtab: true ,
	   	forced_root_block:"",
		force_br_newlines : true,
		force_p_newlines : false,
		setup: function (editor) {
			editor.on('change', function () {
				tinymce.triggerSave();
			});
		}
	});

        $("#form").on("submit", function (event) {
            event.preventDefault();
			var menu_id = $("#menu_id").val();
            var title_en = $("#title_en").val();
			var title_mr = $("#title_mr").val();
			var description_en = $("#description_en").val();
			var description_mr = $("#description_mr").val();
            var is_active = $("#is_active").val();
			
			var csrfToken = "{{ csrf_token() }}";
            
            @isset($id)
                var method = 'PUT';
                var url = "{{ url('/pages/'. $id) }}";
            @else 
                var method = 'POST';
                var url = "{{ url('/pages') }}";
            @endisset

            var requestData = {
                url: url,
                method: method,
                body: {
                    menu_id: menu_id,
					title_en: title_en,
					title_mr: title_mr,
					description_en: description_en,
					description_mr: description_mr,
                    is_active:is_active,
                    _token: csrfToken
                }
            };

            sendRequest(requestData, "{{ url('pages') }}");
        });
    </script>

@endsection









