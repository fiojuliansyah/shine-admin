@extends('admin.layouts.main')

@section('content')
<div class="page-wrapper">
    <div class="content">

        <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
            <div class="my-auto mb-2">
                <h2 class="mb-1">Sites</h2>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item">
                            <a href="index.html"><i class="ti ti-smart-home"></i></a>
                        </li>
                        <li class="breadcrumb-item">CRM</li>
                        <li class="breadcrumb-item active" aria-current="page">List Sites</li>
                    </ol>
                </nav>
            </div>
            <div class="d-flex my-xl-auto right-content align-items-center flex-wrap ">
                <div class="me-2 mb-2">
                    <a href="{{ route('sites.export') }}" class="btn btn-white d-inline-flex align-items-center">
                        <i class="ti ti-file-export me-1"></i>Export Excel
                    </a>
                </div>
                <div class="me-2 mb-2">
                    <a href="#" data-bs-toggle="modal" data-bs-target="#importModal" class="btn btn-white d-inline-flex align-items-center">
                        <i class="ti ti-file-import me-1"></i>Import
                    </a>
                </div>
                <div class="mb-2">
                    <a href="#" data-bs-toggle="modal" data-bs-target="#createModal" class="btn btn-primary d-flex align-items-center">
                        <i class="ti ti-circle-plus me-2"></i>Tambah Site
                    </a>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
                <h5>List Sites</h5>
                <div class="d-flex align-items-center">
                    <label class="me-2 mb-0">Company:</label>
                    <select id="filter_company" class="form-select form-select-sm" style="width: 200px;">
                        <option value="">All Companies</option>
                        @foreach ($companies as $company)
                            <option value="{{ $company->id }}">{{ $company->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="custom-datatable-filter table-responsive">
                    {{ $dataTable->table() }}
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="createModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Tambah Site</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('sites.store') }}" method="POST">
                    @csrf
                    <div class="modal-body pb-0">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Company</label>
                                    <select class="form-select" name="company_id">
                                        @foreach ($companies as $company)
                                            <option value="{{ $company->id }}" {{ $company->is_default == 1 ? 'selected' : '' }}>
                                                {{ $company->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Nama Lokasi Project</label>
                                    <input type="text" class="form-control" name="name" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label class="form-label">Area</label>
                                    <input type="text" class="form-control" name="area" placeholder="Contoh: Jakarta">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label class="form-label">Deskripsi Project</label>
                                    <input type="text" class="form-control" name="description">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Nama Client</label>
                                    <input type="text" class="form-control" name="client_name">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Jabatan Client</label>
                                    <input type="text" class="form-control" name="client_position">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="importModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Import Site</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('sites.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body pb-0">
                        <div class="mb-3">
                            <label class="form-label">Perusahaan</label>
                            <select class="form-select" name="company_id">
                                @foreach ($companies as $company)
                                    <option value="{{ $company->id }}">
                                        {{ $company->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">File Import</label>
                            <input type="file" class="form-control" name="file">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
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

<script>
    $(document).ready(function() {
        $('#filter_company').on('change', function() {
            window.LaravelDataTables["sites-table"].draw();
        });
    });
</script>
@endpush