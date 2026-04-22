@extends('website.layouts.app')

@section('content')
    <div class="page-wrapper">
        <div class="content">

            <div class="welcome-wrap mb-4">
                <div class="d-flex align-items-center justify-content-between flex-wrap">
                    <div class="mb-3">
                        <h2 class="mb-1 text-white">Riwayat Pemberkasan</h2>
                        <p class="text-white">Kelola, lihat, dan tanda tangani dokumen digital Anda secara aman.</p>
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
                            <h5 class="card-title">Daftar Dokumen Digital</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-nowrap mb-0">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Informasi Dokumen</th>
                                            <th>Area / Site</th>
                                            <th>Tanggal Diterbitkan</th>
                                            <th>Status TTD</th>
                                            <th class="text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($histories as $history)
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar avatar-md bg-soft-primary flex-shrink-0">
                                                            <i class="ti ti-file-text fs-20 text-primary"></i>
                                                        </div>
                                                        <div class="ms-2">
                                                            <h6 class="fw-medium mb-0">{{ $history->letter->title ?? 'Surat Digital' }}</h6>
                                                            <span class="fs-12 text-muted">{{ $history->letter_number ?? 'No. Belum Tersedia' }}</span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>{{ $history->site->name ?? '-' }}</td>
                                                <td>{{ $history->created_at->format('d M Y') }}</td>
                                                <td>
                                                    @if($history->second_party_esign)
                                                        <span class="badge bg-success-transparent text-success border-0">
                                                            <i class="ti ti-check me-1"></i> Sudah TTD
                                                        </span>
                                                    @else
                                                        @if($history->esign == 'no-need')
                                                            <span class="badge bg-light text-muted border-0">Tanpa TTD</span>
                                                        @else
                                                            <span class="badge bg-warning-transparent text-warning border-0">
                                                                <i class="ti ti-clock me-1"></i> Menunggu TTD
                                                            </span>
                                                        @endif
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    <a href="{{ route('web.applicants.letter.detail', $history->id) }}" class="btn btn-sm btn-outline-primary">
                                                        <i class="ti ti-eye me-1"></i> Detail Dokumen
                                                    </a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center py-5">
                                                    <p class="text-muted">Belum ada dokumen digital yang tersedia untuk Anda.</p>
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

@push('css')
<style>
    .avatar-md {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
    }
    .bg-soft-primary {
        background-color: rgba(13, 110, 253, 0.1);
    }
</style>
@endpush