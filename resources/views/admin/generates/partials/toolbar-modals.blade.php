<!-- Modal Export / Import Surat (satu format file: hasil export bisa langsung diimport) -->
<div class="modal fade" id="templateModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Export / Import Surat</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="templateSelect" class="col-form-label">Template Surat</label>
                    <select id="templateSelect" placeholder="Cari template surat...">
                        <option value="">Pilih Template Surat</option>
                        @foreach ($letters as $letter)
                            <option value="{{ $letter->id }}">{{ $letter->title }}</option>
                        @endforeach
                    </select>
                    <small class="text-muted">Kolom file mengikuti variabel tetap & variabel kustom template yang dipilih.</small>
                </div>

                <div class="mb-3">
                    <label for="templateSiteSelect" class="col-form-label">Filter Site</label>
                    <select id="templateSiteSelect" placeholder="Semua Site">
                        <option value="">Semua Site</option>
                        @foreach ($sites as $site)
                            <option value="{{ $site->id }}">{{ $site->name }}</option>
                        @endforeach
                    </select>
                    <small class="text-muted">Export: hanya karyawan site ini. Import: hanya karyawan site ini yang diproses.</small>
                </div>

                <ul class="nav nav-tabs mb-3" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" data-bs-toggle="tab" href="#tplExportTab">1. Export</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#tplImportTab">2. Import</a>
                    </li>
                </ul>

                <div class="tab-content">
                    <div id="tplExportTab" class="tab-pane fade show active">
                        <p class="text-muted small mb-3">Unduh file Excel sesuai template terpilih. Kolom otomatis (nama, jabatan, gaji, dll) terisi dari data karyawan sebagai referensi; kolom input & kustom dikosongkan untuk diisi, lalu import kembali file yang sama.</p>
                        <form action="{{ route('generates.export-template') }}" method="POST">
                            @csrf
                            <input type="hidden" name="letter_id" class="tpl-letter-id">
                            <input type="hidden" name="site_id" class="tpl-site-id">
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" name="empty_template" value="1" id="emptyTemplate">
                                <label class="form-check-label" for="emptyTemplate">
                                    Template kosong (hanya header, tanpa data karyawan)
                                </label>
                            </div>
                            <button type="submit" class="btn btn-success w-100 tpl-submit" disabled>
                                <i class="fas fa-download me-1"></i> Download File
                            </button>
                        </form>
                    </div>

                    <div id="tplImportTab" class="tab-pane fade">
                        <form action="{{ route('generates.import-template') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="letter_id" class="tpl-letter-id">
                            <input type="hidden" name="site_id" class="tpl-site-id">
                            <div class="mb-3">
                                <label for="templateFile" class="col-form-label">File Excel</label>
                                <input type="file" name="file" id="templateFile" class="form-control" accept=".xlsx,.xls,.csv">
                            </div>
                            <div class="alert alert-light border small mb-3">
                                Gunakan <strong>file hasil Export</strong> dengan template yang sama. Setiap baris akan membuat <strong>surat terbit baru</strong>; karyawan dicocokkan berdasarkan kolom <strong>NIK Karyawan</strong>.
                            </div>
                            <button type="submit" class="btn btn-primary w-100 tpl-submit" disabled>
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
                <div class="modal-body text-center">
                    @php $currentEsign = Auth::user()->profile?->esign; @endphp
                    @if($currentEsign)
                        @if(str_starts_with($currentEsign, 'data:image/'))
                            <img src="{{ $currentEsign }}" alt="Tanda Tangan" style="max-width:100%;max-height:200px;object-fit:contain;">
                        @else
                            {!! $currentEsign !!}
                        @endif
                    @else
                        Profil tidak ditemukan
                    @endif
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

<!-- Modal Revisi Periode Kontrak (massal) -->
<div class="modal fade" id="reviseContractModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('generates.bulkReviseContract') }}" method="POST" id="bulkReviseContractForm">
                @csrf
                <input type="hidden" name="ids" id="bulkReviseContractIds">
                <div class="modal-header">
                    <h5 class="modal-title">Revisi Periode Kontrak</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info small py-2">
                        Berlaku untuk <strong><span id="bulkReviseContractCount">0</span> surat</strong> yang dicentang di tabel.
                        Kosongkan tanggal yang tidak ingin diubah.
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tanggal Mulai Kontrak (baru)</label>
                            <input type="date" name="start_date" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tanggal Akhir Kontrak (baru)</label>
                            <input type="date" name="end_date" class="form-control">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Alasan Revisi</label>
                        <textarea name="description" class="form-control" rows="2" placeholder="Contoh: Koreksi salah input tanggal PKWT batch November"></textarea>
                    </div>
                    <small class="text-muted d-block mb-3">
                        Surat (lihat/PDF/cetak) langsung memakai periode baru — nomor surat & tanda tangan tetap.
                        Profil karyawan ikut diperbarui bila surat ini kontrak terbarunya, dan perubahan dicatat di Riwayat Karier.
                    </small>

                    @php $reviseTemplates = \App\Models\MessageTemplate::active()->orderBy('name')->get(['id', 'name']); @endphp
                    <div class="border rounded p-3 bg-light">
                        <div class="fw-semibold mb-2"><i class="ti ti-bell me-1 text-primary"></i>Kirim Notifikasi ke Karyawan</div>
                        <div class="d-flex flex-wrap gap-3 mb-2">
                            <div class="form-check">
                                <input class="form-check-input revise-notify-channel" type="checkbox" name="notify_channels[]" value="whatsapp" id="reviseNotifyWa">
                                <label class="form-check-label" for="reviseNotifyWa"><i class="ti ti-brand-whatsapp text-success me-1"></i>WhatsApp</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input revise-notify-channel" type="checkbox" name="notify_channels[]" value="email" id="reviseNotifyEmail">
                                <label class="form-check-label" for="reviseNotifyEmail"><i class="ti ti-mail text-primary me-1"></i>Email</label>
                            </div>
                        </div>
                        @if ($reviseTemplates->isEmpty())
                            <div class="small text-muted">Belum ada template aktif; pesan bawaan akan dipakai.
                                <a href="{{ route('message-templates.index') }}" target="_blank">Buat template</a></div>
                        @else
                            <select name="message_template_id" class="form-select form-select-sm" id="reviseNotifyTemplate" disabled>
                                @foreach ($reviseTemplates as $tpl)
                                    <option value="{{ $tpl->id }}">{{ $tpl->name }}</option>
                                @endforeach
                            </select>
                        @endif
                        <small class="text-muted d-block mt-1">
                            Variabel tambahan di template: <code>{no_surat}</code>, <code>{periode_lama}</code>, <code>{periode_baru}</code>, <code>{mulai}</code>, <code>{selesai}</code>.
                            Tidak dicentang = tidak ada notifikasi.
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light me-2" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Perbarui Surat</button>
                </div>
            </form>
        </div>
    </div>
</div>
