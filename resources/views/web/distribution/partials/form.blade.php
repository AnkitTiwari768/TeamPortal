@csrf

<!-- 1. CONTEXT / DIMENSIONS -->
<h6 class="text-dark fw-semibold border-bottom pb-2 mb-3">1. Duration And Component</h6>
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <label class="form-label fw-bold small required">Financial Year</label>
        <select name="financial_year" id="fy" class="form-select trigger-pool" @disabled(isset($row))>
            <option value="">Select Financial Year</option>
            @foreach(financial_year() as $key => $val)
                <option value="{{ $key }}" {{ (isset($row) && $row->financial_year == $key) ? 'selected' : '' }}>{{ $val }}</option>
            @endforeach
        </select>
        <div class="text-danger small err-msg" id="financial_year_error"></div>
    </div>
    <div class="col-md-4">
        <label class="form-label fw-bold small required">Duration</label>
        <select name="duration_id" id="duration" class="form-select trigger-pool" @disabled(isset($row))>
            <option value="">Select Duration</option>
            @foreach($durations as $key => $val)
                <option value="{{ $key }}" {{ (isset($row) && $row->duration_id == $key) ? 'selected' : '' }}>{{ $val }}</option>
            @endforeach
        </select>
        <div class="text-danger small err-msg" id="duration_id_error"></div>
    </div>
    <div class="col-md-4" id="sub_duration_container" style="{{ (!empty($subDurations) && count($subDurations) > 0) ? '' : 'display:none;' }}">
        <label class="form-label fw-bold small">Sub-Duration</label>
        <select name="sub_duration_id" id="sub_duration" class="form-select trigger-pool" @disabled(isset($row))>
            <option value="">Select Sub-Duration</option>
            @if(isset($subDurations))
                @foreach($subDurations as $sd)
                    <option value="{{ $sd->id }}" {{ (isset($row) && $row->sub_duration_id == $sd->id) ? 'selected' : '' }}>{{ $sd->name }}</option>
                @endforeach
            @endif
        </select>
        <div class="text-danger small err-msg" id="sub_duration_id_error"></div>
    </div>

    <div class="col-md-6">
        <label class="form-label fw-bold small required">Major Component</label>
        <select name="major_component_id" id="major_component" class="form-select trigger-pool" @disabled(isset($row))>
            <option value="">Select Major Component</option>
            @foreach($majorComponents as $comp)
                <option value="{{ $comp->id }}" {{ (isset($row) && $row->major_component_id == $comp->id) ? 'selected' : '' }}>{{ $comp->name }}</option>
            @endforeach
        </select>
        <div class="text-danger small err-msg" id="major_component_id_error"></div>
    </div>
    <div class="col-md-6">
        <label class="form-label fw-bold small">Sub-Component</label>
        <select name="sub_component_id" id="sub_component" class="form-select trigger-pool" @disabled(isset($row))>
            <option value="">Select Sub-Component</option>
            @if(isset($subComponents))
                @foreach($subComponents as $sc)
                    <option value="{{ $sc->id }}" {{ (isset($row) && $row->sub_component_id == $sc->id) ? 'selected' : '' }}>{{ $sc->name }}</option>
                @endforeach
            @endif
        </select>
        <div class="text-danger small err-msg" id="sub_component_id_error"></div>
    </div>
</div>

<!-- 2. REAL-TIME BALANCE OVERVIEW -->
<div id="pool-balance-banner" class="alert alert-secondary border-0 shadow-none mb-4 py-3 px-3" style="display: none; background-color: #f8f9fa; border-left: 4px solid #6c757d !important;">
     <div class="row align-items-center">
          <div class="col">
               <small class="text-muted d-block text-uppercase fw-bold" style="font-size: 0.65rem;">Detected Pool Balance</small>
               <span class="h5 mb-0 fw-bold" id="disp-pool-remaining">₹0.00</span>
          </div>
          <div class="col border-start text-center">
               <small class="text-muted d-block" style="font-size: 0.65rem;">Total Allocated</small>
               <span class="fw-bold text-muted" id="disp-pool-allocated">₹0.00</span>
          </div>
          <div class="col border-start text-end">
               <small class="text-muted d-block" style="font-size: 0.65rem;">Total Distributed</small>
               <span class="fw-bold text-muted" id="disp-pool-distributed">₹0.00</span>
          </div>
     </div>
</div>

