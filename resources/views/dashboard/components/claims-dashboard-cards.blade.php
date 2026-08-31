  <div class="row g-4 mb-3">

      <!-- 1. Total Claims Submitted -->
      <div class="col-lg-4 col-md-6 col-sm-12">
          <div class="card custom-card-nsic all-card">
              <div class=" "></div>
              <div class="card-body">
                  <span class="card-badge badge-blue">ALL</span>
                  <div class="stats-box-nsic">
                      <div class="stats-icon">
                          <i class="bi bi-record-circle"></i>
                      </div>
                      <div>
                          <small class="text-muted d-block" style="font-size:11px;">Total Claims</small>
                          <h5 class="fw-bold mb-0 big-count">{{$total_claims_submitted_by_np}}</h5>
                      </div>
                  </div>
                  <h6 class="fw-bold mb-1">Total Claims Submitted</h6>
                  <p class="text-muted mb-2" style="font-size:12px;">Total claims raised by NP</p>
                  <div class="meta-row">
                      <div><span class="meta-label">BATCHES</span><span
                              class="meta-value">{{$total_batches_submitted_by_np}}</span></div>
                      <div class="total-amount"><span class="meta-label">TOTAL AMOUNT</span><span
                              class="meta-value">{{$total_amount_submitted_by_nps}}</span></div>
                  </div>
                  <!-- <a href="#" class="explore-btn">Explore →</a> -->
              </div>
          </div>
      </div>

      <!-- 2. Approved Claims -->
      <div class="col-lg-4 col-md-6 col-sm-12">
          <div class="card custom-card-nsic approved-card">
              <div class=" "></div>
              <div class="card-body">
                  <span class="card-badge badge-green">APPROVED</span>
                  <div class="stats-box-nsic">
                      <div class="stats-icon">
                          <i class="bi bi-check-circle"></i>
                      </div>
                      <div>
                          <small class="text-muted d-block" style="font-size:11px;">Approved Claims</small>
                          <h5 class="fw-bold mb-0 big-count">{{$total_approved_claims}}</h5>
                      </div>
                  </div>
                  <h6 class="fw-bold mb-1">Approved Claims</h6>
                  <p class="text-muted mb-2" style="font-size:12px;">Successfully approved claims</p>
                  <div class="meta-row">
                      <div><span class="meta-label">BATCHES</span><span
                              class="meta-value">{{$total_approved_batches}}</span></div>
                      <div class="total-amount"><span class="meta-label">TOTAL AMOUNT</span><span
                              class="meta-value">{{$total_amount_approved}}</span></div>
                  </div>
                  <!-- <a href="#" class="explore-btn">Explore →</a> -->
              </div>
          </div>
      </div>

         <!-- 3. Payment Completed  -->
      <div class="col-lg-4 col-md-6 col-sm-12">
          <div class="card custom-card-nsic approved-card">
              <div class=" "></div>
              <div class="card-body">
                  <span class="card-badge badge-green">Payment Completed</span>
                  <div class="stats-box-nsic">
                      <div class="stats-icon">
                          <i class="bi bi-check-circle"></i>
                      </div>
                      <div>
                          <small class="text-muted d-block" style="font-size:11px;">Payment Completed  Claims</small>
                          <h5 class="fw-bold mb-0 big-count">{{$total_payment_completed_claims}}</h5>
                      </div>
                  </div>
                  <h6 class="fw-bold mb-1">Payment Completed  Claims</h6>
                  <p class="text-muted mb-2" style="font-size:12px;">Successfully payment completed claims </p>
                  <div class="meta-row">
                      <div><span class="meta-label">BATCHES</span><span
                              class="meta-value">{{$total_payment_completed_batches}}</span></div>
                      <div class="total-amount"><span class="meta-label">TOTAL AMOUNT</span><span
                              class="meta-value">{{$total_amount_payment_completed}}</span></div>
                  </div>
                  <!-- <a href="#" class="explore-btn">Explore →</a> -->
              </div>
          </div>
      </div>

      <!-- 4. Rejected Claims -->
      <div class="col-lg-4 col-md-6 col-sm-12">
          <div class="card custom-card-nsic rejected-card">
              <div class=" "></div>
              <div class="card-body">
                  <span class="card-badge badge-red">REJECTED</span>
                  <div class="stats-box-nsic">
                      <div class="stats-icon">
                          <i class="bi bi-x-circle"></i>
                      </div>
                      <div>
                          <small class="text-muted d-block" style="font-size:11px;">Rejected Claims</small>
                          <h5 class="fw-bold mb-0 big-count">{{ $total_rejected_claims }}</h5>
                      </div>
                  </div>
                  <h6 class="fw-bold mb-1">Rejected Claims</h6>
                  <p class="text-muted mb-2" style="font-size:12px;">Rejected claims</p>
                  <div class="meta-row">
                      <div><span class="meta-label">No of Claims</span><span
                              class="meta-value">{{$total_rejected_claims}}</span></div>
                      <div class="total-amount"><span class="meta-label">TOTAL AMOUNT</span><span
                              class="meta-value">{{$total_amount_rejected}}</span></div>
                  </div>
                  <!-- <a href="#" class="explore-btn">Explore →</a> -->
              </div>
          </div>
      </div>

      <!-- 5. Pending Claims -->
      <div class="col-lg-4 col-md-6 col-sm-12">
          <div class="card custom-card-nsic pending-card-new">
              <div class=" "></div>
              <div class="card-body">
                  <span class="card-badge badge-orange">PENDING</span>
                  <div class="stats-box-nsic">
                      <div class="stats-icon">
                          <i class="bi bi-hourglass-split"></i>
                      </div>
                      <div>
                          <small class="text-muted d-block" style="font-size:11px;">Pending Claims</small>
                          <h5 class="fw-bold mb-0 big-count">{{ $total_claims_pending }}</h5>
                      </div>
                  </div>
                  <h6 class="fw-bold mb-1">Pending Claims</h6>
                  <p class="text-muted mb-2" style="font-size:12px;">Pending claims across all stages</p>
                  <div class="meta-row">
                      <div><span class="meta-label">BATCHES</span><span
                              class="meta-value">{{$total_batches_pending}}</span></div>
                      <div class="total-amount"><span class="meta-label">TOTAL AMOUNT</span><span
                              class="meta-value">{{$total_amount_pending}}</span></div>
                  </div>
                  <!-- <a href="#" class="explore-btn">Explore →</a> -->
              </div>
          </div>
      </div>

      <!-- 6. Pending with ONDC -->
      <div class="col-lg-4 col-md-6 col-sm-12">
          <div class="card custom-card-nsic ondc-card">
              <div class=" "></div>
              <div class="card-body">
                  <span class="card-badge badge-indigo">ONDC</span>
                  <div class="stats-box-nsic">
                      <div class="stats-icon">
                          <i class="bi bi-record-circle"></i>
                      </div>
                      <div>
                          <small class="text-muted d-block" style="font-size:11px;">Pending with ONDC</small>
                          <h5 class="fw-bold mb-0 big-count"> {{$total_claims_pending_with_ondc}}</h5>
                      </div>
                  </div>
                  <h6 class="fw-bold mb-1">Pending with ONDC</h6>
                  <p class="text-muted mb-2" style="font-size:12px;">Claims awaiting ONDC action</p>
                  <div class="meta-row">
                      <div><span class="meta-label">BATCHES</span><span
                              class="meta-value">{{$total_batches_pending_with_ondc}}</span></div>
                      <div class="total-amount"><span class="meta-label">TOTAL AMOUNT</span><span
                              class="meta-value">{{$total_amount_pending_with_ondc}}</span></div>
                  </div>
                  <!-- <a href="#" class="explore-btn">Explore →</a> -->
              </div>
          </div>
      </div>

      <!-- 7. Pending with NSIC -->
      <div class="col-lg-4 col-md-6 col-sm-12">
          <div class="card custom-card-nsic nsic-card">
              <div class=" "></div>
              <div class="card-body">
                  <span class="card-badge badge-purple">NSIC</span>
                  <div class="stats-box-nsic">
                      <div class="stats-icon">
                          <i class="bi bi-record-circle"></i>
                      </div>
                      <div>
                          <small class="text-muted d-block" style="font-size:11px;">Pending with NSIC</small>
                          <h5 class="fw-bold mb-0 big-count">{{$total_claims_pending_with_nsic}}</h5>
                      </div>
                  </div>
                  <h6 class="fw-bold mb-1">Pending with NSIC</h6>
                  <p class="text-muted mb-2" style="font-size:12px;">Claims awaiting NSIC review</p>
                  <div class="meta-row">
                      <div><span class="meta-label">BATCHES</span><span
                              class="meta-value">{{$total_batches_pending_with_nsic}}</span></div>
                      <div class="total-amount"><span class="meta-label">TOTAL AMOUNT</span><span
                              class="meta-value">{{$total_amount_pending_with_nsic}}</span></div>
                  </div>
                  <!-- <a href="#" class="explore-btn">Explore →</a> -->
              </div>
          </div>
      </div>

      <!-- 8. Pending with SNP -->
      <div class="col-lg-4 col-md-6 col-sm-12">
          <div class="card custom-card-nsic snp-card-new">
              <div class=" " style="background: #fbbf24; !important"></div>
              <div class="card-body">
                  <span class="card-badge badge-amber">NP</span>
                  <div class="stats-box-nsic">
                      <div class="stats-icon">
                          <i class="bi bi-record-circle"></i>
                      </div>
                      <div>
                          <small class="text-muted d-block" style="font-size:11px;">Pending with NP</small>
                          <h5 class="fw-bold mb-0 big-count">{{$total_claims_pending_with_nps}}</h5>
                      </div>
                  </div>
                  <h6 class="fw-bold mb-1">Pending with NP</h6>
                  <p class="text-muted mb-2" style="font-size:12px;">Claims where additional documents or information
                      are requested from NP</p>
                  <div class="meta-row">
                      <div><span class="meta-label">BATCHES</span><span
                              class="meta-value">{{$total_batches_pending_with_nps}}</span></div>
                      <div class="total-amount"><span class="meta-label">TOTAL AMOUNT</span><span
                              class="meta-value">{{$total_amount_pending_with_nps}}</span></div>
                  </div>
                  <!-- <a href="#" class="explore-btn">Explore →</a> -->
              </div>
          </div>
      </div>

      <!-- 9. Pending with NSIC Finance -->
      <div class="col-lg-4 col-md-6 col-sm-12">
          <div class="card custom-card-nsic finance-card">
              <div class=" "></div>
              <div class="card-body">
                  <span class="card-badge badge-teal">FINANCE</span>
                  <div class="stats-box-nsic">
                      <div class="stats-icon">
                          <i class="bi bi-coin"></i>
                      </div>
                      <div>
                          <small class="text-muted d-block" style="font-size:11px;">Pending with Finance</small>
                          <h5 class="fw-bold mb-0 big-count">{{$total_claims_pending_with_finance}}</h5>
                      </div>
                  </div>
                  <h6 class="fw-bold mb-1">Pending with NSIC Finance</h6>
                  <p class="text-muted mb-2" style="font-size:12px;">Claims forwarded to Finance team for processing</p>
                  <div class="meta-row">
                      <div><span class="meta-label">BATCHES</span><span
                              class="meta-value">{{$total_batches_pending_with_finance}}</span></div>
                      <div class="total-amount"><span class="meta-label">TOTAL AMOUNT</span><span
                              class="meta-value">{{$total_amount_pending_with_finance}}</span></div>
                  </div>
                  <!-- <a href="#" class="explore-btn">Explore →</a> -->
              </div>
          </div>
      </div>
  </div>

  <style>
