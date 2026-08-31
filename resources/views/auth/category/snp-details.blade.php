<div class="table-responsive">
	<table class="table table-bordered table-hover align-middle" style="overflow-x: auto">
		<thead class="table-light">
		  <tr>
			<th scope="col">Select</th>
			<th scope="col">NP ID</th>
			<th scope="col">Organisation Name</th>
			<th scope="col">Website of Organisation</th>
			<th scope="col">Product Category</th>
			<th scope="col">Short Description about NP</th>
			<th scope="col">Commercial Model</th>
			<th scope="col">Languages Supported</th>
		  </tr>
		</thead>
		<tbody>
		@if(!empty($snpdetails))
			@foreach($snpdetails as $snpdetail)
		  <tr>
			<td>
			  <input class="form-check-input snp_id" type="radio" name="snp_id" id="snp_id" value="{{$snpdetail['id']}}" required>
			</td>
			<td>{{$snpdetail['snp_id']}}</td>
			<td>{{$snpdetail['organisation_name']}}</td>
			<td><a href="{{$snpdetail['website']}}" target="_blank" rel="noopener noreferrer">{{$snpdetail['website']}}</a>
				<!-- {{$snpdetail['website']}}</td> -->
			<td>{{$snpdetail['product_category']}}</td>
			<td>{{$snpdetail['short_description']}}</td>
			<td>
				<a href="javascript:void(0)"
					class="commercialModelBtn text-primary"
					data-model='@json($snpdetail["commercial_model"])'>
					View
				</a>
            </td>
			<td>{{$snpdetail['languages_supported']}}</td>
		  </tr>
		  @endforeach
		  @endif
		</tbody>
	  </table>
</div>

