@extends('layouts.main')

@section('content')
<div class="page-wrapper">
    <div class="content">

        <!-- Breadcrumb -->
        <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
            <div class="my-auto mb-2">
                <h2 class="mb-1">Lowongan</h2>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item">
                            <a href="index.html"><i class="ti ti-smart-home"></i></a>
                        </li>
                        <li class="breadcrumb-item">HRM</li>
                        <li class="breadcrumb-item active" aria-current="page">List Lowongan</li>
                    </ol>
                </nav>
            </div>
            <div class="d-flex my-xl-auto right-content align-items-center flex-wrap">
                <div class="me-2 mb-2">
                    <div class="dropdown">
                        <a href="javascript:void(0);" class="dropdown-toggle btn btn-white d-inline-flex align-items-center" data-bs-toggle="dropdown">
                            <i class="ti ti-file-export me-1"></i>Export
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end p-3">
                            <li><a href="javascript:void(0);" class="dropdown-item rounded-1"><i class="ti ti-file-type-pdf me-1"></i>Export as PDF</a></li>
                            <li><a href="javascript:void(0);" class="dropdown-item rounded-1"><i class="ti ti-file-type-xls me-1"></i>Export as Excel</a></li>
                        </ul>
                    </div>
                </div>
                <div class="mb-2">
                    <a href="#" data-bs-toggle="modal" data-bs-target="#addCareer" class="btn btn-primary d-flex align-items-center">
                        <i class="ti ti-circle-plus me-2"></i>Tambah Lowongan
                    </a>
                </div>
            </div>
        </div>
        <!-- /Breadcrumb -->

        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
                <h5>List Lowongan</h5>
            </div>
            <div class="card-body p-0">
                <div class="custom-datatable-filter table-responsive">
                    {{ $dataTable->table() }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create Modal -->
<div class="modal fade" id="addCareer" tabindex="-1" aria-labelledby="addCareerLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Tambah Lowongan</h4>
                <button type="button" class="btn-close custom-btn-close" data-bs-dismiss="modal" aria-label="Close">
                    <i class="ti ti-x"></i>
                </button>
            </div>
            <form action="{{ route('careers.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Perusahaan</label>
                            <select class="form-select" name="company_id" required>
                                @foreach ($companies as $company)
                                    <option value="{{ $company->id }}">{{ $company->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nama Lowongan</label>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Deskripsi</label>
                            <textarea class="form-control" id="description" name="description" rows="5" required></textarea>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Department</label>
                            <input type="text" class="form-control" name="department">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Lokasi</label>
                            <input type="text" class="form-control" name="location">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Fungsi Pekerjaan</label>
                            <input type="text" class="form-control" name="workfunction">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Pengalaman</label>
                            <input type="text" class="form-control" name="experience">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Lulusan</label>
                            <input type="text" class="form-control" name="graduate">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Jurusan</label>
                            <input type="text" class="form-control" name="major">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Gaji</label>
                            <input type="text" class="form-control" name="salary">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Jumlah Pelamar</label>
                            <input type="text" class="form-control" name="candidate">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Sampai Tanggal</label>
                            <input type="date" class="form-control" name="until_date">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light me-2" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('css')
<link rel="stylesheet" href="/admin/assets/css/dataTables.bootstrap5.min.css">
@endpush

@push('js')
<script src="/admin/assets/js/jquery.dataTables.min.js"></script>
<script src="/admin/assets/js/dataTables.bootstrap5.min.js"></script>
{{ $dataTable->scripts(attributes: ['type' => 'module']) }}
<script src="/admin/assets/libs/tinymce/tinymce.min.js"></script>
<script>
    tinymce.init({
        selector: "#description",
        plugins: "anchor autolink autosave charmap codesample directionality emoticons help image insertdatetime link lists media nonbreaking pagebreak searchreplace table visualblocks visualchars wordcount",
        toolbar: "undo redo spellcheckdialog | aidialog aishortcuts | blocks fontfamily fontsizeinput | bold italic underline forecolor backcolor | link image addcomment showcomments  | align lineheight checklist bullist numlist | indent outdent | inserttemplate | removeformat typography math",
        editable_root: false,
        height: '700px',
        toolbar_sticky: true,
        autosave_restore_when_empty: true,
        content_style: `
            body {
            background: #fff;
            }
            .editable-section:focus-visible {
            outline: none !important;
            }
            .header,
            .footer {
            font-size: 0.8rem;
            color: #ddd;
            }
            .header {
            display: flex;
            justify-content: space-between;
            padding: 0 0 1rem 0;
            }
            .header .right-text {
            text-align: right;
            }
            .footer {
            padding: 2rem 0 0 0;
            text-align: center;
            }
            @media (min-width: 840px) {
            html {
                background: #eceef4;
                min-height: 100%;
                padding: 0.5rem;
            }
            body {
                background-color: #fff;
                box-shadow: 0 0 4px rgba(0, 0, 0, .15);
                box-sizing: border-box;
                margin: 1rem auto 0;
                max-width: 820px;
                min-height: calc(100vh - 1rem);
                padding: 2rem 6rem 2rem 6rem;
            }
            }
            @media print {
            .pagebreak {
                page-break-before: always;
            }
            }
        `,
        setup: function (editor) {
            editor.on('change', function () {
                editor.save();
            });
        }
    });

    document.getElementById('letterForm').addEventListener('submit', function(event) {
        var description = tinymce.get('description').getContent();
        if (description.trim() === '') {
            alert('Deskripsi tidak boleh kosong.');
            event.preventDefault();
        }
    });
</script>

<script>
    // Fungsi untuk inisialisasi TinyMCE di modal tertentu
    function initializeTinyMCE(modalId) {
        tinymce.init({
            selector: `#description-${modalId}`, // Selector untuk textarea di modal dengan ID dinamis
            plugins: "anchor autolink autosave charmap codesample directionality emoticons help image insertdatetime link lists media nonbreaking pagebreak searchreplace table visualblocks visualchars wordcount",
            toolbar: "undo redo spellcheckdialog | aidialog aishortcuts | blocks fontfamily fontsizeinput | bold italic underline forecolor backcolor | link image addcomment showcomments  | align lineheight checklist bullist numlist | indent outdent | inserttemplate | removeformat typography math",
            editable_root: false,
            height: '700px',
            toolbar_sticky: true,
            autosave_restore_when_empty: true,
            content_style: `
                body {
                background: #fff;
                }
                .editable-section:focus-visible {
                outline: none !important;
                }
                .header,
                .footer {
                font-size: 0.8rem;
                color: #ddd;
                }
                .header {
                display: flex;
                justify-content: space-between;
                padding: 0 0 1rem 0;
                }
                .header .right-text {
                text-align: right;
                }
                .footer {
                padding: 2rem 0 0 0;
                text-align: center;
                }
            `,
            setup: function (editor) {
                editor.on('change', function () {
                    editor.save();
                });
            }
        });
    }

    $(document).on('shown.bs.modal', function (e) {
        var modalId = $(e.target).attr('id').split('-')[1];
        initializeTinyMCE(modalId);
    });

    $(document).on('hidden.bs.modal', function (e) {
        var modalId = $(e.target).attr('id').split('-')[1]; 
        tinymce.remove(`#description-${modalId}`);
    });

    document.getElementById('letterForm').addEventListener('submit', function(event) {
        var description = tinymce.get('description').getContent();
        if (description.trim() === '') {
            alert('Deskripsi tidak boleh kosong.');
            event.preventDefault();
        }
    });
</script>

@endpush
