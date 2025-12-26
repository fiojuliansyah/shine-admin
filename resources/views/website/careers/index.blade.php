@extends('website.layouts.app')

@section('content')
<div class="page-wrapper">
    <div class="content">

        <!-- Breadcrumb -->
        <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
            <div class="my-auto mb-2">
                <h2 class="mb-1">Lowongan Pekerjaan</h2>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item">
                            <a href="index.html"><i class="ti ti-smart-home"></i></a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">Lowongan Pekerjaan</li>
                    </ol>
                </nav>
            </div>
        </div>
        <!-- /Breadcrumb -->

        <div class="card">
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <h5>List Pekerjaan</h5>
                    <form action="{{ route('web.applicants.career') }}" method="GET">
                        <div class="d-flex my-xl-auto right-content align-items-center flex-wrap row-gap-3">
                            <div class="me-3">
                                <div class="input-icon-end position-relative">
                                    <input type="date" name="date" class="form-control" value="{{ request('date') }}">
                                    <span class="input-icon-addon">
                                        <i class="ti ti-chevron-down"></i>
                                    </span>
                                </div>
                            </div>

                            <div class="dropdown me-3">
                                <input type="text" name="search" class="form-control" placeholder="Cari Lowongan" value="{{ request('search') }}">
                            </div>

                            <button type="submit" class="btn btn-primary me-2">
                                <i class="ti ti-filter me-1"></i> Filter
                            </button>

                            <a href="{{ route('web.applicants.career') }}" class="btn btn-outline-secondary">
                                <i class="ti ti-refresh me-1"></i> Reset
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="row">
            @forelse ($careers as $career)  
                <div class="col-xl-3 col-lg-4 col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="card bg-light">
                                <div class="card-body p-3">
                                    <div class="d-flex align-items-center">
                                        <a href="#" class="me-2">
                                            <span class="avatar avatar-lg bg-gray"><img src="/admin/assets/img/favicon.png" class="w-auto h-auto" alt="icon"></span>
                                        </a>
                                        <div>
                                            <h6 class="fw-medium mb-1 text-truncate"><a href="#">{{ $career->name }}</a></h6>
                                            <p class="fs-12 text-gray fw-normal">{{ $career->applicants_count }} Pelamar</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex flex-column mb-3">
                                <p class="text-dark d-inline-flex align-items-center mb-2">
                                    <i class="ti ti-map-pin-check text-gray-5 me-2"></i>
                                    {{ $career->location }}
                                </p>
                                <p class="text-dark d-inline-flex align-items-center mb-2">
                                    <i class="ti ti-wallet text-gray-5 me-2"></i>
                                    {{ number_format($career->salary, 0, '.', ',') }} / month
                                </p>
                                <p class="text-dark d-inline-flex align-items-center">
                                    <i class="ti ti-briefcase text-gray-5 me-2"></i>
                                    {{ $career->experience }}
                                </p>
                            
                            </div>
                            <div class="mb-3">
                                <span class="badge bg-danger-transparent me-2">{{ $career->candidate }} kandidat</span>
                                <span class="badge bg-secondary-transparent">{{ $career->graduate }}</span>
                            </div>
                            <div class="d-flex align-items-center justify-content-between border-top pt-3 mt-3">
                                <p class="d-inline-flex align-items-center text-gray-9 mb-0">
                                    <i class="ti ti-clock me-1"></i>{{ $career->created_at->diffForHumans() }}
                                </p>
                                <div>
                                    <a href="{{ route('web.applicants.career.detail', $career->slug) }}" class="btn btn-secondary">Apply</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
            <p class="text-center">Belum ada lowongan tersedia</p>
            @endforelse
        </div>
    </div>
</div>
@endsection