<div class="modal fade" id="importModal" tabindex="-1" role="dialog" aria-labelledby="importModalTitle"
                aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form id="importForm" action="{{ route('import.process') }}" method="POST"
                enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="importModalTitle">Import Surat</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div>
                        <div class="mb-3">
                            <label for="template" class="col-form-label">Template</label>
                            <select name="template" id="template" class="form-select">
                                <option value="">Pilih Template Surat</option>
                                @foreach ($letters as $letter)
                                    <option value="{{ $letter->id }}">{{ $letter->title }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="site" class="col-form-label">Lokasi Area</label>
                            <select name="site" id="site" class="select2 form-select">
                                <option value="">Pilih Lokasi Area</option>
                                @foreach ($sites as $site)
                                    <option value="{{ $site->id }}">{{ $site->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="start_date" class="col-form-label">Tanggal Mulai</label>
                            <input type="date" name="start_date" id="start_date" class="form-control">
                        </div>

                        <div class="mb-3">
                            <label for="end_date" class="col-form-label">Tanggal Selesai</label>
                            <input type="date" name="end_date" id="end_date" class="form-control">
                        </div>

                        <div class="mb-3">
                            <label for="file" class="col-form-label">File Import</label>
                            <input type="file" name="file" id="file" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light me-2" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Import <i
                            class="fas fa-file-import ms-1"></i></button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal untuk tanda tangan -->
<div class="modal fade" id="signaturemodal" tabindex="-1" role="dialog" aria-labelledby="signaturemodalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            @if (Auth::user()->profile && Auth::user()->profile->esign == null) 
                <form action="{{ route('save.signature') }}" method="POST" id="signatureForm">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="signaturemodalTitle">Buat Tanda Tangan</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <canvas id="signatureCanvas" width="500" height="200"></canvas>
                        <!-- Hidden input to store the signature -->
                        <input type="hidden" name="signature" id="signatureInput">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light me-2" data-bs-dismiss="modal">batal</button>
                        <button type="submit" class="btn btn-primary me-2" id="saveSignature">Buat</button>
                        <button type="button" class="btn btn-warning" id="resetSignature">Reset</button>
                    </div>
                </form>
            @else
                <div class="modal-header">
                    <h5 class="modal-title" id="signaturemodalTitle">Tanda Tangan Anda</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    {!! Auth::user()->profile ? Auth::user()->profile->esign : 'Profil tidak ditemukan' !!}
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <form action="{{ route('delete.signature') }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Hapus Tanda Tangan</button>
                    </form>
                </div>
            @endif
        </div>
    </div>
</div>

<div class="modal fade" id="approvemodal" tabindex="-1" role="dialog" aria-labelledby="approvemodalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form action="{{ route('generates.bulkApprove') }}" method="POST" id="bulkApproveForm">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="signaturemodalTitle">Bulk Approve</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Apakah Anda yakin ingin menanda tanganin yang anda pilih?</p>
                    <input type="hidden" name="esign" value="{{ Auth::user()->profile ? Auth::user()->profile->esign : 'Profil tidak ditemukan' }}">
                    <input type="hidden" name="ids" id="bulkApproveIds">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light me-2" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="bulk-update-btn">Approve</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="deletemodal" tabindex="-1" role="dialog" aria-labelledby="deletemodalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form action="{{ route('generates.bulkDelete') }}" method="POST" id="bulkDeleteForm">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="signaturemodalTitle">Bulk Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Apakah Anda yakin ingin menghapus yang anda pilih?</p>
                    <input type="hidden" name="ids" id="bulkDeleteIds">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light me-2"" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-danger" id="bulk-delete-btn">Hapus</button>
                </div>
            </form>                     
        </div>
    </div>
</div>
