@extends('components.admin.layout')

@section('page-content')

<!-- <div class="container-fluid"> 
  <div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">{{ __('message.file_list') }}
          <span class="float-right">
          @if (acl('edit-media'))
            <a href="{{ url('media/create') }}" class="btn btn-info btn-sm">
                <i class="fas fa-plus"></i> {{ __('message.add_file') }}
            </a>
            @endif
          </span>
        </h6>
    </div> -->
    <div class="container-fluid px-4 py-4">
    <div class="row">
        <div class="col-lg-12 col-md-12">
            <div class="card container-main-card">
                <div class="card-header d-flex">
                    <div class="heading">
                        <h1>{{ __('media.file_list') }}</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="#">Home</a></li>
                                <li class="breadcrumb-item"><a href="#"> {{ __('media.add_file') }}</a></li>
                            </ol>
                        </nav>
                    </div>
                    @if (acl('media-edit'))
                    <div class="action-header ms-auto">
                        <!-- split button -->
                        <div class="btn-group drop-btn">
                            <a href="{{ url('media/create') }}">
                            <button type="button" class="btn btn-danger"> 
                                <img src="{{asset('assets/img-new/add.svg')}}">{{ __('media.add_file') }}
                            </button></a>
                        </div>
                    </div>
                    @endif
                </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
						<th>File Name</th>
						<th>File Url</th>
                        <th>File Size</th>
						<th>Date/Time</th>
                        <th>Action</th>
                    </tr>
                </thead>
				  <?php foreach($files as $key=>$file){ 
				    //echo "<pre/>";print_r($file);
				    $ext  = (new SplFileInfo($file))->getExtension(); 
					$img = array("png", "jpg", "jpeg", "gif","PNG", "JPG", "JPEG", "GIF");
					$doc=array('doc', 'docx','DOC','DOCX');
					$excel=array('xls','xlsx','XLS','XLSX');
					$pdf=array('pdf', 'PDF');
				  
				  ?>
				    <tr>
						<td>
						<?php echo pathinfo(basename($file), PATHINFO_FILENAME);?>
						<br>
						<?php if(in_array($ext,$img)){ ?>
							<img src="<?php echo url($file); ?>" alt="<?php echo pathinfo($file, PATHINFO_FILENAME); ?>" title="<?php echo pathinfo($file, PATHINFO_FILENAME); ?>" loading="lazy" style="height:60px; width:60px;">

						 <?php }else if(in_array($ext,$pdf)){ ?>
							<img src="<?php echo url('img/pdf.png'); ?>" alt="<?php echo pathinfo($file, PATHINFO_FILENAME); ?>" title="<?php echo pathinfo($file, PATHINFO_FILENAME); ?>" loading="lazy" style="height:60px; width:60px;">
						  <?php }else if(in_array($ext,$doc)){ ?>
							
							<img src="<?php echo url('img/doc.png'); ?>" alt="<?php echo pathinfo($file, PATHINFO_FILENAME); ?>" title="<?php echo pathinfo($file, PATHINFO_FILENAME); ?>" loading="lazy" style="height:60px; width:60px;">
						 <?php }if(in_array($ext,$excel)){ ?>
							
							<img src="<?php echo url('img/excel.png'); ?>" alt="<?php echo pathinfo($file, PATHINFO_FILENAME); ?>" title="<?php echo pathinfo($file, PATHINFO_FILENAME); ?>" loading="lazy" style="height:60px; width:60px;">
						 <?php } ?>
						
						</td>
						<td><code><?php echo url($file); ?></code></td>
						<td><?php echo formatSizeUnits(filesize($file)); ?></td>
						<td><?php echo date("Y-M-d H:i:s",strtotime($key)); ?></td>
						<td><button type="button" class="btn btn-danger btn-sm m-b-0-0 waves-effect waves-light remove" data-path="<?php echo $file; ?>"><i class="fa fa-trash-o"></i></button></td>
                    </tr>
				  <?php } ?>
            </table>
        </div>
    </div>
  </div>
</div>
</div>
</div>

@endsection

@section('js')

<script>
$(document).ready(function() {
  var dataTable=$('#dataTable').DataTable(
	{"lengthMenu": [[5, 25, 50, -1], [5, 25, 50, "All"]],
	"scrollX": true,
	"order": [[3, "desc" ]],
	"columnDefs": [ {
      "targets": [ 4 ],
      "orderable": false
    } ]
	});
	
	$("body").on("click", ".remove", function(e) { 
		e.preventDefault();
		var path=$(this).data("path");
		if (confirm('Are you sure to remove this file?')){
			$.ajax({
				url: "{{ url('media/deleteUploadFile') }}",
				method: "POST",
				dataType: "json",
				data: {
					path: path,
					_token: "{{ csrf_token() }}"
				},
				success: function (response) {
					 toastr.success("File deleted successfully!");
					window.setTimeout(function() {
					   window.location.href='<?php echo url("media");?>';
					},1000);
				}   
			});
		}
		
	});
	
});
</script>

@endsection
           