/* ── Base Card ── */
.custom-card-nsic {
    border: none;
    border-radius: 14px;
    overflow: hidden;
    box-shadow: 0 1px 4px rgba(0, 0, 0, 0.06);
    height: 100%;
}

/* ── Colored top header bar ── */
.  {
    height: 5px;
    width: 100%;
}

/* Row 1 colors */
.all-card .  {
    background: #2563eb;
}

.approved-card .  {
    background: #16a34a;
}

.rejected-card .  {
    background: #dc2626;
}

.pending-card-new .  {
    background: #ea580c;
}

/* Row 2 colors */
.ondc-card .  {
    background: #4338ca;
}

.nsic-card .  {
    background: #7c3aed;
}



.finance-card .  {
    background: #0d9488;
}

/* ── Badge (top-right) ── */
.card-badge {
    font-size: 11px;
    font-weight: 700;
    padding: 3px 11px;
    border-radius: 20px;
    letter-spacing: 0.5px;
    float: right;
}

.badge-blue {
    background: #dbeafe;
    color: #1d4ed8;
}

.badge-green {
    background: #dcfce7;
    color: #15803d;
}

.badge-red {
    background: #fee2e2;
    color: #b91c1c;
}

.badge-orange {
    background: #ffedd5;
    color: #c2410c;
}

