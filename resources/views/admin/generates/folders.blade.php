@extends('admin.layouts.main')

@section('content')
    <div class="page-wrapper">
        <div class="content">
            <!-- Breadcrumb -->
            <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
                <div class="my-auto mb-2">
                    <h2 class="mb-1">Surat Terbit</h2>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="{{ route('dashboard') }}"><i class="ti ti-smart-home"></i></a>
                            </li>
                            <li class="breadcrumb-item">HR</li>
                            <li class="breadcrumb-item active" aria-current="page">Surat Terbit</li>
                        </ol>
                    </nav>
                </div>
            </div>
            <!-- /Breadcrumb -->

            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
                    <h5>Folder Surat Terbit</h5>
                    <span class="text-muted small">Pilih tipe surat untuk melihat surat yang telah diterbitkan</span>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        @forelse ($types as $type)
                            <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
                                <a href="{{ route('generates.index', ['type_id' => $type->id]) }}"
                                    class="d-block text-decoration-none text-reset">
                                    <div class="card border shadow-none mb-0 h-100 folder-card">
                                        <div class="card-body text-center p-3">
                                            <div class="mb-2">
                                                <i class="ti ti-folder-filled text-warning" style="font-size: 48px;"></i>
                                            </div>
                                            <h6 class="mb-1 text-truncate" title="{{ $type->name }}">{{ $type->name }}</h6>
                                            <span class="badge bg-primary-transparent">{{ $type->generates_count }} surat</span>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        @empty
                            <div class="col-12">
                                <div class="text-center text-muted py-4">Belum ada tipe surat.</div>
                            </div>
                        @endforelse

                        @if ($uncategorizedCount > 0)
                            <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
                                <a href="{{ route('generates.index', ['type_id' => 'none']) }}"
                                    class="d-block text-decoration-none text-reset">
                                    <div class="card border shadow-none mb-0 h-100 folder-card">
                                        <div class="card-body text-center p-3">
                                            <div class="mb-2">
                                                <i class="ti ti-folder text-secondary" style="font-size: 48px;"></i>
                                            </div>
                                            <h6 class="mb-1 text-truncate">Tanpa Tipe</h6>
                                            <span class="badge bg-secondary-transparent">{{ $uncategorizedCount }} surat</span>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('css')
<style>
    .folder-card {
        transition: transform .15s ease, box-shadow .15s ease;
        cursor: pointer;
    }
    .folder-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 .5rem 1rem rgba(0, 0, 0, .1) !important;
    }
</style>
@endpush
