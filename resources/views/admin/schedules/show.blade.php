@extends('admin.layouts.main')

@section('content')
    <div class="page-wrapper">
        <div class="content">

            <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
                <div class="my-auto mb-2">
                    <h2 class="mb-1">{{ $site->name }}</h2>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="index.html"><i class="ti ti-smart-home"></i></a>
                            </li>
                            <li class="breadcrumb-item">HRM</li>
                            <li class="breadcrumb-item">Jadwal Project</li>
                            <li class="breadcrumb-item active">{{ $site->name }}</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex my-xl-auto right-content align-items-center flex-wrap">
                    <div class="mb-2">
                        <a href="#" data-bs-toggle="modal" data-bs-target="#cleanModal"
                            class="btn btn-white d-flex align-items-center me-2 text-danger">
                            <i class="ti ti-trash me-1"></i> Clean Schedule
                        </a>
                    </div>
                    <div class="mb-2">
                        <a href="#" data-bs-toggle="modal" data-bs-target="#importModal"
                            class="btn btn-white d-flex align-items-center me-2">
                            <i class="ti ti-file-upload me-1"></i> Import Jadwal
                        </a>
                    </div>
                    <div class="mb-2">
                        <a href="#" data-bs-toggle="modal" data-bs-target="#shiftModal"
                            class="btn btn-primary d-flex align-items-center">
                            <i class="ti ti-circle-plus me-2"></i> Create Shift
                        </a>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div style="overflow-x: scroll; white-space: nowrap; position: relative; height: auto;">
                        <table class="table table-bordered dt-responsive nowrap"
                            style="font-size: 12px; table-layout: fixed; min-width: 100%; display: block;">
                            <thead class="text-center">
                                <tr>
                                    <th
                                        style="position: sticky; left: 0; background-color: white; z-index: 10; border-right: 2px solid #dee2e6; width: 10%;">
                                        NIK Karyawan</th>
                                    <th
                                        style="position: sticky; left: 100px; background-color: white; z-index: 10; border-right: 2px solid #dee2e6;">
                                        Nama</th>
                                    @foreach ($dates as $date)
                                        <th colspan="2">{{ \Carbon\Carbon::parse($date)->format('d M') }}</th>
                                    @endforeach
                                </tr>
                                <tr>
                                    <th style="position: sticky; left: 0; background-color: white; z-index: 10;"></th>
                                    <th style="position: sticky; left: 100px; background-color: white; z-index: 10;"></th>
                                    @foreach ($dates as $date)
                                        <th>Clock In</th>
                                        <th>Clock Out</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($groupedSchedules as $userId => $schedules)
                                    @php
                                        $user = $schedules->first()->user;
                                    @endphp
                                    <tr>
                                        <td
                                            style="position: sticky; left: 0; background-color: white; z-index: 9; text-align: center; border-right: 2px solid #dee2e6;">
                                            {{ $user->employee_nik }}
                                        </td>
                                        <td
                                            style="position: sticky; left: 100px; background-color: white; z-index: 9; border-right: 2px solid #dee2e6;">
                                            {{ $user->name }}
                                        </td>
                                        @foreach ($dates as $date)
                                            @php
                                                $schedule = $schedules->firstWhere('date', $date);
                                            @endphp
                                            @if ($schedule && in_array($schedule->type, ['off', 'leave']))
                                                <td colspan="2" style="text-align: center; vertical-align: middle;">
                                                    <strong>{{ strtoupper($schedule->type) }}</strong>
                                                </td>
                                            @else
                                                <td style="text-align: center;">{{ $schedule->clock_in ?? '-' }}</td>
                                                <td style="text-align: center;">{{ $schedule->clock_out ?? '-' }}</td>
                                            @endif
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="importModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form action="{{ route('schedules.import') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="modal-header">
                                <h5 class="modal-title">Import Schedule</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <input type="hidden" name="site_id" value="{{ $site->id }}">
                                <div class="mb-3">
                                    <label class="form-label">Select Month</label>
                                    <input type="month" class="form-control" name="month" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Upload Excel File</label>
                                    <input type="file" class="form-control" name="file" required>
                                    <small class="text-muted">Formats: .xlsx, .csv</small>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Default Late (Minutes)</label>
                                    <input type="number" class="form-control" name="late" value="0" min="0">
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-primary">Upload</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="shiftModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form action="{{ route('schedules.shift.store') }}" method="POST">
                            @csrf
                            <div class="modal-header">
                                <h5 class="modal-title">Create Shift</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <input type="hidden" name="site_id" value="{{ $site->id }}">
                                <div class="form-group mb-3">
                                    <label>Shift Name</label>
                                    <input type="text" name="name" class="form-control"
                                        placeholder="Enter Shift Name">
                                </div>
                                <div class="form-group mb-3">
                                    <label>Shift Code</label>
                                    <input type="text" name="shift_code" class="form-control"
                                        placeholder="Create Shift Code">
                                </div>
                                <div class="form-group mb-3">
                                    <label>Clock In</label>
                                    <input type="time" name="clock_in" class="form-control">
                                </div>
                                <div class="form-group mb-3">
                                    <label>Clock Out</label>
                                    <input type="time" name="clock_out" class="form-control">
                                </div>
                                <div class="form-group mb-3">
                                    <label>Shift Type</label>
                                    <select name="type" class="form-control">
                                        <option value="">None</option>
                                        <option value="off">OFF</option>
                                        <option value="leave">Leave</option>
                                    </select>
                                </div>
                                <div class="mt-4">
                                    <h6>Shift List</h6>
                                    <div class="list-group">
                                        @foreach ($shifts as $shift)
                                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                                <div>
                                                    <strong>{{ $shift->name }}</strong> ({{ $shift->shift_code }})<br>
                                                    <small>Clock In: {{ $shift->clock_in }} | Clock Out:
                                                        {{ $shift->clock_out }}</small>
                                                </div>
                                                <div>
                                                    <button type="button" class="btn btn-sm btn-outline-primary me-1"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#editShiftModal-{{ $shift->id }}">
                                                        <i class="ti ti-edit"></i>
                                                    </button>

                                                    <button type="button" class="btn btn-sm btn-outline-danger"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#deleteShiftModal-{{ $shift->id }}">
                                                        <i class="ti ti-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-primary">Tambah</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="cleanModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <form action="{{ route('schedules.clean', $site->id) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <div class="modal-header">
                                <h5 class="modal-title text-danger">Konfirmasi Hapus Jadwal</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="text-center mb-4">
                                    <i class="ti ti-alert-triangle text-danger" style="font-size: 3rem;"></i>
                                    <p class="mt-2">Anda akan menghapus data jadwal pada project:
                                        <br><strong>{{ $site->name }}</strong>
                                    </p>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Hapus Bulan Tertentu (Opsional)</label>
                                    <input type="month" name="filter_month" class="form-control">
                                    <div class="form-text">Kosongkan untuk menghapus seluruh jadwal di project ini.</div>
                                </div>
                                <div class="alert alert-light-danger bg-light-danger border-0 mb-0">
                                    <small class="text-danger fw-bold">Peringatan: Tindakan ini permanen.</small>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light me-2" data-bs-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-danger">Hapus Sekarang</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>
    @foreach ($shifts as $shift)
        <div class="modal fade" id="editShiftModal-{{ $shift->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content shadow-lg">
                    <form action="{{ route('schedules.shift.update', $shift->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="modal-header">
                            <h5 class="modal-title">Edit Shift: {{ $shift->name }}</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="form-group mb-3">
                                <label class="form-label">Shift Name</label>
                                <input type="text" name="name" class="form-control" value="{{ $shift->name }}"
                                    required>
                            </div>
                            <div class="form-group mb-3">
                                <label class="form-label">Shift Code</label>
                                <input type="text" name="shift_code" class="form-control"
                                    value="{{ $shift->shift_code }}" required>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label font-bold text-success">Clock In</label>
                                    <input type="time" name="clock_in" class="form-control"
                                        value="{{ $shift->clock_in }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label font-bold text-danger">Clock Out</label>
                                    <input type="time" name="clock_out" class="form-control"
                                        value="{{ $shift->clock_out }}">
                                </div>
                            </div>
                            <div class="form-group mb-3">
                                <label class="form-label">Shift Type</label>
                                <select name="type" class="form-control">
                                    <option value="" {{ $shift->type == '' ? 'selected' : '' }}>None</option>
                                    <option value="off" {{ $shift->type == 'off' ? 'selected' : '' }}>OFF</option>
                                    <option value="leave" {{ $shift->type == 'leave' ? 'selected' : '' }}>Leave</option>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer bg-light">
                            <button type="button" class="btn btn-light me-2" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach
@foreach ($shifts as $shift)
    <div class="modal fade" id="deleteShiftModal-{{ $shift->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="{{ route('schedules.shift.destroy', $shift->id) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="modal-header">
                        <h5 class="modal-title">Hapus Shift</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-center">
                        <div class="mb-3">
                            <i class="ti ti-alert-circle text-danger" style="font-size: 3rem;"></i>
                        </div>
                        <p>Apakah Anda yakin ingin menghapus shift <strong>{{ $shift->name }}</strong> ({{ $shift->shift_code }})?</p>
                        <p class="text-muted small">Tindakan ini tidak dapat dibatalkan.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger">Ya, Hapus</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endforeach
@endsection
