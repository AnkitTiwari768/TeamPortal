<style>
    .desc {
        max-height: 70px; /* approx 3–4 lines */
        overflow: hidden;
        transition: max-height 0.3s ease;
		 display: inline;
    }

    .desc.expanded {
        max-height: 1000px;
    }

    .read-more {
        color: #0d6efd;
        cursor: pointer;
        font-size: 13px;
        display: inline-block;
        margin-top: 6px;
    }
</style>

<div class="table-responsive">
	<table class="table table-bordered table-hover align-middle common_table" style="overflow-x: auto">
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
			<td class="text-center">
			  <input class="snp_id" type="radio" name="snp_id" id="snp_id" value="{{$snpdetail['id']}}">
			</td>
			<td>{{$snpdetail['snp_id']}}</td>
			<td>{{$snpdetail['organisation_name']}}</td>
			<td><a href="{{$snpdetail['website']}}" target="_blank" rel="noopener noreferrer">Link</a>
			<!-- <td>{{$snpdetail['website']}}</td> -->
			<!-- <td>{{$snpdetail['product_category']}}</td> -->
			<!-- <td>{{$snpdetail['short_description']}}</td> -->
			 <td>
				<p class="desc">
					{{ trim($snpdetail['product_category'] ?? '') }}
				</p>
				<a href="javascript:void(0)"
				class="toggle-link"
				style="text-decoration:none;"
				onclick="toggleDesc(this)">
					Read more
				</a>
			 </td>
			<td>
			
			<p class="desc">
				{{ trim($snpdetail['short_description'] ?? '') }}
			</p>
			<a href="javascript:void(0)" class="toggle-link" style="text-decoration:none;" onclick="toggleDesc(this)">Read more</a>

			
			</td>
			<td>
				<a href="javascript:void(0)"
					class="commercialModelBtn text-primary" style="text-decoration:none;"
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


<script>
	document.querySelectorAll('.desc').forEach(desc => {
		const fullText = desc.textContent.trim();
		desc.setAttribute('data-full', fullText);

		if (fullText.length > 50) {
			desc.textContent = fullText.substring(0, 50) + "...";
			desc.classList.add('collapsed');
		} else {
			// hide Read more if text is short
			desc.nextElementSibling.style.display = 'none';
		}
	});

	function toggleDesc(el) {
		const desc = el.previousElementSibling;
		const fullText = desc.getAttribute('data-full');
		const shortText = fullText.substring(0, 50) + "...";

		if (desc.classList.contains('collapsed')) {
			desc.textContent = fullText;
			el.innerText = 'Show less';
			desc.classList.remove('collapsed');
		} else {
			desc.textContent = shortText;
			el.innerText = 'Read more';
			desc.classList.add('collapsed');
		}
	}

</script>
