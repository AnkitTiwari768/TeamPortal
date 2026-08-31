<script>
   function loadSubDistrict(district_id, selectedSubDistrict = null) {
    $(".sub_district_id").prop("disabled", true);
    $(".sub_district_id").html('<option value="">Loading...</option>');
    $.get(BASE_URL + '/get-sub-district/' + district_id, function(res) {
        let html = "<option value=''>Select</option>";

        if (!res.data || res.data.length === 0) {
            $(".sub_district_id").html(html);
            $(".sub_district_id").prop("disabled", true);
            return;
        }

        $.each(res.data, function(i, row) {
            html += `<option value="${row.id}">${row.name}</option>`;
        });

        $(".sub_district_id").html(html);
        $(".sub_district_id").prop("disabled", false);

        if (selectedSubDistrict) {
            $(".sub_district_id").val(selectedSubDistrict);
        }
    }).fail(function() {
        $(".sub_district_id").html("<option value=''>Select</option>");
        $(".sub_district_id").prop("disabled", false);
    });
}
</script>
