@extends('admin.layouts.main')

@section('content')
<div class="page-wrapper">
    <div class="content">
        <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
            <div class="my-auto mb-2">
                <h2 class="mb-1">Import Data Pegawai</h2>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="ti ti-smart-home"></i></a></li>
                        <li class="breadcrumb-item">Data Pegawai</li>
                        <li class="breadcrumb-item active">Import</li>
                    </ol>
                </nav>
            </div>
            <a href="{{ route('employees.import.template') }}" class="btn btn-outline-primary">
                <i class="ti ti-file-download me-1"></i>Download Template
            </a>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
        @endif

        <div class="row">
            <div class="col-xl-7">
                <div class="card border-0 shadow-sm">
                    <div class="card-header"><h5 class="mb-0">Upload File Pegawai</h5></div>
                    <form action="{{ route('employees.import') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="card-body">
                            <label for="file" class="form-label fw-bold">File Excel/CSV</label>
                            <input type="file" id="file" name="file" class="form-control" accept=".xlsx,.xls,.csv" required>
                            <div class="form-text">Maksimal 10 MB. Gunakan template agar susunan kolom sesuai.</div>
                        </div>
                        <div class="card-footer text-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="ti ti-file-upload me-1"></i>Import Pegawai
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="col-xl-5">
                <div class="card border-0 shadow-sm">
                    <div class="card-header"><h5 class="mb-0">Ketentuan Import</h5></div>
                    <div class="card-body">
                        <ul class="mb-0 ps-3">
                            <li class="mb-2">Wajib: PT, NIK Karyawan, Nama, Email, Jabatan, dan Project.</li>
                            <li class="mb-2">PT menggunakan kode perusahaan dan Jabatan menggunakan kode jabatan.</li>
                            <li class="mb-2">Project harus sesuai nama site pada perusahaan.</li>
                            <li class="mb-2">Status kosong dianggap <strong>ACTIVE</strong>; isi <strong>RESIGN</strong> untuk pegawai resign.</li>
                            <li class="mb-2">Tanggal menggunakan format <strong>dd-mm-yyyy</strong>.</li>
                            <li>Manager dapat diisi NIK Karyawan, email, atau nama pegawai.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
