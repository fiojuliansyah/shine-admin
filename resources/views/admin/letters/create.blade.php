@extends('admin.layouts.main')

@section('content')
<div class="page-wrapper">
    <div class="content">

        <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-4">
            <div class="my-auto mb-2">
                <h2 class="mb-1">E-Letter Builder</h2>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('letters.index') }}"><i class="ti ti-smart-home"></i></a></li>
                        <li class="breadcrumb-item">E-Recruitment</li>
                        <li class="breadcrumb-item active" aria-current="page">Create Template</li>
                    </ol>
                </nav>
            </div>
            <div class="d-flex my-xl-auto right-content align-items-center flex-wrap">
                <div class="mb-2 me-2">
                    <button type="button" class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#addVariable">
                        <i class="ti ti-plus me-2"></i> Add Custom Variable
                    </button>
                </div>
                <div class="mb-2">
                    <button type="button" class="btn btn-outline-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#lihatVariable">
                        <i class="ti ti-copy me-2"></i> List Variable & Copy
                    </button>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h5 class="card-title mb-0 text-primary">Buat Template E-Letter</h5>
            </div>
            <div class="card-body">
                <form id="letterForm" action="{{ route('letters.store') }}" method="POST">
                    @csrf
                    
                    {{-- Container untuk input hidden variabel kustom --}}
                    <div id="customVarsContainer"></div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Site</label>
                            <select class="select2 form-select" name="site_id" required>
                                <option value="" disabled selected>Pilih Site</option>
                                @foreach ($sites as $site)
                                    <option value="{{ $site->id }}">{{ $site->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Nama Template</label>
                            <input type="text" class="form-control" name="title" required placeholder="Nama template...">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Tipe Template</label>
                            <select class="form-select" name="type_letter_id" required>
                                <option value="" disabled selected>Pilih Tipe</option>
                                @foreach ($types as $type)
                                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <textarea class="form-control" id="description" name="description"></textarea>
                    </div>
                    <div class="d-flex justify-content-end gap-2 pt-3">
                        <a href="{{ route('letters.index') }}" class="btn btn-light px-4">Batal</a>
                        <button type="submit" class="btn btn-primary px-5">Simpan Template</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- MODAL: ADD CUSTOM VARIABLE --}}
<div class="modal fade" id="addVariable" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary">
                <h5 class="modal-title text-white">Tambah Variabel Kustom</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
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
                    <h6 class="fw-bold border-bottom pb-2">Variabel Yang Akan Ditambahkan:</h6>
                    <ul class="list-group list-group-flush" id="tempVarList">
                        {{-- List variabel baru muncul di sini --}}
                    </ul>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary" onclick="addNewVariable()">Tambah Ke Daftar</button>
            </div>
        </div>
    </div>
</div>

{{-- MODAL: LIST VARIABLE --}}
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
                            <button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" onclick="copyVar('[ttl]')">TTL <code>[ttl]</code> <i class="ti ti-copy"></i></button>
                            <button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" onclick="copyVar('[alamat]')">Alamat <code>[alamat]</code> <i class="ti ti-copy"></i></button>
                            <button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" onclick="copyVar('[handphone]')">No HP <code>[handphone]</code> <i class="ti ti-copy"></i></button>
                        </div>
                    </div>
                    <div class="col-md-4 border-top pt-3">
                        <h6 class="fw-bold border-bottom pb-2 mb-3 text-primary">INFORMASI LOKASI PROJECT</h6>
                        <div class="list-group list-group-flush">
                            <button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" onclick="copyVar('[area]')">Area <code>[area]</code> <i class="ti ti-copy"></i></button>
                            <button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" onclick="copyVar('[area_client]')">Nama Client <code>[nama_client]</code> <i class="ti ti-copy"></i></button>
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
@endsection

@push('js')
<script src="/admin/assets/libs/tinymce/tinymce.min.js"></script>
<script>
    let varCount = 0;

    tinymce.init({
        selector: "#description",
        plugins: "anchor autolink autosave charmap codesample directionality emoticons help image insertdatetime link lists media nonbreaking pagebreak searchreplace table visualblocks visualchars wordcount",
        toolbar: "undo redo | blocks fontfamily fontsizeinput | bold italic underline forecolor backcolor | link image | align lineheight bullist numlist | indent outdent | removeformat nonbreaking",
        height: '750px',
        content_style: `
            body { background: #fff; font-family: 'Helvetica', sans-serif; font-size: 14px; }
            @media (min-width: 840px) {
                html { background: #eceef4; padding: 0.5rem; }
                body {
                    background-color: #fff;
                    box-shadow: 0 0 4px rgba(0, 0, 0, .15);
                    margin: 1rem auto;
                    max-width: 820px;
                    min-height: calc(100vh - 2rem);
                    padding: 2rem 5rem;
                }
            }
        `,
        setup: function (editor) {
            editor.on('change', function () { editor.save(); });
        }
    });

    function copyVar(val) {
        navigator.clipboard.writeText(val).then(() => {
            const status = document.getElementById('copyStatus');
            status.innerText = 'Copied: ' + val;
            setTimeout(() => { status.innerText = ''; }, 2000);
        });
    }

    function addNewVariable() {
        const name = document.getElementById('var_name').value;
        const codeInput = document.getElementById('var_code').value;
        const code = codeInput.toLowerCase().replace(/[^a-z0-9_]/g, '');

        if (name === '' || code === '') {
            alert('Nama dan Kode variabel harus diisi!');
            return;
        }

        const fullCode = `[${code}]`;
        
        // 1. Tambahkan ke list visual di modal add
        const list = document.getElementById('tempVarList');
        const li = document.createElement('li');
        li.className = 'list-group-item d-flex justify-content-between align-items-center px-0';
        li.innerHTML = `<div><span class="fw-medium">${name}</span> <code class="ms-1">${fullCode}</code></div>
                        <button type="button" class="btn btn-sm btn-outline-danger border-0" onclick="removeTempVar(this, ${varCount})"><i class="ti ti-trash"></i></button>`;
        list.appendChild(li);

        // 2. Tambahkan ke list copy di modal list
        const copyList = document.getElementById('list-custom-vars');
        const col = document.createElement('div');
        col.className = 'col-md-4';
        col.id = `copy-col-${varCount}`;
        col.innerHTML = `
            <button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" onclick="copyVar('${fullCode}')">
                ${name} <code>${fullCode}</code> <i class="ti ti-copy"></i>
            </button>
        `;
        copyList.appendChild(col);

        // 3. Tambahkan input hidden ke form utama
        const container = document.getElementById('customVarsContainer');
        const inputDiv = document.createElement('div');
        inputDiv.id = `input-group-${varCount}`;
        inputDiv.innerHTML = `
            <input type="hidden" name="custom_vars[${varCount}][name]" value="${name}">
            <input type="hidden" name="custom_vars[${varCount}][variable]" value="${code}">
        `;
        container.appendChild(inputDiv);

        // Reset & Increment
        document.getElementById('var_name').value = '';
        document.getElementById('var_code').value = '';
        varCount++;
    }

    function removeTempVar(btn, id) {
        btn.closest('li').remove();
        document.getElementById(`input-group-${id}`).remove();
        document.getElementById(`copy-col-${id}`).remove();
    }

    document.getElementById('letterForm').addEventListener('submit', function(event) {
        var description = tinymce.get('description').getContent();
        if (description.trim() === '') {
            alert('Konten surat tidak boleh kosong.');
            event.preventDefault();
        }
    });
</script>
@endpush