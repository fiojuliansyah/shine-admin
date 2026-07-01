<div class="modal fade" id="addVariable" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary">
                <h5 class="modal-title text-white">Tambah Variabel Kustom</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="form-label fw-bold">Nama Keterangan</label>
                    <input type="text" id="var_name" class="form-control" placeholder="Contoh: No Sertifikat">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Kode Variabel</label>
                    <div class="input-group">
                        <span class="input-group-text">[</span>
                        <input type="text" id="var_code" class="form-control" placeholder="no_sertifikat">
                        <span class="input-group-text">]</span>
                    </div>
                </div>
                <div class="mt-4">
                    <h6 class="fw-bold border-bottom pb-2">Variabel Terpasang:</h6>
                    <ul class="list-group list-group-flush" id="tempVarList">
                        @if($letter && $letter->customVariables)
                            @foreach($letter->customVariables as $cv)
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <div><span class="fw-medium">{{ $cv->name }}</span> <code class="ms-1">[{{ $cv->variable }}]</code></div>
                                    <button type="button" class="btn btn-sm btn-outline-danger border-0" onclick="removeVar(this, '{{ $cv->id }}')"><i class="ti ti-trash"></i></button>
                                </li>
                            @endforeach
                        @endif
                    </ul>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary" onclick="addNewVariable()">Simpan Variabel</button>
            </div>
        </div>
    </div>
</div>
