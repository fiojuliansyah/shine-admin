@extends('admin.layouts.main')

@section('content')
<div class="page-wrapper">
    <div class="content">
        <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
            <div class="my-auto mb-2">
                <h2 class="mb-1">Hasil Import Data Pegawai</h2>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="ti ti-smart-home"></i></a></li>
                        <li class="breadcrumb-item"><a href="{{ route('employees.import.form') }}">Import Data Pegawai</a></li>
                        <li class="breadcrumb-item active">Hasil</li>
                    </ol>
                </nav>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('employees.import.result.pdf') }}" class="btn btn-outline-danger">
                    <i class="ti ti-file-type-pdf me-1"></i>Download PDF
                </a>
                <a href="{{ route('employees.import.form') }}" class="btn btn-primary">
                    <i class="ti ti-arrow-back-up me-1"></i>Coba Import Lagi
                </a>
            </div>
        </div>

        <div class="alert alert-danger d-flex align-items-start">
            <i class="ti ti-alert-triangle fs-4 me-2"></i>
            <div>
                <strong>Import dibatalkan.</strong> Tidak ada data yang disimpan karena terdapat
                <strong>{{ count($rowErrors) }} kesalahan</strong> dari <strong>{{ $totalRows }} baris</strong>.
                Perbaiki file Excel Anda, lalu upload ulang.
                @isset($fileName)
                    <div class="small mt-1 text-muted">File: <strong>{{ $fileName }}</strong> &middot; {{ $at ?? '' }}</div>
                @endisset
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Daftar Data yang Bermasalah</h5>
                <span class="badge bg-danger">{{ count($rowErrors) }} error</span>
            </div>
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width:70px">Baris</th>
                            <th>Nama / Referensi</th>
                            <th>Kolom</th>
                            <th>Kesalahan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($rowErrors as $error)
                            <tr>
                                <td class="fw-bold">{{ $error['row'] }}</td>
                                <td>{{ $error['employee'] }}</td>
                                <td><span class="badge bg-warning-subtle text-warning">{{ $error['field'] }}</span></td>
                                <td>{{ $error['message'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
