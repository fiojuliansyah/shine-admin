<div class="modal fade" id="importModal" tabindex="-1" role="dialog" aria-labelledby="importModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form id="importForm" action="{{ route('import.process') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="importModalTitle">Import Surat</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
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

<!-- Modal Export / Import Template Custom -->
<div class="modal fade" id="templateModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Export / Import Template Surat</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="templateSelect" class="col-form-label">Template Surat</label>
                    <select id="templateSelect" class="form-select">
                        <option value="">Pilih Template Surat</option>
                        @foreach ($letters as $letter)
                            <option value="{{ $letter->id }}">{{ $letter->title }}</option>
                        @endforeach
                    </select>
                    <small class="text-muted">Kolom file mengikuti variabel tetap & variabel kustom template yang dipilih.</small>
                </div>

                <ul class="nav nav-tabs mb-3" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" data-bs-toggle="tab" href="#tplExportTab">Export Template</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#tplImportTab">Import Data</a>
                    </li>
                </ul>

                <div class="tab-content">
                    <div id="tplExportTab" class="tab-pane fade show active">
                        <p class="text-muted small mb-3">Unduh file Excel kosong sesuai template terpilih, isi datanya, lalu import kembali.</p>
                        <form action="{{ route('generates.export-template') }}" method="POST">
                            @csrf
                            <input type="hidden" name="letter_id" id="exportLetterId">
                            <button type="submit" class="btn btn-success w-100" id="exportTemplateBtn" disabled>
                                <i class="fas fa-download me-1"></i> Download Template
                            </button>
                        </form>
                    </div>

                    <div id="tplImportTab" class="tab-pane fade">
                        <form action="{{ route('generates.import-template') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="letter_id" id="importLetterId">
                            <div class="mb-3">
                                <label for="templateSite" class="col-form-label">Batasi ke Site (opsional)</label>
                                <select name="site_id" id="templateSite" class="form-select">
                                    <option value="">Semua Site</option>
                                    @foreach ($sites as $site)
                                        <option value="{{ $site->id }}">{{ $site->name }}</option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Jika dipilih, hanya karyawan pada site tersebut yang diproses.</small>
                            </div>
                            <div class="mb-3">
                                <label for="templateFile" class="col-form-label">File Excel</label>
                                <input type="file" name="file" id="templateFile" class="form-control" accept=".xlsx,.xls,.csv">
                            </div>
                            <div class="alert alert-light border small mb-3">
                                Setiap baris akan membuat <strong>surat terbit baru</strong>. Karyawan dicocokkan berdasarkan kolom <strong>NIK Karyawan</strong>. Gunakan file hasil Export template di atas.
                            </div>
                            <button type="submit" class="btn btn-primary w-100" id="importTemplateBtn" disabled>
                                <i class="fas fa-file-import me-1"></i> Import Data
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal untuk tanda tangan -->
<div class="modal fade" id="signaturemodal" tabindex="-1" role="dialog" aria-labelledby="signaturemodalTitle"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            @if (Auth::user()->profile && Auth::user()->profile->esign == null)
                <form action="{{ route('save.signature') }}" method="POST" id="signatureForm"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Buat Tanda Tangan</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <ul class="nav nav-tabs mb-3" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" data-bs-toggle="tab" href="#drawTab">Gambar Manual</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#uploadTab">Upload File</a>
                            </li>
                        </ul>

                        <div class="tab-content">
                            <div id="drawTab" class="tab-pane fade show active">
                                <canvas id="signatureCanvas" width="450" height="200"
                                    style="border: 1px dashed #ccc; background: #f9f9f9;"></canvas>
                                <input type="hidden" name="signature" id="signatureInput">
                            </div>
                            <div id="uploadTab" class="tab-pane fade">
                                <label class="form-label">Upload Foto Tanda Tangan (PNG/JPG)</label>
                                <input type="file" name="signature_file" class="form-control" accept="image/*">
                                <small class="text-muted">Gunakan background putih/transparan untuk hasil
                                    terbaik.</small>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="button" class="btn btn-warning" id="resetSignature">Reset Canvas</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
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

<div class="modal fade" id="approvemodal" tabindex="-1" role="dialog" aria-labelledby="approvemodalTitle"
    aria-hidden="true">
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
                    <input type="hidden" name="esign"
                        value="{{ Auth::user()->profile ? Auth::user()->profile->esign : 'Profil tidak ditemukan' }}">
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

<div class="modal fade" id="deletemodal" tabindex="-1" role="dialog" aria-labelledby="deletemodalTitle"
    aria-hidden="true">
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
