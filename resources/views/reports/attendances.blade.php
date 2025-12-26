@extends('layouts.main')

@section('content')
<div class="page-wrapper">
    <div class="content">
        <!-- start page title -->
        <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
            <div class="my-auto mb-2">
                <h2 class="mb-1">Reports</h2>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item">
                            <a href="index.html"><i class="ti ti-smart-home"></i></a>
                        </li>
                        <li class="breadcrumb-item">
                            Reports
                        </li>
                    </ol>
                </nav>
            </div>
        </div>
        <!-- end page title -->
        
            <div class="row">
                <div class="col-md-6 col-xxl-3 d-flex">
                    <div class="card w-100">
                        <div class="card-body">
                            <h4 class="card-title mb-4">Absensi Per Pegawai</h4>
                            <form action="{{ route('employee.export') }}" method="GET">
                                <div class="mb-3">
                                    <label class="form-label">Pegawai :</label>
                                    <select name="user_id" class="form-select">
                                        <option value="">Pilih Pegawai</option>
                                        @foreach ($employees as $employee)
                                            <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Project :</label>
                                    <select name="site_id" class="form-select">
                                        <option value="">Pilih Project</option>
                                        @foreach ($sites as $site)
                                            <option value="{{ $site->id }}">{{ $site->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">dari Tanggal :</label>
                                    <input type="date" name="start_date" class="form-control">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">sampai Tanggal :</label>
                                    <input type="date" name="end_date" class="form-control">
                                </div>
                                <button type="submit" class="btn btn-primary">Export</button>
                            </form>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 col-xxl-3 d-flex">
                    <div class="card w-100">
                        <div class="card-body">
                            <h4 class="card-title mb-4">Absensi Per Project</h4>
                            <form action="{{ route('export.excel') }}" method="GET">
                                <div class="mb-3">
                                    <label class="form-label">Project :</label>
                                    <select name="site_id" class="form-select">
                                        <option value="">Pilih Project</option>
                                        @foreach ($sites as $site)
                                            <option value="{{ $site->id }}">{{ $site->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">dari Tanggal :</label>
                                    <input type="date" name="start_date" class="form-control">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">sampai Tanggal :</label>
                                    <input type="date" name="end_date" class="form-control">
                                </div>
                                <button type="submit" class="btn btn-primary">Export</button>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-xxl-3 d-flex">
                    <div class="card w-100">
                        <div class="card-body">
                            <h4 class="card-title mb-4">Export Data Cuti</h4>
                            <form action="{{ route('leaves.export') }}" method="POST">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label">Project :</label>
                                    <select name="site_id" class="form-select">
                                        <option value="">Pilih Project</option>
                                        @foreach ($sites as $site)
                                            <option value="{{ $site->id }}">{{ $site->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">dari Tanggal :</label>
                                    <input type="date" name="start_date" class="form-control">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">sampai Tanggal :</label>
                                    <input type="date" name="end_date" class="form-control">
                                </div>
                                <button type="submit" class="btn btn-primary">Export</button>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-xxl-3 d-flex">
                    <div class="card w-100">
                        <div class="card-body">
                            <h4 class="card-title mb-4">Export Data Ijin</h4>
                            <form action="{{ route('permits.export') }}" method="POST">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label">Project :</label>
                                    <select name="site_id" class="form-select">
                                        <option value="">Pilih Project</option>
                                        @foreach ($sites as $site)
                                            <option value="{{ $site->id }}">{{ $site->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">dari Tanggal :</label>
                                    <input type="date" name="start_date" class="form-control">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">sampai Tanggal :</label>
                                    <input type="date" name="end_date" class="form-control">
                                </div>
                                <button type="submit" class="btn btn-primary">Export</button>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-xxl-3 d-flex">
                    <div class="card w-100">
                        <div class="card-body">
                            <h4 class="card-title mb-4">Export Data Lembur</h4>
                            <form action="{{ route('overtimes.export') }}" method="POST">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label">Project :</label>
                                    <select name="site_id" class="form-select">
                                        <option value="">Pilih Project</option>
                                        @foreach ($sites as $site)
                                            <option value="{{ $site->id }}">{{ $site->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">dari Tanggal :</label>
                                    <input type="date" name="start_date" class="form-control">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">sampai Tanggal :</label>
                                    <input type="date" name="end_date" class="form-control">
                                </div>
                                <button type="submit" class="btn btn-primary">Export</button>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-xxl-3 d-flex">
                    <div class="card w-100">
                        <div class="card-body">
                            <h4 class="card-title mb-4">Export Data User</h4>
                            <form action="{{ route('export.active-users') }}" method="GET" class="mb-2">
                                <div class="mb-2">
                                    <label class="form-label">Project :</label>
                                    <select name="site_id" class="form-select">
                                        <option value="">Semua Project</option>
                                        @foreach ($sites as $site)
                                            <option value="{{ $site->id }}">{{ $site->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary w-100">Export Active Users</button>
                            </form>
                            
                            <form action="{{ route('export.inactive-users') }}" method="GET">
                                <div class="mb-2">
                                    <label class="form-label">Project :</label>
                                    <select name="site_id" class="form-select">
                                        <option value="">Semua Project</option>
                                        @foreach ($sites as $site)
                                            <option value="{{ $site->id }}">{{ $site->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary w-100">Export Inactive Users</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
    </div>
</div>
@endsection
