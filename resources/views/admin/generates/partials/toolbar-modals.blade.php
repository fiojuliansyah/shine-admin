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

{{-- Bulk Approve / Bulk Delete: konfirmasi lewat dialog seragam (AppDialog), form tersembunyi --}}
<form action="{{ route('generates.bulkApprove') }}" method="POST" id="bulkApproveForm" class="d-none"
    data-loading-text="Menandatangani surat...">
    @csrf
    <input type="hidden" name="ids" id="bulkApproveIds">
</form>
<form action="{{ route('generates.bulkDelete') }}" method="POST" id="bulkDeleteForm" class="d-none"
    data-loading-text="Menghapus surat...">
    @csrf
    <input type="hidden" name="ids" id="bulkDeleteIds">
</form>

<!-- Modal Revisi Surat Massal: kolom mengikuti template surat yang dicentang -->
<div class="modal fade" id="reviseContractModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <form action="{{ route('generates.bulkRevise') }}" method="POST" id="bulkReviseContractForm" class="d-flex flex-column h-100">
                @csrf
                <input type="hidden" name="ids" id="bulkReviseContractIds">
                <div class="modal-header">
                    <h5 class="modal-title">Revisi Surat Massal</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info small py-2 mb-3">
                        Berlaku untuk <strong><span id="bulkReviseContractCount">0</span> surat</strong> yang dicentang
                        <span id="bulkReviseLetters"></span>.
                        Kolom mengikuti template surat; <strong>kosongkan kolom yang tidak ingin diubah</strong>.
                    </div>

                    <div id="bulkReviseLoading" class="text-center py-4 d-none">
                        <div class="spinner-border text-primary"></div>
                        <div class="small text-muted mt-2">Memuat kolom template surat...</div>
                    </div>

                    <div id="bulkReviseFieldsWrap">
                        <h6 class="mb-2 d-none" id="bulkReviseFieldsTitle">Isi Surat</h6>
                        <div class="row" id="bulkReviseFields"></div>
                        <h6 class="mb-2 mt-2 d-none" id="bulkReviseVarsTitle">Variabel Template</h6>
                        <div class="row" id="bulkReviseVars"></div>
                        <div class="text-muted small d-none" id="bulkReviseEmpty">
                            Template surat terpilih tidak memiliki kolom yang bisa direvisi per surat.
                        </div>
                    </div>

                    <div class="mb-3 mt-2">
                        <label class="form-label">Alasan Revisi</label>
                        <textarea name="description" class="form-control" rows="2" placeholder="Contoh: Koreksi salah input tanggal PKWT batch November"></textarea>
                    </div>
                    <small class="text-muted d-block mb-3">
                        Nomor surat & tanda tangan tetap. Surat (lihat/PDF/cetak) langsung memakai isi baru dan perubahan dicatat di Riwayat Karier.
                        Data seperti nama, alamat, jabatan, site & gaji diambil dari data karyawan — ubah di menu karyawan.
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
                            Variabel tambahan di template pesan: <code>{no_surat}</code>, <code>{perubahan}</code>, <code>{periode_lama}</code>,
                            <code>{periode_baru}</code>, <code>{mulai}</code>, <code>{selesai}</code>. Tidak dicentang = tidak ada notifikasi.
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
