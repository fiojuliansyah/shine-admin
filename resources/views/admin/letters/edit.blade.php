@extends('admin.layouts.main')

@section('content')
<div class="page-wrapper">
    <div class="content">

        <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-4">
            <div class="my-auto mb-2">
                <h2 class="mb-1">Edit E-Letter Builder</h2>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('letters.index') }}"><i class="ti ti-smart-home"></i></a></li>
                        <li class="breadcrumb-item">E-Recruitment</li>
                        <li class="breadcrumb-item active" aria-current="page">Edit Template</li>
                    </ol>
                </nav>
            </div>
            <div class="d-flex my-xl-auto right-content align-items-center flex-wrap">
                <div class="mb-2">
                    <button type="button" class="btn btn-outline-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#lihatVariable">
                        <i class="ti ti-copy me-2"></i> List Variable & Copy
                    </button>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h5 class="card-title mb-0 text-primary">Update Template E-Letter</h5>
            </div>
            <div class="card-body">
                <form id="letterForm" action="{{ route('letters.update', $letter->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Site</label>
                            <select class="select2 form-select" name="site_id" required>
                                @foreach ($sites as $site)
                                    <option value="{{ $site->id }}" {{ $letter->site_id == $site->id ? 'selected' : '' }}>
                                        {{ $site->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Nama Template</label>
                            <input type="text" class="form-control" name="title" value="{{ $letter->title }}" required placeholder="Nama template...">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Tipe Template</label>
                            <select class="form-select" name="type_letter_id" required>
                                @foreach ($types as $type)
                                    <option value="{{ $type->id }}" {{ $letter->type_letter_id == $type->id ? 'selected' : '' }}>
                                        {{ $type->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <textarea class="form-control" id="description" name="description">{!! old('description', $letter->description) !!}</textarea>
                    </div>
                    <div class="d-flex justify-content-end gap-2 pt-3">
                        <a href="{{ route('letters.index') }}" class="btn btn-light px-4">Batal</a>
                        <button type="submit" class="btn btn-primary px-5">Update Template</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

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
                            <button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" onclick="copyVar('[mulai]')">Tgl Mulai <code>[mulai]</code> <i class="ti ti-copy"></i></button>
                            <button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" onclick="copyVar('[selesai]')">Tgl Selesai <code>[selesai]</code> <i class="ti ti-copy"></i></button>
                            <button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" onclick="copyVar('[pihak_2]')">Pihak 2 <code>[pihak_2]</code> <i class="ti ti-copy"></i></button>
                            <button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" onclick="copyVar('[sign_2]')">Tanda Tangan P2 <code>[sign_2]</code> <i class="ti ti-copy"></i></button>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <h6 class="fw-bold border-bottom pb-2 mb-3 text-primary">INFORMASI PERSONAL</h6>
                        <div class="list-group list-group-flush">
                            <button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" onclick="copyVar('[nama_karyawan]')">Nama Karyawan <code>[nama_karyawan]</code> <i class="ti ti-copy"></i></button>
                            <button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" onclick="copyVar('[ttl]')">TTL <code>[ttl]</code> <i class="ti ti-copy"></i></button>
                            <button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" onclick="copyVar('[alamat]')">Alamat <code>[alamat]</code> <i class="ti ti-copy"></i></button>
                            <button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" onclick="copyVar('[handphone]')">No HP <code>[handphone]</code> <i class="ti ti-copy"></i></button>
                        </div>
                    </div>

                    <div class="col-md-4 border-top pt-3">
                        <h6 class="fw-bold border-bottom pb-2 mb-3 text-primary">INFORMASI PEGAWAI</h6>
                        <div class="list-group list-group-flush">
                            <button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" onclick="copyVar('[no_karyawan]')">No Karyawan <code>[no_karyawan]</code> <i class="ti ti-copy"></i></button>
                            <button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" onclick="copyVar('[area]')">Area <code>[area]</code> <i class="ti ti-copy"></i></button>
                            <button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" onclick="copyVar('[jabatan]')">Jabatan <code>[jabatan]</code> <i class="ti ti-copy"></i></button>
                            <button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" onclick="copyVar('[esign]')">Tanda Tangan HRD <code>[esign]</code> <i class="ti ti-copy"></i></button>
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
                        <h6 class="fw-bold border-bottom pb-2 mb-3 text-primary">KONTAK DARURAT</h6>
                        <div class="list-group list-group-flush">
                            <button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" onclick="copyVar('[nama_kontak]')">Nama <code>[nama_kontak]</code> <i class="ti ti-copy"></i></button>
                            <button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" onclick="copyVar('[no_kontak]')">Nomor HP <code>[no_kontak]</code> <i class="ti ti-copy"></i></button>
                            <button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" onclick="copyVar('[alamat_kontak]')">Alamat <code>[alamat_kontak]</code> <i class="ti ti-copy"></i></button>
                            <button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" onclick="copyVar('[hubungan]')">Hubungan <code>[hubungan]</code> <i class="ti ti-copy"></i></button>
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
    tinymce.init({
        selector: "#description",
        plugins: "anchor autolink autosave charmap codesample directionality emoticons help image insertdatetime link lists media nonbreaking pagebreak searchreplace table visualblocks visualchars wordcount",
        toolbar: "undo redo | blocks fontfamily fontsizeinput | bold italic underline forecolor backcolor | link image | align lineheight bullist numlist | indent outdent | removeformat nonbreaking",
        height: '750px',
        content_style: `
            body { 
                background: #fff; 
                font-family: 'Helvetica', sans-serif; 
                font-size: 14px; 
                position: relative; 
                z-index: 1; 
            }
            img { 
                position: absolute; 
                z-index: -1; 
                pointer-events: auto;
                max-width: 100%;
                height: auto;
                opacity: 0.7;
            }
            p, h1, h2, h3, h4, h5, h6, ul, ol, table {
                position: relative;
                z-index: 2;
                pointer-events: none;
                background: transparent !important;
            }
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

    document.getElementById('letterForm').addEventListener('submit', function(event) {
        var description = tinymce.get('description').getContent();
        if (description.trim() === '') {
            alert('Konten surat tidak boleh kosong.');
            event.preventDefault();
        }
    });
</script>
@endpush