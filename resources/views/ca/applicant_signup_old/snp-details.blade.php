<div class="table-responsive">
	<table class="table table-bordered table-hover align-middle" style="overflow-x: auto">
		<thead class="table-light">
		  <tr>
			<th scope="col">Select</th>
			<th scope="col">SNP Name</th>
			<th scope="col">Commercial Model</th>
			<th scope="col">Commercial Model Documents</th>
			<th scope="col">Number of Live sellerson boarded by SNP on ONDC to date</th>
			<th scope="col">Date of going liveon ONDC for the SNP</th>
			<th scope="col">No of transactions done till date by the SNP on ONDC</th>
			<th scope="col">Short description of the SNP</th>
			<th scope="col">Upload Pdf or Doc File</th>
		  </tr>
		</thead>
		<tbody>
		@if(!empty($snpdetails))
			@foreach($snpdetails as $snpdetail)
		  <tr>
			<td>
			  <input class="form-check-input snp_id" type="radio" name="snp_id" id="snp_id" value="{{$snpdetail->id}}">
			</td>
			<td>{{$snpdetail->snp_name}}</td>
			<td>{{$snpdetail->commercial_model}}</td>
			<td><a target="_blank" href="{{ url('storage/app/uploads/commercial_model/'.$snpdetail->commercial_model_document) }}">Download</a></td>
			<td>{{$snpdetail->live_seller}}</td>
			<td>{{$snpdetail->date_of_going_live_on_ondc}}</td>
			<td>{{$snpdetail->no_of_transactions_done}}</td>
			<td>{{$snpdetail->short_description}}</td>
			<td><a target="_blank" href="{{ url('storage/app/uploads/commercial_model/'.$snpdetail->description_document) }}">Download</a></td>
		  </tr>
		  @endforeach
		  @endif
		</tbody>
	  </table>
</div>
