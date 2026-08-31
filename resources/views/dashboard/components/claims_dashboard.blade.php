	<div class="row g-4 mb-4">
                        <!-- Seller Network Participants (SNP) -->
						 <div class="col-lg-4 col-md-6 col-sm-12">
                            <div class="card custom-card-nsic snp-card">
                                <div class="custom-card-nsic-header"></div>
                                <div class="card-body">
                                    <h5 class="fw-bold">Seller Network Participants (SNP)</h5>
                                    <div class="stats-box-nsic">
                                        <div class="stats-icon">
                                            <i class="bi bi-clipboard-check"></i>
                                        </div>
                                        <div>
                                            <small class="text-primary d-block">Total Registrations</small>
                                            <h5 class="fw-bold mb-0 snpCount_totalSnp">
                                                {{ $snp['total_registered_sbl'] ?? '' }}</h5>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-6">
                                            <p class="info-label">Verification Pending </p>
                                            <span
                                                class="info-value snpCount_pendingSnp">{{ $snp['total_pending_sbl'] ?? '' }}</span>
                                        </div>
                                        <div class="col-6">
                                            <p class="info-label">Verified SNP</p>
                                            <span
                                                class="info-value snpCount_approvedSnp">{{ $snp['total_verified_sbl'] ?? '' }}</span>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-6">
                                            <p class="info-label">Rejected SNP</p>
                                            <span
                                                class="info-value snpCount_rejectedSnp">{{ $snp['total_rejected_sbl'] ?? '' }}</span>
                                        </div>
                                        <div class="col-6">
                                            <p class="info-label">Reverted SNP</p>
                                            <span
                                                class="info-value snpCount_revertedSnp">{{ $snp['total_reverted_sbl'] ?? '' }}</span>
                                        </div>
                                    </div>
                                    {{-- <a href="{{ url('mis-snp-registration-reports') }}" class="explore-link">Explore</a> --}}
                                </div>
                            </div>
                        </div>
						
						<!-- Buyer Network Participants (BNP)-->
						<div class="col-lg-4 col-md-6 col-sm-12">
                            <div class="card custom-card-nsic bnp-card">
                                <div class="custom-card-nsic-header"></div>
                                <div class="card-body">
                                    <h5 class="fw-bold">Buyer Network Participants (BNP)</h5>
                                    <div class="stats-box-nsic">
                                        <div class="stats-icon">
                                            <i class="bi bi-clipboard-check"></i>
                                        </div>
                                        <div>
                                            <small class="text-primary d-block">Total Registrations</small>
                                            <h5 class="fw-bold mb-0 bnpCount_totalBnp">
                                                {{ $bnp['total_registered_sbl'] ?? '' }}</h5>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-6">
                                            <p class="info-label">Verification Pending </p>
                                            <span
                                                class="info-value bnpCount_pendingBnp">{{ $bnp['total_pending_sbl'] ?? '' }}</span>
                                        </div>
                                        <div class="col-6">
                                            <p class="info-label">Verified SNP</p>
                                            <span
                                                class="info-value bnpCount_approvedBnp">{{ $bnp['total_verified_sbl'] ?? '' }}</span>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-6">
                                            <p class="info-label">Rejected SNP</p>
                                            <span
                                                class="info-value bnpCount_rejectedBnp">{{ $bnp['total_rejected_sbl'] ?? '' }}</span>
                                        </div>
                                        <div class="col-6">
                                            <p class="info-label">Reverted SNP</p>
                                            <span
                                                class="info-value bnpCount_revertedBnp">{{ $bnp['total_reverted_sbl'] ?? '' }}</span>
                                        </div>
                                    </div>
                                    {{-- <a href="{{ url('mis-snp-registration-reports') }}" class="explore-link">Explore</a> --}}
                                </div>
                            </div>
                        </div>
						
						 <!-- Seller Network Participants (SNP) -->
						<div class="col-lg-4 col-md-6 col-sm-12">
                            <div class="card custom-card-nsic claim-card">
                                <div class="custom-card-nsic-header"></div>
                                <div class="card-body">
                                    <h5 class="fw-bold">Logistics Service Providers (LSP)</h5>
                                    <div class="stats-box-nsic">
                                        <div class="stats-icon">
                                            <i class="bi bi-clipboard-check"></i>
                                        </div>
                                        <div>
                                            <small class="text-primary d-block">Total Registrations</small>
                                            <h5 class="fw-bold mb-0 lspCount_totalLsp">
                                                {{ $lsp['total_registered_sbl'] ?? '' }}</h5>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-6">
                                            <p class="info-label">Verification Pending </p>
                                            <span
                                                class="info-value lspCount_pendingLsp">{{ $lsp['total_pending_sbl'] ?? '' }}</span>
                                        </div>
                                        <div class="col-6">
                                            <p class="info-label">Verified LSP</p>
                                            <span
                                                class="info-value lspCount_approvedLsp">{{ $lsp['total_verified_sbl'] ?? '' }}</span>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-6">
                                            <p class="info-label">Rejected LSP</p>
                                            <span
                                                class="info-value lspCount_rejectedLsp">{{ $lsp['total_rejected_sbl'] ?? '' }}</span>
                                        </div>
                                        <div class="col-6">
                                            <p class="info-label">Reverted LSP</p>
                                            <span
                                                class="info-value lspCount_revertedLsp">{{ $lsp['total_reverted_sbl'] ?? '' }}</span>
                                        </div>
                                    </div>
                                    {{-- <a href="{{ url('mis-snp-registration-reports') }}" class="explore-link">Explore</a> --}}
                                </div>
                            </div>
                        </div>


                    </div>
                </div>