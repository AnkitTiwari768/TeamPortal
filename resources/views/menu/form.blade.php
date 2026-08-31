@extends('components.admin.layout')

@section('page-content')
 
<div class="container-fluid px-4 py-4">     
	<div class="row">
		<div class="col-lg-12 col-md-12">
			<div class="card container-main-card">
				<div class="card-header d-flex">
					<div class="heading">
						<h1>
						@if(!empty($row->id))
							 {{ __('menu.edit_menu') }}
						@else
							 {{ __('menu.add_menu') }}
						@endif
						</h1>
						<nav aria-label="breadcrumb">
							<ol class="breadcrumb">
								<li class="breadcrumb-item"><a href="#">Home</a></li> 
								<li class="breadcrumb-item"><a href="#">@if(!empty($row->id))
							 {{ __('menu.edit_menu') }}
						@else
							 {{ __('menu.add_menu') }}
						@endif</a></li>
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
                                <label>{{ __('menu.parent_title') }} </label>
                               <?php
								$menus=$flatMenus;
								//echo "<pre/>";print_r($menus);exit;
								$parents=array();
								foreach ($menus as $menu){
									$parents[] = $menu;
								}
								
								
								//$tree = buildTree($parents);
								function printTree($tree, $r = 0, $p = null, $parent_id = null) {
									foreach ($tree as $i => $t) {
										
										$dash = ($t['parent_id'] == 0) ? '' : str_repeat('-', $r) .' ';
										
										if ($t['id'] == $parent_id) {
											
											printf("\t<option value='%s' selected>%s%s</option>\n", $t['id'], $dash, $t['title_en']);
										}
										else {
											printf("\t<option value='%s'>%s%s</option>\n", $t['id'], $dash, $t['title_en']);
										}
										if ($t['parent_id'] == $p) {
											$r = 0;
										}
										if (isset($t['_children'])) {
											printTree($t['_children'], $r+1, $t['parent_id'],$parent_id);
										}
									}
								}

							
									print("<select class='form-control' name='parent_id' id='parent_id'>\n");
										printf("<option value=''>Select (चुनें)</option>");
										printTree($tree,0,null,$row['parent_id'] ?? null);
									print("</select>");
								?>
                                <span class="text-danger form-error" id="parent_id_error"></span>
                            </div>
                            
							<div class="col-md-6 mb-3">
                                <label class="required">{{ __('menu.sub_menu') }} </label>

                              {!! Form::select('sub_menu', ['' => __('menu.select').' '.__('menu.select_mr')] + $subMenuList, $row['sub_menu'] ?? null, ['class' => 'form-control', 'id' => 'sub_menu']) !!}
                                <span class="text-danger form-error" id="sub_menu_error"></span>
                            </div> 
							<div class="col-md-6 mb-3">
                                <label class="required">{{ __('menu.title_en') }}</label>
                                <input type="text" class="form-control" name="title_en" id="title_en" <?php if (!empty($row['title_en']) && @$row['title_en']=='Wishlist' || @$row['title_en']=='Postal Code' || @$row['title_en']=='Blog' || @$row['title_en']=='Feedback'){ echo "disabled"; }else{ echo "";}?> placeholder="{{ __('menu.title_en') }} {{ __('menu.title_en_mr') }}"
                                    @if (!empty($row['title_en']))
                                        value="{{ $row['title_en'] }}"
                                    @endif
                                >
                                <span class="text-danger form-error" id="title_en_error"></span>
                            </div>
							
							<div class="col-md-6 mb-3">
                                <label class="required">{{ __('menu.title_mr') }} </label>
                                <input type="text" class="form-control" name="title_mr" id="title_mr" <?php if (!empty($row['title_mr']) && @$row['title_mr']=='इच्छा-सूची' || @$row['title_mr']=='डाक कोड' || @$row['title_mr']=='ब्लॉग' || @$row['title_mr']=='प्रतिक्रिया'){ echo "disabled"; }else{ echo "";}?> placeholder="{{ __('menu.title_mr') }} {{ __('menu.title_mr_mr') }}"
                                    @if (!empty($row['title_mr']))
                                        value="{{ $row['title_mr'] }}"
                                    @endif
                                >
                                <span class="text-danger form-error" id="title_mr_error"></span>
                            </div>
							 
							
							
							
							<div class="col-md-6 mb-3">
                                <label class="required">{{ __('menu.sort_order') }}</label>
                            

                                {!! Form::select('sort_order', ['' => __('menu.select').' '.__('menu.select_mr')] + $sortOrderList, $row['sort_order'] ?? null, ['class' => 'form-control', 'id' => 'sort_order']) !!}
								

                                <span class="text-danger form-error" id="sort_order_error"></span>
                            </div> 
							
                        
                            <div class="col-md-6 mb-3">
                                <label class="required">{{ __('menu.status') }}</label>
                                {{ Form::select('is_active', status_list(), $row['is_active'] ?? null, ['class' => 'form-control', 'id' => 'is_active']) }}   
                                <span class="text-danger form-error" id="is_active_error"></span>
                            </div> 
							
							<div class="col-md-6 mb-3">
                                <label class="required">{{ __('menu.template') }} </label>
                               {!! Form::select('template', $templateList, $row['template'] ?? null, ['class' => 'form-control', 'id' => 'template']) !!}
								 
                                <span class="text-danger form-error" id="sort_order_error"></span>
                            </div> 
							
							<div class="col-md-6 mb-3">
								<label class="required">{{ __('menu.show_in') }} </label>
								
								 <?php
									$show_in=array();
									if(!empty($row['show_in'])){
										$explode_data=explode(",",$row['show_in']);
										foreach ($explode_data as $key => $value) {
										   $show_in[]=$value;
										}
									}
                                ?>
								 <select name="show_in[]" id="show_in" class="form-control" multiple style="height:66px">
								   <option value="1" <?php if(in_array(1,$show_in)){ echo "selected";}else{ echo '';}?>>Header</option>
								   <option value="2" <?php if(in_array(2,$show_in)){ echo "selected";}else{ echo '';}?>>Footer</option>
								 </select>
								<span class="text-danger form-error" id="show_in_error"></span>
							</div> 
							
							<div class="col-md-6 mb-3" style="display:none" id="div_menu_category">
                                <label class="required">{{ __('menu.fmenu_category_en') }}</label>
                              {!! Form::select('fmenu_category', ['' => __('menu.select').' '.__('menu.select_mr')] + $fmenuCategoryList, $row['fmenu_category'] ?? null, ['class' => 'form-control', 'id' => 'fmenu_category']) !!}
								 
                                <span class="text-danger form-error" id="fmenu_category_error"></span>
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
    
	
    <script>
	var fields = <?php echo json_encode(isset($row)? $row: ''); ?>;
	//console.log(fields.show_in);
	if(fields.show_in == 2)
		$('#parent_id').attr('disabled','disabled');
	
	if(fields !='')
		$('#parent_id').attr('disabled','disabled');
	
	$("#show_in").change(function(){
		var show_in = $("#show_in").val();
		if(show_in == 2)
			$('#parent_id').attr('disabled','disabled');
		else
			$('#parent_id').removeAttr('disabled');
	});
	
	  var post_menu_order='<?php echo !empty($_POST["sort_order"])?$_POST["sort_order"]:''; ?>';
	 var menu_id=$("#parent_id").val();
	 if(menu_id){
		onload_selected_menu_order(menu_id); 
	 }
	 
	
	 
	$("#parent_id").change(function(){
       var menu_id = $(this).val();
	   if(menu_id !=''){
		  
			$("#sort_order").html("<option value=''>Loading...</option>");
			var append;
			$.ajax({
			  type: 'GET',
			 url: BASE_URL+"/menus/get_existed_menu_order/"+menu_id,
			  success:function(data){
                var res=$.parseJSON(data);
                $("#sort_order").html("");
                $.each(res.data, function(k, values) {
                   append=$("#sort_order").append(
						  $('<option></option>').val(values).html(values)
					  );
				});
             
				  $("#sort_order").prepend("<option value=''>Select Sort Order</option>").val('')+append;
				}
			}); 
	    } else {
            $("#sort_order").prepend("<option value=''>Select Sort Order</option>").val('');
			window.location.reload();
	   }
       
	});
	
	
	
	function onload_selected_menu_order(menu_id){
		if(menu_id !=''){
			$("#sort_order").html("<option value=''>Loading...</option>");
			var append;
			$.ajax({
			  type: 'GET',
			   url: BASE_URL+"/menus/get_existed_menu_order/"+menu_id,
			  success:function(data){
                    var res=$.parseJSON(data);
                    $("#sort_order").html("");
                    var selected_option = $("#sort_order").append(
                            $('<option></option>').val("<?php echo !empty($row['sort_order'])?$row['sort_order']:'';?>").html("<?php echo !empty($row['sort_order'])?$row['sort_order']:'';?>")
                        );
                    $.each(res.data, function(k, values) {
                    append=$("#sort_order").append(
                            $('<option></option>').val(values).html(values)
                        );
                    });
                  
				    $("#sort_order").prepend("<option value=''>Select Sort Order</option>").val('') + append ;
                   
                    if(post_menu_order !=0){
                        $("#sort_order").val(post_menu_order);
                    } else {
                        $("#sort_order").val("<?php echo !empty($row['sort_order'])?$row['sort_order']:'';?>");
                    }
				  
				}
			});
		}else{
			$("#sort_order").html("<option value=''>Select Sort Order</option>").val('');
			window.location.reload();
		}
	}
	
	
	
	
	     var links=$("#show_in").val();
		 if(links !=''){
			footer_menu_category(links);
		 }
		$("#show_in").click(function(){
			var links=$("#show_in").val();
			footer_menu_category(links);
		});
		
		function footer_menu_category(links){
			if(links=='1,2'){
				$("#div_menu_category").show();
			}else if(links==2){
				$("#div_menu_category").show();
			}else{
				$("#div_menu_category").hide();
			}
		}
		
        $("#form").on("submit", function (event) {
            event.preventDefault();
			var parent_id = $("#parent_id").val();
            var title_en = $("#title_en").val();
			var title_mr = $("#title_mr").val();
			var sub_menu = $("#sub_menu").val();
			var sort_order = $("#sort_order").val();
			var show_in = $("#show_in").val();
            var is_active = $("#is_active").val();
			var template = $("#template").val();
			var fmenu_category = $("#fmenu_category").val();
			
			
			var csrfToken = "{{ csrf_token() }}";
            
            @isset($id)
                var method = 'PUT';
                var url = "{{ url('/menus/'. $id) }}";
            @else 
                var method = 'POST';
                var url = "{{ url('/menus') }}";
            @endisset

            var requestData = {
                url: url,
                method: method,
                body: {
                    parent_id: parent_id,
					title_en: title_en,
					title_mr: title_mr,
					sub_menu: sub_menu,
					sort_order: sort_order,
					show_in: show_in,
                    is_active:is_active,
					template:template,
					fmenu_category:fmenu_category,
                    _token: csrfToken
                }
            };

            sendRequest(requestData, "{{ url('menus') }}");
        });
    </script>

@endsection