.badge-indigo {
    background: #e0e7ff;
    color: #3730a3;
}

.badge-purple {
    background: #ede9fe;
    color: #6d28d9;
}

.badge-amber {
    background: #fef3c7;
    color: #b45309;
}

.badge-teal {
    background: #ccfbf1;
    color: #0f766e;
}

/* ── Stats box (icon + total) ── */
.stats-box-nsic {
    display: flex;
    align-items: center;
    gap: 12px;
    margin: 10px 0 14px;
}

.stats-icon {
    width: 44px;
    height: 44px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    flex-shrink: 0;
}

/* .all-card .stats-icon {
    background: #eff6ff;
    color: #2563eb;
} */

.approved-card .stats-icon {
    background: #f0fdf4;
    color: #16a34a;
}

.rejected-card .stats-icon {
    background: #fef2f2;
    color: #dc2626;
}

.pending-card-new .stats-icon {
    background: #fff7ed;
    color: #ea580c;
}

.ondc-card .stats-icon {
    background: #eef2ff;
    color: #4338ca;
}

.nsic-card .stats-icon {
    background: #f5f3ff;
    color: #7c3aed;
}

.snp-card .stats-icon {
    background: #fffbeb;
    color: #d97706;
}

.finance-card .stats-icon {
    background: #0d9488;
    color: #0d9488;
}

