@extends('website.layouts.app')

@section('content')
    <div class="page-wrapper">
        <div class="content">

            <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-4">
                <div class="my-auto mb-2">
                    <h2 class="mb-1">Detail Lowongan</h2>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ url('/') }}"><i class="ti ti-smart-home"></i></a></li>
                            <li class="breadcrumb-item"><a href="#">Lowongan Kerja</a></li>
                            <li class="breadcrumb-item active" aria-current="page">{{ $career->name }}</li>
                        </ol>
                    </nav>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <span class="badge bg-soft-success text-success mb-2 px-3 py-2">Open Recruitment</span>
                                    <h3 class="fw-bold mb-1">{{ $career->name }}</h3>
                                    <p class="text-muted mb-0"><i class="ti ti-map-pin me-1"></i> {{ $career->location }}
                                    </p>
                                </div>
                                <div class="text-end d-none d-md-block">
                                    <h4 class="text-primary fw-bold mb-0">Rp
                                        {{ number_format($career->salary, 0, '.', ',') }}</h4>
                                    <small class="text-muted">Per Bulan</small>
                                </div>
                            </div>

                            <div class="row g-3 pt-3 border-top">
                                <div class="col-6 col-md-3">
                                    <small class="text-muted d-block">Pengalaman</small>
                                    <span class="fw-semibold">{{ $career->experience }}</span>
                                </div>
                                <div class="col-6 col-md-3">
                                    <small class="text-muted d-block">Pendidikan</small>
                                    <span class="fw-semibold">{{ $career->graduate }}</span>
                                </div>
                                <div class="col-6 col-md-3">
                                    <small class="text-muted d-block">Batas Akhir</small>
                                    <span
                                        class="fw-semibold">{{ \Carbon\Carbon::parse($career->until_date)->format('d M Y') }}</span>
                                </div>
                                <div class="col-6 col-md-3">
                                    <small class="text-muted d-block">Kebutuhan</small>
                                    <span class="fw-semibold">{{ $career->candidate }} Orang</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-body p-4">
                            <h5 class="fw-bold mb-3 border-bottom pb-2">Deskripsi Pekerjaan</h5>
                            <div class="job-description">
                                {!! $career->description !!}
                            </div>

                            <h5 class="fw-bold mt-4 mb-3 border-bottom pb-2">Kualifikasi & Detail</h5>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="d-flex align-items-center">
                                        <div class="bg-light p-2 rounded me-3">
                                            <i class="ti ti-school fs-4 text-primary"></i>
                                        </div>
                                        <div>
                                            <small class="text-muted d-block">Jurusan</small>
                                            <span class="fw-medium">{{ $career->major }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="d-flex align-items-center">
                                        <div class="bg-light p-2 rounded me-3">
                                            <i class="ti ti-briefcase fs-4 text-primary"></i>
                                        </div>
                                        <div>
                                            <small class="text-muted d-block">Fungsi Pekerjaan</small>
                                            <span class="fw-medium">{{ $career->workfunction }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm sticky-top" style="top: 20px; z-index: 10;">
                        <div class="card-body p-4">
                            <h5 class="fw-bold mb-3 text-center">Tertarik dengan posisi ini?</h5>
                            <p class="text-muted text-center small mb-4">Pastikan profil Anda sudah lengkap sebelum melamar
                                untuk meningkatkan peluang diterima.</p>

                            <form action="{{ route('web.applicants.career.apply', $career->slug) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-primary btn-lg w-100 py-3 shadow-sm mb-3 fw-bold">
                                    <i class="ti ti-send me-2"></i> Lamar Sekarang
                                </button>
                            </form>

                            <div class="alert alert-warning d-flex align-items-center mb-0" role="alert">
                                <i class="ti ti-info-circle me-2 fs-4"></i>
                                <small>Batas waktu lamaran berakhir dalam
                                    {{ \Carbon\Carbon::parse($career->until_date)->diffForHumans() }}</small>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 p-3 bg-white rounded shadow-sm border">
                        <h6 class="fw-bold mb-2">Tips Melamar</h6>
                        <ul class="small text-muted ps-3 mb-0">
                            <li>Gunakan CV terbaru dalam format PDF.</li>
                            <li>Pastikan nomor HP & Email aktif.</li>
                            <li>Siapkan berkas pendukung sesuai persyaratan.</li>
                        </ul>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <style>
        .bg-soft-success {
            background-color: #e8fadf;
        }

        .job-description ul {
            padding-left: 20px;
        }

        .job-description p {
            line-height: 1.6;
            color: #4b5563;
        }
    </style>
@endsection
