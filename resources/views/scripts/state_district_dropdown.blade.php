<script>
function loadDistrict(state_id,selectedDistrict = null) {
    $(".district_id").prop("disabled", true);
    $(".district_id").html('<option value="">Loading...</option>');
    $.get(BASE_URL + '/getDistrict/' + state_id, function(res) {
        let html = "<option value=''>Select</option>";

        $.each(res.data, function(i, row) {
            html += `<option value="${row.id}">${row.name}</option>`;
        });

        $(".district_id").html(html);

        /*if (districtId) {
            $(".district_id").val(districtId);
            loadSubDistrict(districtId);
        }*/
        if (selectedDistrict) {
            $(".district_id").val(selectedDistrict);
            loadSubDistrict(selectedDistrict, subDistrictId);
        }
        $(".district_id").prop("disabled", false);
    }).fail(function() {
        $(".district_id").html("<option value=''>Select</option>");
        $(".district_id").prop("disabled", false);
    });
}
</script>
