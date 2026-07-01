<div class="card border-0 shadow-sm">
    <div class="card-header d-flex align-items-center justify-content-between">
        <h6 class="mb-0">Variabel</h6>
        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#addVariable" title="Variabel Kustom">
            <i class="ti ti-variable"></i>
        </button>
    </div>
    <div class="card-body" style="max-height: 900px; overflow-y: auto;">
        <style>
            .var-btn { display:flex; justify-content:space-between; align-items:center; width:100%; text-align:left; background:#f8f9fa; border:1px solid #e9ecef; border-radius:4px; padding:5px 8px; margin-bottom:4px; font-size:11px; cursor:pointer; }
            .var-btn:hover { background:#e8f0fe; border-color:#0d6efd; }
            .var-btn code { font-size:10px; color:#0d6efd; }
            .var-group-title { font-size:12px; font-weight:700; text-transform:uppercase; color:#6c757d; margin:12px 0 6px; border-bottom:1px solid #eee; padding-bottom:4px; }
            .var-group-title:first-child { margin-top:0; }
        </style>

        <div class="var-group-title">Kop Surat</div>
        <button type="button" class="var-btn" onclick="insertVar('[no_surat]')">No Surat <code>[no_surat]</code></button>
        <button type="button" class="var-btn" onclick="insertVar('[romawi]')">Bulan Romawi <code>[romawi]</code></button>
        <button type="button" class="var-btn" onclick="insertVar('[tahun]')">Tahun <code>[tahun]</code></button>
        <button type="button" class="var-btn" onclick="insertVar('[tgl_surat]')">Tgl Terbit <code>[tgl_surat]</code></button>

        <div class="var-group-title">Isi Surat</div>
        <button type="button" class="var-btn" onclick="insertVar('[hari]')">Hari <code>[hari]</code></button>
        <button type="button" class="var-btn" onclick="insertVar('[mulai]')">Tgl Mulai <code>[mulai]</code></button>
        <button type="button" class="var-btn" onclick="insertVar('[selesai]')">Tgl Selesai <code>[selesai]</code></button>
        <button type="button" class="var-btn" onclick="insertVar('[sign_2]')">TTD Karyawan <code>[sign_2]</code></button>
        <button type="button" class="var-btn" onclick="insertVar('[esign]')">TTD HRD <code>[esign]</code></button>

        <div class="var-group-title">Pegawai</div>
        <button type="button" class="var-btn" onclick="insertVar('[no_karyawan]')">No Karyawan <code>[no_karyawan]</code></button>
        <button type="button" class="var-btn" onclick="insertVar('[nama_karyawan]')">Nama <code>[nama_karyawan]</code></button>
        <button type="button" class="var-btn" onclick="insertVar('[nik_ktp]')">NIK KTP <code>[nik_ktp]</code></button>
        <button type="button" class="var-btn" onclick="insertVar('[jenis_kelamin]')">Jenis Kelamin <code>[jenis_kelamin]</code></button>
        <button type="button" class="var-btn" onclick="insertVar('[ttl]')">TTL <code>[ttl]</code></button>
        <button type="button" class="var-btn" onclick="insertVar('[alamat]')">Alamat <code>[alamat]</code></button>
        <button type="button" class="var-btn" onclick="insertVar('[handphone]')">No HP <code>[handphone]</code></button>
        <button type="button" class="var-btn" onclick="insertVar('[jabatan]')">Jabatan <code>[jabatan]</code></button>

        <div class="var-group-title">Lokasi Project</div>
        <button type="button" class="var-btn" onclick="insertVar('[lokasi_project]')">Lokasi <code>[lokasi_project]</code></button>
        <button type="button" class="var-btn" onclick="insertVar('[area]')">Area <code>[area]</code></button>
        <button type="button" class="var-btn" onclick="insertVar('[nama_client]')">Nama Client <code>[nama_client]</code></button>
        <button type="button" class="var-btn" onclick="insertVar('[jabatan_client]')">Jabatan Client <code>[jabatan_client]</code></button>
        <button type="button" class="var-btn" onclick="insertVar('[area_description]')">Deskripsi <code>[area_description]</code></button>

        <div class="var-group-title">Gaji</div>
        <button type="button" class="var-btn" onclick="insertVar('[gaji]')">Gaji <code>[gaji]</code></button>
        <button type="button" class="var-btn" onclick="insertVar('[tunjangan]')">Tunjangan <code>[tunjangan]</code></button>
        <button type="button" class="var-btn" onclick="insertVar('[komisi]')">Komisi <code>[komisi]</code></button>
        <button type="button" class="var-btn" onclick="insertVar('[potongan]')">Potongan <code>[potongan]</code></button>

        <div class="var-group-title">Pengaturan Gaji</div>
        <button type="button" class="var-btn" onclick="insertVar('[gaji_pokok]')">Gaji Pokok <code>[gaji_pokok]</code></button>
        <button type="button" class="var-btn" onclick="insertVar('[tunj_jabatan]')">Tunj. Jabatan <code>[tunj_jabatan]</code></button>
        <button type="button" class="var-btn" onclick="insertVar('[tunj_kehadiran]')">Tunj. Kehadiran <code>[tunj_kehadiran]</code></button>
        <button type="button" class="var-btn" onclick="insertVar('[tunj_komunikasi]')">Tunj. Komunikasi <code>[tunj_komunikasi]</code></button>
        <button type="button" class="var-btn" onclick="insertVar('[tunj_makan]')">Tunj. Makan <code>[tunj_makan]</code></button>
        <button type="button" class="var-btn" onclick="insertVar('[tunj_transport]')">Tunj. Transport <code>[tunj_transport]</code></button>
        <button type="button" class="var-btn" onclick="insertVar('[tunj_lembur_tetap]')">Tunj. Lembur Tetap <code>[tunj_lembur_tetap]</code></button>
        <button type="button" class="var-btn" onclick="insertVar('[tunj_other_non_fix]')">Tunj. Other Non Fix <code>[tunj_other_non_fix]</code></button>

        <div class="var-group-title" id="customVarHeading" style="display:none">Kustom</div>
        <div id="customVarButtons"></div>
    </div>
</div>
