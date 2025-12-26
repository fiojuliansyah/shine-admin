@extends('layouts.main')

@section('content')
    <div class="page-wrapper">
        <div class="content">
            <!-- Breadcrumb -->
            <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
                <div class="my-auto mb-2">
                    <h2 class="mb-1">Securty Patroll</h2>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href=""><i class="ti ti-smart-home"></i></a>
                            </li>
                            <li class="breadcrumb-item">
                                Productivity
                            </li>
                            <li class="breadcrumb-item" aria-current="page">Securty Patroll</li>
                        </ol>
                    </nav>
                </div>
            </div>

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="mb-0">List Floor</h3>
                    {{-- <a href="{{ route('printFloors', $id) }}" class="btn btn-primary">
                        <i class="ti ti-printer me-2"></i> Cetak All Floor
                    </a> --}}
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 5%">#</th>
                                    <th>Nama Floor</th>
                                    <th>Deskripsi</th>
                                    <th style="width: 15%">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($floors as $index => $floor)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $floor->name }}</td>
                                        <td>{{ $floor->description ?? '-' }}</td>
                                        <td>
                                            <a href="{{ route('securty-patroll.showTask', $floor->id) }}"
                                                class="btn btn-sm btn-primary">
                                                <i class="ti ti-edit"></i>
                                                Lihat Detail
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">Belum ada floor</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script src="/admin/assets/plugins/fullcalendar/index.global.min.js"></script>
    <script src="/admin/assets/js/tasksPlanner.js"></script>
@endpush
