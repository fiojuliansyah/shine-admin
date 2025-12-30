@extends('website.layouts.app')

@section('content')
    <div class="page-wrapper">
        <div class="content">

            <div class="welcome-wrap mb-4">
                <div class="d-flex align-items-center justify-content-between flex-wrap">
                    <div class="mb-3">
                        <h2 class="mb-1 text-white">Riwayat Lamaran</h2>
                        <p class="text-white">Pantau status seluruh lamaran kerja yang telah Anda kirimkan.</p>
                    </div>
                </div>
                <div class="welcome-bg">
                    <img src="/admin/assets/img/bg/welcome-bg-02.svg" alt="img" class="welcome-bg-01">
                    <img src="/admin/assets/img/bg/welcome-bg-03.svg" alt="img" class="welcome-bg-02">
                    <img src="/admin/assets/img/bg/welcome-bg-01.svg" alt="img" class="welcome-bg-03">
                </div>
            </div>

            <div class="row">
                <div class="col-sm-12">
                    <div class="card border-0">
                        <div class="card-header">
                            <h5 class="card-title">Daftar Lamaran Anda</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-nowrap mb-0">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Posisi Pekerjaan</th>
                                            <th>Tanggal Melamar</th>
                                            <th>Status Terakhir</th>
                                            <th class="text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($applicants as $app)
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="ms-2">
                                                            <h6 class="fw-medium mb-0">{{ $app->career->name ?? 'Posisi Tidak Diketahui' }}</h6>
                                                            <span class="fs-12 text-muted">SHINE Karir Official</span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>{{ $app->created_at->format('d M Y, H:i') }}</td>
                                                <td>
                                                    @php
                                                        // Logika warna badge berdasarkan status (asumsi status_id atau status name)
                                                        $badgeClass = 'bg-warning-transparent text-warning'; // Default Pending
                                                        $statusName = $app->status->name ?? 'Review Berkas';

                                                        if(str_contains(strtolower($statusName), 'terima') || str_contains(strtolower($statusName), 'lolos')) {
                                                            $badgeClass = 'bg-success-transparent text-success';
                                                        } elseif(str_contains(strtolower($statusName), 'tolak') || str_contains(strtolower($statusName), 'gagal')) {
                                                            $badgeClass = 'bg-danger-transparent text-danger';
                                                        }
                                                    @endphp
                                                    <span class="badge {{ $badgeClass }} border-0">
                                                        {{ $statusName }}
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <a href="{{ route('web.applicants.careers.detail', $app->career->slug ?? '#') }}" class="btn btn-sm btn-outline-primary">
                                                        <i class="ti ti-eye me-1"></i> Detail Job
                                                    </a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center py-5">
                                                    <img src="/admin/assets/img/bg/empty-state.svg" alt="no-data" style="width: 150px;" class="mb-3">
                                                    <p class="text-muted">Anda belum melamar pekerjaan apapun.</p>
                                                    <a href="{{ route('web.applicants.careers.index') }}" class="btn btn-primary btn-sm">Cari Lowongan</a>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection