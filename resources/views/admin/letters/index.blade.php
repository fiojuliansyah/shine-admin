@extends('admin.layouts.main')

@section('content')
<div class="page-wrapper">
    <div class="content">

        <!-- Breadcrumb -->
        <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
            <div class="my-auto mb-2">
                <h2 class="mb-1">List Template</h2>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item">
                            <a href="index.html"><i class="ti ti-smart-home"></i></a>
                        </li>
                        <li class="breadcrumb-item">E-Recruitment</li>
                        <li class="breadcrumb-item active" aria-current="page">Template</li>
                    </ol>
                </nav>
            </div>
            <div class="d-flex my-xl-auto right-content align-items-center flex-wrap">
                <div class="d-flex align-items-center flex-wrap mb-1">
                    <button type="button" class="btn btn-outline-primary mb-2 me-2" data-bs-toggle="modal" data-bs-target="#importDocxModal"><i class="ti ti-file-import me-1"></i>Import DOCX</button>
                    <a href="{{ route('letters.create') }}" target="_blank" class="btn btn-primary mb-2"><i class="ti ti-square-rounded-plus me-1"></i>Buat Template</a>
                </div>
            </div>
        </div>
        <!-- /Breadcrumb -->

        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
                <h5>List E-Letter</h5>
            </div>
            <div class="card-body p-0">
                <div class="custom-datatable-filter table-responsive">
                    {{ $dataTable->table() }}
                </div>
            </div>
        </div>
    </div>
</div>

{{-- MODAL: IMPORT DOCX --}}
<div class="modal fade" id="importDocxModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('letters.import') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-primary">
                    <h5 class="modal-title text-white"><i class="ti ti-file-import me-1"></i>Import Template dari DOCX</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-info py-2 small">
                        File <strong>.docx</strong> akan dikonversi menjadi template editor. Setelah impor, Anda akan diarahkan ke editor untuk merapikan tata letak lalu menyimpannya.
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Site</label>
                        <select name="site_id" class="form-select" required>
                            <option value="" disabled selected>Pilih Site</option>
                            @foreach ($sites as $site)
                                <option value="{{ $site->id }}">{{ $site->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nama Template</label>
                        <input type="text" name="title" class="form-control" required placeholder="Nama template...">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Tipe Template</label>
                        <select name="type_letter_id" class="form-select" required>
                            <option value="" disabled selected>Pilih Tipe</option>
                            @foreach ($types as $type)
                                <option value="{{ $type->id }}">{{ $type->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">File DOCX</label>
                        <input type="file" name="docx" class="form-control" accept=".docx" required>
                        <small class="text-muted">Maksimal 10 MB. Format yang didukung: .docx</small>
                    </div>
                    <div class="form-check">
                        <input type="hidden" name="require_hrd_signature" value="0">
                        <input class="form-check-input" type="checkbox" name="require_hrd_signature" value="1" id="impHrd" checked>
                        <label class="form-check-label" for="impHrd">Perlu Tanda Tangan HRD</label>
                    </div>
                    <div class="form-check">
                        <input type="hidden" name="require_employee_signature" value="0">
                        <input class="form-check-input" type="checkbox" name="require_employee_signature" value="1" id="impEmp" checked>
                        <label class="form-check-label" for="impEmp">Perlu Tanda Tangan Employee</label>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="ti ti-upload me-1"></i>Import &amp; Buka Editor</button>
                </div>
            </div>
        </form>
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
@endpush
