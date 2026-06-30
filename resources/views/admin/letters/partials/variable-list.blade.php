<div class="modal fade" id="lihatVariable" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-dark">
                <h5 class="modal-title text-white">Klik Variable Untuk Menyalin</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-4">
                    <div class="col-md-4">
                        <h6 class="fw-bold border-bottom pb-2 mb-3 text-primary">KOP SURAT</h6>
                        <div class="list-group list-group-flush">
                            <button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" onclick="copyVar('[no_surat]')">No Surat <code>[no_surat]</code> <i class="ti ti-copy"></i></button>
                            <button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" onclick="copyVar('[romawi]')">Bulan Romawi <code>[romawi]</code> <i class="ti ti-copy"></i></button>
                            <button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" onclick="copyVar('[tahun]')">Tahun <code>[tahun]</code> <i class="ti ti-copy"></i></button>
                            <button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center bg-light" onclick="copyVar('[tgl_surat]')">Tgl Terbit <code>[tgl_surat]</code> <i class="ti ti-copy text-primary"></i></button>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <h6 class="fw-bold border-bottom pb-2 mb-3 text-primary">ISI SURAT</h6>
                        <div class="list-group list-group-flush">
                            <button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" onclick="copyVar('[hari]')">Hari <code>[hari]</code> <i class="ti ti-copy"></i></button>
                            <button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" onclick="copyVar('[mulai]')">Tgl Mulai Kontrak<code>[mulai]</code> <i class="ti ti-copy"></i></button>
                            <button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" onclick="copyVar('[selesai]')">Tgl Selesai Kontrak<code>[selesai]</code> <i class="ti ti-copy"></i></button>
                            <button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" onclick="copyVar('[sign_2]')">Tanda Tangan karyawan <code>[sign_2]</code> <i class="ti ti-copy"></i></button>
                            <button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" onclick="copyVar('[esign]')">Tanda Tangan HRD <code>[esign]</code> <i class="ti ti-copy"></i></button>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <h6 class="fw-bold border-bottom pb-2 mb-3 text-primary">INFORMASI PEGAWAI</h6>
                        <div class="list-group list-group-flush">
                            <button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" onclick="copyVar('[no_karyawan]')">No Karyawan <code>[no_karyawan]</code> <i class="ti ti-copy"></i></button>
                            <button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" onclick="copyVar('[nama_karyawan]')">Nama Karyawan <code>[nama_karyawan]</code> <i class="ti ti-copy"></i></button>
                            <button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" onclick="copyVar('[nik_ktp]')">NIK KTP <code>[nik_ktp]</code> <i class="ti ti-copy"></i></button>
                            <button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" onclick="copyVar('[jenis_kelamin]')">Jenis Kelamin <code>[jenis_kelamin]</code> <i class="ti ti-copy"></i></button>
                            <button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" onclick="copyVar('[ttl]')">TTL <code>[ttl]</code> <i class="ti ti-copy"></i></button>
                            <button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" onclick="copyVar('[alamat]')">Alamat <code>[alamat]</code> <i class="ti ti-copy"></i></button>
                            <button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" onclick="copyVar('[handphone]')">No HP <code>[handphone]</code> <i class="ti ti-copy"></i></button>
                        </div>
                    </div>
                    <div class="col-md-4 border-top pt-3">
                        <h6 class="fw-bold border-bottom pb-2 mb-3 text-primary">INFORMASI LOKASI PROJECT</h6>
                        <div class="list-group list-group-flush">
                            <button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" onclick="copyVar('[lokasi_project]')">Lokasi Project <code>[lokasi_project]</code> <i class="ti ti-copy"></i></button>
                            <button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" onclick="copyVar('[area]')">Area <code>[area]</code> <i class="ti ti-copy"></i></button>
                            <button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" onclick="copyVar('[nama_client]')">Nama Client <code>[nama_client]</code> <i class="ti ti-copy"></i></button>
                            <button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" onclick="copyVar('[jabatan_client]')">Jabatan Client <code>[jabatan_client]</code> <i class="ti ti-copy"></i></button>
                            <button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" onclick="copyVar('[area_description]')">Deskripsi Client<code>[area_description]</code> <i class="ti ti-copy"></i></button>
                            <button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" onclick="copyVar('[jabatan]')">Jabatan <code>[jabatan]</code> <i class="ti ti-copy"></i></button>
                        </div>
                    </div>
                    <div class="col-md-4 border-top pt-3">
                        <h6 class="fw-bold border-bottom pb-2 mb-3 text-primary">KOMPONEN GAJI</h6>
                        <div class="list-group list-group-flush">
                            <button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" onclick="copyVar('[gaji]')">Gaji <code>[gaji]</code> <i class="ti ti-copy"></i></button>
                            <button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" onclick="copyVar('[tunjangan]')">Tunjangan <code>[tunjangan]</code> <i class="ti ti-copy"></i></button>
                            <button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" onclick="copyVar('[komisi]')">Komisi <code>[komisi]</code> <i class="ti ti-copy"></i></button>
                            <button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" onclick="copyVar('[potongan]')">Potongan <code>[potongan]</code> <i class="ti ti-copy"></i></button>
                        </div>
                    </div>

                    <div class="col-md-4 border-top pt-3">
                        <h6 class="fw-bold border-bottom pb-2 mb-3 text-primary">PENGATURAN GAJI</h6>
                        <div class="list-group list-group-flush">
                            <button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" onclick="copyVar('[gaji_pokok]')">Gaji Pokok <code>[gaji_pokok]</code> <i class="ti ti-copy"></i></button>
                            <button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" onclick="copyVar('[tunj_jabatan]')">Tunjangan Jabatan <code>[tunj_jabatan]</code> <i class="ti ti-copy"></i></button>
                            <button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" onclick="copyVar('[tunj_kehadiran]')">Tunjangan Kehadiran <code>[tunj_kehadiran]</code> <i class="ti ti-copy"></i></button>
                            <button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" onclick="copyVar('[tunj_komunikasi]')">Tunjangan Komunikasi <code>[tunj_komunikasi]</code> <i class="ti ti-copy"></i></button>
                            <button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" onclick="copyVar('[tunj_makan]')">Tunjangan Makan <code>[tunj_makan]</code> <i class="ti ti-copy"></i></button>
                            <button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" onclick="copyVar('[tunj_transport]')">Tunjangan Transport <code>[tunj_transport]</code> <i class="ti ti-copy"></i></button>
                            <button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" onclick="copyVar('[tunj_lembur_tetap]')">Tunjangan Lembur Tetap <code>[tunj_lembur_tetap]</code> <i class="ti ti-copy"></i></button>
                            <button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" onclick="copyVar('[tunj_other_non_fix]')">Tunjangan Other Non Fix <code>[tunj_other_non_fix]</code> <i class="ti ti-copy"></i></button>
                        </div>
                    </div>


                    <div class="col-md-12 border-top pt-3">
                        <h6 class="fw-bold border-bottom pb-2 mb-3 text-danger">VARIABEL KUSTOM ANDA</h6>
                        <div class="row" id="list-custom-vars">
                            {{-- Variabel kustom yang baru dibuat akan muncul di sini otomatis --}}
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light py-2">
                <div class="me-auto text-success fw-bold ps-2" id="copyStatus"></div>
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>