/* ── Big count number ── */
.big-count {
    font-size: 34px;
    font-weight: 700;
    line-height: 1.1;
    margin: 0 0 12px;
}

.all-card .big-count {
    color: #2563eb;
}

.approved-card .big-count {
    color: #16a34a;
}

.rejected-card .big-count {
    color: #dc2626;
}

.pending-card-new .big-count {
    color: #ea580c;
}

.ondc-card .big-count {
    color: #4338ca;
}

.nsic-card .big-count {
    color: #7c3aed;
}

.snp-card .big-count {
    color: #d97706;
}

.finance-card .big-count {
    color: #0d9488;
}

/* ── Meta labels (BATCHES / TOTAL AMOUNT) ── */
.meta-row {
    display: flex;
    gap: 24px;
    margin-bottom: 16px;
}

.meta-label {
    font-size: 10px;
    font-weight: 700;
    color: #9ca3af;
    letter-spacing: 0.5px;
    display: block;
    margin-bottom: 2px;
}

.total-amount {
    margin-left: 39%;
}

.meta-value {
    font-size: 13px;
    font-weight: 600;
    color: #111827;
}

/* ── Info grid (pending/verified/rejected/reverted) ── */
.info-label {
    font-size: 11px;
    color: #6b7280;
    margin: 0 0 2px;
}

.info-value {
    font-size: 15px;
    font-weight: 700;
    color: #111827;
    display: block;
    margin-bottom: 10px;
}

/* ── Explore button ── */
.explore-btn {
    display: block;
    width: 100%;
    background: transparent;
    border: 1.5px solid #d1d5db;
    border-radius: 8px;
    padding: 9px 0;
    font-size: 13px;
    font-weight: 500;
    color: #374151;
    text-align: center;
    cursor: pointer;
    transition: background 0.18s, border-color 0.18s, color 0.18s;
    margin-top: 4px;
    text-decoration: none;
}

.all-card .explore-btn:hover {
    background: #2563eb;
    border-color: #2563eb;
    color: #fff;
}

.approved-card .explore-btn:hover {
    background: #16a34a;
    border-color: #16a34a;
    color: #fff;
}

.rejected-card .explore-btn:hover {
    background: #dc2626;
    border-color: #dc2626;
    color: #fff;
}

.pending-card-new .explore-btn:hover {
    background: #ea580c;
    border-color: #ea580c;
    color: #fff;
}

.ondc-card .explore-btn:hover {
    background: #4338ca;
    border-color: #4338ca;
    color: #fff;
}

.nsic-card .explore-btn:hover {
    background: #7c3aed;
    border-color: #7c3aed;
    color: #fff;
}

.snp-card-new .explore-btn:hover {
    background: #0da436;
    border-color: #177d15;
    color: #fff;
}

.finance-card .explore-btn:hover {
    background: #0d9488;
    border-color: #0d9488;
    color: #fff;
}

.card-body {
    display: flex;
    flex-direction: column;
}
  </style>