<!-- 3. NUMERICS -->
<h6 class="text-dark fw-semibold border-bottom pb-2 mb-3 mt-4">2. Financial Distribution Matrix</h6>
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <label class="form-label fw-bold small required">Distribution Amount (₹)</label>
        <input type="number" step="0.01" name="distribution_amount" id="amount" class="form-control fw-bold calc-trigger" value="{{ $row->distribution_amount ?? '' }}" @disabled(isset($row))>
        <div class="text-danger small err-msg" id="distribution_amount_error"></div>
    </div>
    <div class="col-md-4">
        <label class="form-label fw-bold small required">TDS Percentage (%)</label>
        <div class="input-group">
            <input type="number" step="0.01" name="tds_percentage" id="tds_pct" class="form-control calc-trigger" value="{{ $row->tds_percentage ?? '0' }}">
            <span class="input-group-text bg-light">%</span>
        </div>
        <div class="text-danger small err-msg" id="tds_percentage_error"></div>
    </div>
    
    <div class="col-md-4">
        <label class="form-label fw-bold small">&nbsp;</label>
        <div class="d-flex align-items-center bg-light rounded border border-dashed" style="height:38px;">
            <div class="d-flex align-items-center justify-content-center w-50 h-100 small border-end">
                <span class="text-muted lh-1" style="font-size:0.7rem;">TDS Amount</span> &nbsp;
                <strong id="calc-tds" class="text-danger lh-1">₹{{ number_format((float)($row->tds_amount ?? 0), 2) }}</strong>
            </div>
            <div class="d-flex align-items-center justify-content-center w-50 h-100 small">
                <span class="text-muted lh-1" style="font-size:0.7rem;">Net Payable</span> &nbsp;
                <strong id="calc-net" class="text-success lh-1">₹{{ number_format((float)($row->net_payable_amount ?? 0), 2) }}</strong>
            </div>
        </div>
    </div>
</div>

<!-- 4. LOGISTICS -->
<h6 class="text-dark fw-semibold border-bottom pb-2 mb-3 mt-4">3. Other Details</h6>
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <label class="form-label fw-bold small">Sanction Order No.</label>
        <input type="text" name="sanction_order_number" class="form-control" value="{{ $row->sanction_order_number ?? '' }}">
    </div>
    <div class="col-md-4">
        <label class="form-label fw-bold small">Sanction Order Date</label>
        <input type="text" name="sanction_order_date" class="form-control datepicker" placeholder="dd-mm-yyyy" value="{{ $row->sanction_order_date_formatted ?? '' }}">
        <div class="text-danger small err-msg" id="sanction_order_date_error"></div>
    </div>
    <div class="col-md-4">
        <label class="form-label fw-bold small">Document</label>
        <input type="file" id="file_upload_raw" class="form-control form-control-sm"   accept=".pdf,application/pdf">
        <input type="hidden" name="upload_document" id="upload_document_hidden" value="{{ $row->upload_document ?? '' }}">
        <div id="upload-status" class="small mt-1">
             @if(isset($row) && $row->upload_document)
                 <div class="d-flex align-items-center gap-2 mt-1">
                     <span class="text-success fw-bold small"><i class="fa fa-check"></i> Uploaded</span>
                     <a href="{{ route('dist.document', $row->id) }}" target="_blank" class="btn btn-xs btn-link p-0 text-info small"><i class="fa fa-eye"></i> View Current</a>
                 </div>
            
             @endif
     <span class="text-muted">
    Note: Accept only PDF file.
</span>
        </div>
    </div>
    <div class="col-12">
        <label class="form-label fw-bold small">Remarks</label>
        <textarea name="remarks" class="form-control" rows="2"></textarea>
    </div>
</div>

<hr class="my-4 opacity-10">

<!-- SUBMISSION -->
<div class="d-flex justify-content-end gap-2">
    <a href="{{ route('dist.index') }}" class="btn btn-secondary px-4">Cancel</a>
    <button type="submit" class="btn btn-primary fw-medium shadow-sm" id="save-btn">
         <i class="fa fa-save me-1"></i> {{ isset($row) ? 'Update Record' : 'Submit Transaction' }}
    </button>
</div><br>
<div id="api-error-alert" class="alert alert-danger alert-dismissible fade show d-none" role="alert">
    <span id="api-error-text"></span>

    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>

<!-- Conditional Ghost Inputs (Ensures disabled fields are delivered to server validation pipeline) -->
@if(isset($row))
    <input type="hidden" name="financial_year" value="{{ $row->financial_year }}">
    <input type="hidden" name="duration_id" value="{{ $row->duration_id }}">
    <input type="hidden" name="sub_duration_id" value="{{ $row->sub_duration_id }}">
    <input type="hidden" name="major_component_id" value="{{ $row->major_component_id }}">
    <input type="hidden" name="sub_component_id" value="{{ $row->sub_component_id }}">
    <input type="hidden" name="distribution_amount" value="{{ $row->distribution_amount }}">
@endif

<script>
document.getElementById('file_upload_raw').addEventListener('change', function () {
    const file = this.files[0];

 if (file && file.type !== 'application/pdf') {
        //alert('Only PDF files are allowed.');
        $('#upload-status').html(
                '<span class="text-danger"><i class="fa-solid fa-xmark"></i> Only PDF files are allowed.</span>'
            );
        this.value = '';
    }
});
</script>