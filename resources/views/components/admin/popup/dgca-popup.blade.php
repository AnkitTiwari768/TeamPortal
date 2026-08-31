<!-- Popup modal start here -->
<div class="modal fade html-content" id="dgcaPermissionModal" tabindex="-1" aria-labelledby="dgcaPermissionModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered modal-lg">
	
		<div class="modal-content">
			<div class="modal-header border-0 modal-header border-0 justify-content-center">
				<h5 class="modal-title" id="dgcaPermissionModalLabel">Permission for Airport/Aerial Filming
				<!--<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>-->
			</div>
			<div class="modal-body pb-1">
				<h6>Dear {{ get_auth_user_name() }},</h6>
                <p>
                    Directorate General of Civil Aviation (DGCA) is the regulatory body governing the safety aspects of civil aviation in India. It endeavours to promote safe and efficient Air Transportation through regulation and proactive safety scrutiny system. DGCA provides permission for shoots at airports in the country.For filming at Airports / Aerial Filming permission, kindly apply at least two (2) months prior to the day of the shoot. Please sign up on the EGCA portal and apply online mentioning purpose as Ground Photography.
                </p>
               

			</div>
			<div class="modal-footer border-0 justify-content-center"> 
				<a href="https://www.dgca.gov.in/digigov-portal/jsp/dgca/common/login.jsp" target="_blank"
                class="btn btn-primary action btn-sm" 
                ><i class="fa fa-send"></i> Redirect to DGCA Website</a>
				<button type="button" class="btn btn-danger btn-sm" data-bs-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i> Close</button>
			</div>
		</div>
	
	</div>
</div>