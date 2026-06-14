<!-- ═══ Developer Option Button (Password-protected quick access) ═══ -->
<div class="row mt-3">
    <div class="col-12">
        <button type="button" class="btn btn-link text-white-50 w-100 text-center py-2" 
                id="btn-open-developer-option" 
                style="font-size:0.85rem;opacity:0.6;text-decoration:none;border:1px dashed rgba(255,255,255,0.15);border-radius:8px;">
            <i class="fas fa-code me-1"></i> Developer Option
        </button>
    </div>
</div>

<!-- ═══ Modal Developer Option ═══ -->
<div class="modal fade" id="modalDeveloperOption" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
    <div class="modal-dialog modal-fullscreen modal-dialog-centered modal-xl">
        <div class="modal-content bg-dark text-white">
            <div class="modal-header border-secondary">
                <h5 class="modal-title w-100 text-center">Developer Option — Direct Input</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <!-- Score Display Header -->
                <div class="row mb-4">
                    <div class="col-md-5 text-center">
                        <div class="p-3 rounded" style="background:rgba(21,101,192,0.2);border:1px solid rgba(21,101,192,0.4);">
                            <div class="h6 mb-2 text-white-50">SUDUT BIRU</div>
                            <div class="display-4 fw-bold text-white" id="dev-skor-biru">0</div>
                        </div>
                    </div>
                    <div class="col-md-2 d-flex align-items-center justify-content-center">
                        <div class="text-center">
                            <div class="h6 text-white-50">Ronde</div>
                            <div class="h4 text-white fw-bold" id="dev-ronde">1</div>
                        </div>
                    </div>
                    <div class="col-md-5 text-center">
                        <div class="p-3 rounded" style="background:rgba(198,40,40,0.2);border:1px solid rgba(198,40,40,0.4);">
                            <div class="h6 mb-2 text-white-50">SUDUT MERAH</div>
                            <div class="display-4 fw-bold text-white" id="dev-skor-merah">0</div>
                        </div>
                    </div>
                </div>

                <!-- Button Grid -->
                <div class="row g-3">
                    <!-- ═══ Blue Side ═══ -->
                    <div class="col-md-5">
                        <div class="p-3 rounded" style="background:#1a1a1a;border:1px solid #333;">
                            <h6 class="text-center text-white-50 mb-3">BLUE CORNER</h6>
                            
                            <!-- Serangan -->
                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <button type="button" class="btn btn-lg w-100 text-white dev-btn-input" 
                                            data-sudut="biru" data-mode="serangan" data-jumlah="1"
                                            style="background:rgba(21,101,192,0.5);border:none;">
                                        <i class="fas fa-hand-fist me-1"></i> Punch
                                    </button>
                                </div>
                                <div class="col-6">
                                    <button type="button" class="btn btn-lg w-100 text-white dev-btn-input" 
                                            data-sudut="biru" data-mode="serangan" data-jumlah="2"
                                            style="background:rgba(21,101,192,0.5);border:none;">
                                        <i class="fas fa-person-running me-1"></i> Kick
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Jatuhan -->
                            <div class="row g-2 mb-3">
                                <div class="col-8">
                                    <button type="button" class="btn btn-lg w-100 text-white dev-btn-input" 
                                            data-sudut="biru" data-mode="jatuhan" data-jumlah="3"
                                            style="background:rgba(21,101,192,0.5);border:none;">
                                        <i class="fas fa-person-falling me-1"></i> Dropping
                                    </button>
                                </div>
                                <div class="col-4">
                                    <button type="button" class="btn btn-lg w-100 text-white-50 dev-btn-delete" 
                                            data-sudut="biru" data-mode="jatuhan" data-jumlah="hapus"
                                            style="background:transparent;border:1px solid #555;">
                                        Del Drop
                                    </button>
                                </div>
                            </div>
                            <div class="row g-2 mb-2">
                                <div class="col-12">
                                    <button type="button" class="btn w-100 text-white-50 dev-btn-delete" 
                                            data-sudut="biru" data-mode="serangan" data-jumlah="hapus"
                                            style="background:transparent;border:1px solid #555;font-size:0.9rem;">
                                        <i class="fas fa-trash-can me-1"></i> Delete Last Score
                                    </button>
                                </div>
                            </div>

                            <hr class="border-secondary my-3">

                            <!-- Hukuman -->
                            <div class="row g-2">
                                <div class="col-6">
                                    <button type="button" class="btn w-100 text-white dev-btn-input" 
                                            data-sudut="biru" data-mode="binaan" data-jumlah="1"
                                            style="background:rgba(21,101,192,0.35);border:none;font-size:0.85rem;padding:0.6rem;">
                                        Verbal Warn I
                                    </button>
                                </div>
                                <div class="col-6">
                                    <button type="button" class="btn w-100 text-white dev-btn-input" 
                                            data-sudut="biru" data-mode="binaan" data-jumlah="2"
                                            style="background:rgba(21,101,192,0.35);border:none;font-size:0.85rem;padding:0.6rem;">
                                        Verbal Warn II
                                    </button>
                                </div>
                                <div class="col-6">
                                    <button type="button" class="btn w-100 text-white dev-btn-input" 
                                            data-sudut="biru" data-mode="hukuman" data-jumlah="-1"
                                            style="background:rgba(21,101,192,0.35);border:none;font-size:0.85rem;padding:0.6rem;">
                                        -1
                                    </button>
                                </div>
                                <div class="col-6">
                                    <button type="button" class="btn w-100 text-white dev-btn-input" 
                                            data-sudut="biru" data-mode="hukuman" data-jumlah="-2"
                                            style="background:rgba(21,101,192,0.35);border:none;font-size:0.85rem;padding:0.6rem;">
                                        -2
                                    </button>
                                </div>
                                <div class="col-6">
                                    <button type="button" class="btn w-100 text-white dev-btn-input" 
                                            data-sudut="biru" data-mode="hukuman" data-jumlah="-5"
                                            style="background:rgba(21,101,192,0.35);border:none;font-size:0.85rem;padding:0.6rem;">
                                        -5
                                    </button>
                                </div>
                                <div class="col-6">
                                    <button type="button" class="btn w-100 text-white dev-btn-input" 
                                            data-sudut="biru" data-mode="hukuman" data-jumlah="-10"
                                            style="background:rgba(21,101,192,0.35);border:none;font-size:0.85rem;padding:0.6rem;">
                                        -10
                                    </button>
                                </div>
                                <div class="col-12">
                                    <button type="button" class="btn w-100 text-white-50 dev-btn-delete" 
                                            data-sudut="biru" data-mode="binaan" data-jumlah="hapus"
                                            style="background:transparent;border:1px solid #555;font-size:0.85rem;">
                                        <i class="fas fa-trash-can me-1"></i> Delete Binaan
                                    </button>
                                </div>
                                <div class="col-12">
                                    <button type="button" class="btn w-100 text-white-50 dev-btn-delete" 
                                            data-sudut="biru" data-mode="hukuman" data-jumlah="hapus"
                                            style="background:transparent;border:1px solid #555;font-size:0.85rem;">
                                        <i class="fas fa-trash-can me-1"></i> Delete Hukuman
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ═══ Center Info ═══ -->
                    <div class="col-md-2 d-flex align-items-center justify-content-center">
                        <div class="text-center">
                            <i class="fas fa-code text-white-50" style="font-size:2rem;"></i>
                            <p class="text-white-50 mt-2 small">Direct Input<br>Mode</p>
                        </div>
                    </div>

                    <!-- ═══ Red Side (mirror of blue) ═══ -->
                    <div class="col-md-5">
                        <div class="p-3 rounded" style="background:#1a1a1a;border:1px solid #333;">
                            <h6 class="text-center text-white-50 mb-3">RED CORNER</h6>
                            
                            <!-- Serangan -->
                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <button type="button" class="btn btn-lg w-100 text-white dev-btn-input" 
                                            data-sudut="merah" data-mode="serangan" data-jumlah="1"
                                            style="background:rgba(198,40,40,0.5);border:none;">
                                        <i class="fas fa-hand-fist me-1"></i> Punch
                                    </button>
                                </div>
                                <div class="col-6">
                                    <button type="button" class="btn btn-lg w-100 text-white dev-btn-input" 
                                            data-sudut="merah" data-mode="serangan" data-jumlah="2"
                                            style="background:rgba(198,40,40,0.5);border:none;">
                                        <i class="fas fa-person-running me-1"></i> Kick
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Jatuhan -->
                            <div class="row g-2 mb-3">
                                <div class="col-8">
                                    <button type="button" class="btn btn-lg w-100 text-white dev-btn-input" 
                                            data-sudut="merah" data-mode="jatuhan" data-jumlah="3"
                                            style="background:rgba(198,40,40,0.5);border:none;">
                                        <i class="fas fa-person-falling me-1"></i> Dropping
                                    </button>
                                </div>
                                <div class="col-4">
                                    <button type="button" class="btn btn-lg w-100 text-white-50 dev-btn-delete" 
                                            data-sudut="merah" data-mode="jatuhan" data-jumlah="hapus"
                                            style="background:transparent;border:1px solid #555;">
                                        Del Drop
                                    </button>
                                </div>
                            </div>
                            <div class="row g-2 mb-2">
                                <div class="col-12">
                                    <button type="button" class="btn w-100 text-white-50 dev-btn-delete" 
                                            data-sudut="merah" data-mode="serangan" data-jumlah="hapus"
                                            style="background:transparent;border:1px solid #555;font-size:0.9rem;">
                                        <i class="fas fa-trash-can me-1"></i> Delete Last Score
                                    </button>
                                </div>
                            </div>

                            <hr class="border-secondary my-3">

                            <!-- Hukuman -->
                            <div class="row g-2">
                                <div class="col-6">
                                    <button type="button" class="btn w-100 text-white dev-btn-input" 
                                            data-sudut="merah" data-mode="binaan" data-jumlah="1"
                                            style="background:rgba(198,40,40,0.35);border:none;font-size:0.85rem;padding:0.6rem;">
                                        Verbal Warn I
                                    </button>
                                </div>
                                <div class="col-6">
                                    <button type="button" class="btn w-100 text-white dev-btn-input" 
                                            data-sudut="merah" data-mode="binaan" data-jumlah="2"
                                            style="background:rgba(198,40,40,0.35);border:none;font-size:0.85rem;padding:0.6rem;">
                                        Verbal Warn II
                                    </button>
                                </div>
                                <div class="col-6">
                                    <button type="button" class="btn w-100 text-white dev-btn-input" 
                                            data-sudut="merah" data-mode="hukuman" data-jumlah="-1"
                                            style="background:rgba(198,40,40,0.35);border:none;font-size:0.85rem;padding:0.6rem;">
                                        -1
                                    </button>
                                </div>
                                <div class="col-6">
                                    <button type="button" class="btn w-100 text-white dev-btn-input" 
                                            data-sudut="merah" data-mode="hukuman" data-jumlah="-2"
                                            style="background:rgba(198,40,40,0.35);border:none;font-size:0.85rem;padding:0.6rem;">
                                        -2
                                    </button>
                                </div>
                                <div class="col-6">
                                    <button type="button" class="btn w-100 text-white dev-btn-input" 
                                            data-sudut="merah" data-mode="hukuman" data-jumlah="-5"
                                            style="background:rgba(198,40,40,0.35);border:none;font-size:0.85rem;padding:0.6rem;">
                                        -5
                                    </button>
                                </div>
                                <div class="col-6">
                                    <button type="button" class="btn w-100 text-white dev-btn-input" 
                                            data-sudut="merah" data-mode="hukuman" data-jumlah="-10"
                                            style="background:rgba(198,40,40,0.35);border:none;font-size:0.85rem;padding:0.6rem;">
                                        -10
                                    </button>
                                </div>
                                <div class="col-12">
                                    <button type="button" class="btn w-100 text-white-50 dev-btn-delete" 
                                            data-sudut="merah" data-mode="binaan" data-jumlah="hapus"
                                            style="background:transparent;border:1px solid #555;font-size:0.85rem;">
                                        <i class="fas fa-trash-can me-1"></i> Delete Binaan
                                    </button>
                                </div>
                                <div class="col-12">
                                    <button type="button" class="btn w-100 text-white-50 dev-btn-delete" 
                                            data-sudut="merah" data-mode="hukuman" data-jumlah="hapus"
                                            style="background:transparent;border:1px solid #555;font-size:0.85rem;">
                                        <i class="fas fa-trash-can me-1"></i> Delete Hukuman
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-secondary">
                <button type="button" class="btn btn-secondary btn-lg w-100" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
