@extends('components.admin.content-layout')
@section('card-content')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<style>
   .select2-container{
   width:100% !important;
   }
   .select2-container .select2-selection--single{
   height:42px !important;
   border:1px solid #ced4da !important;
   }
   .select2-container--default .select2-selection--single .select2-selection__rendered{
   line-height:40px !important;
   }
   .select2-container--default .select2-selection--single .select2-selection__arrow{
   height:40px !important;
   }
</style>
<div class="card-body">
   <form id="userByPassForm">
      @csrf
      <div class="row">
         <div class="col-md-6 mb-3">
            <label class="fw-bold mb-2">
            Select User Email
            </label>
            <select
               name="email"
               class="form-control select2"
               
               >
               <option value="">
                  Select User
               </option>
               @foreach($users as $user)
               <option value="{{ $user->email }}">
                  {{ $user->email }}
               </option>
               @endforeach
            </select>
         </div>
         <div class="col-md-6 mb-3">
            <label class="fw-bold mb-2">
            By Pass User
            </label>
            <select
               name="by_pass"
               class="form-control"
               
               >
               <option value="">
                  Select Status
               </option>
               <option value="1">
                  True
               </option>
               <option value="0">
                  False
               </option>
            </select>
         </div>
      </div>
      <div class="text-end">
         <button
            type="submit"
            class="btn btn-success"
            id="submitBtn"
            >
         Update
         </button>
      </div>
   </form>
</div>
@endsection
@section('js')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script>
   $(document).ready(function () {
   
       $('.select2').select2({
           width: '100%'
       });
   
       toastr.options = {
           closeButton: true,
           progressBar: true,
           positionClass: "toast-top-right",
           timeOut: 3000,
           extendedTimeOut: 1000,
           preventDuplicates: true
       };
   
       $('#userByPassForm').on('submit', function(e){
   
           e.preventDefault();
   
           $('#submitBtn').prop('disabled', true);
   
           $.ajax({
   
               url: "{{ route('user-bypass.update') }}",
   
               type: "POST",
   
               data: $(this).serialize(),
   
               success: function(response){
   
                   $('#submitBtn').prop('disabled', false);
   
                   if(response.status){
   
                       toastr.success(response.message);
   
                       $('#userByPassForm')[0].reset();
   
                       $('.select2').val('').trigger('change');
   
                   }else{
   
                       toastr.error(response.message);
                   }
               },
   
               error: function(xhr){
   
                   $('#submitBtn').prop('disabled', false);
   
                   if(xhr.responseJSON?.errors){
   
                       $.each(xhr.responseJSON.errors, function(key, value){
                           toastr.error(value[0]);
                       });
   
                       return;
                   }
   
                   toastr.error(
                       xhr.responseJSON?.message ??
                       'Something went wrong.'
                   );
               }
           });
   
       });
   
   });
</script>
@endsection