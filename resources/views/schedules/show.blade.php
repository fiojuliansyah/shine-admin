@extends('layouts.main')

@section('content')
<div class="page-wrapper">
    <div class="content">

        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
            <div class="my-auto mb-2">
                <h2 class="mb-1">{{ $site->name }}</h2>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item">
                            <a href="index.html"><i class="ti ti-smart-home"></i></a>
                        </li>
                        <li class="breadcrumb-item">
                            HRM
                        </li>
                        <li class="breadcrumb-item" aria-current="page">Jadwal Project</li>
                        <li class="breadcrumb-item active" aria-current="page">{{ $site->name }}</li>
                    </ol>
                </nav>
            </div>
            <div class="d-flex my-xl-auto right-content align-items-center flex-wrap">
                <div class="mb-2">
                    <a href="#" data-bs-toggle="modal" data-bs-target="#importModal" class="btn btn-white d-flex align-items-center me-2">
                        <i class="ti ti-file-upload me-1"></i> Import Jadwal
                    </a>
                </div>
                <div class="mb-2">
                    <a href="#" data-bs-toggle="modal" data-bs-target="#shiftModal" class="btn btn-primary d-flex align-items-center">
                        <i class="ti ti-circle-plus me-2"></i> Create Shift
                    </a>
                </div>
            </div>
        </div>
        <!-- /Page Header -->

        <!-- Table Section -->
        <div class="card">
            <div class="card-body">
                <div style="overflow-x: scroll; white-space: nowrap; position: relative; height: auto;">
                    <table class="table table-bordered dt-responsive nowrap" 
                        style="font-size: 12px; table-layout: fixed; min-width: 100%; display: block;">
                        <thead class="text-center">
                            <tr>
                                <th style="position: sticky; left: 0; background-color: white; z-index: 10; border-right: 2px solid #dee2e6; width: 10%;">NIK Karyawan</th>
                                <th style="position: sticky; left: 100px; background-color: white; z-index: 10; border-right: 2px solid #dee2e6;">Nama</th>
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
                                    <td style="position: sticky; left: 0; background-color: white; z-index: 9; text-align: center; border-right: 2px solid #dee2e6;">
                                        {{ $user->employee_nik }}
                                    </td>
                                    <td style="position: sticky; left: 100px; background-color: white; z-index: 9; border-right: 2px solid #dee2e6;">
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
                                            <td style="text-align: center;">
                                                {{ $schedule->clock_in ?? '-' }}
                                            </td>
                                            <td style="text-align: center;">
                                                {{ $schedule->clock_out ?? '-' }}
                                            </td>
                                        @endif
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Modal for Import -->
        <div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form id="bulk-update-form" action="{{ route('schedules.import') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title" id="importModalLabel">Import Schedule</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="site_id" value="{{ $site->id }}">
                            
                            <div class="mb-3">
                                <label for="month" class="form-label">Select Month</label>
                                <input type="month" class="form-control" id="month" name="month" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="file" class="form-label">Upload Excel File</label>
                                <input type="file" class="form-control" id="file" name="file" required>
                                <small class="text-muted">Accepted formats: .xlsx, .csv</small>
                            </div>

                            <div class="mb-3">
                                <label for="late" class="form-label">Default Late (Minutes)</label>
                                <input type="number" class="form-control" id="late" name="late" value="0" min="0">
                                <small class="text-muted">Enter default late minutes if any.</small>
                            </div>

                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light me-2" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Upload</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal for Creating Shift -->
        <div class="modal fade" id="shiftModal" tabindex="-1" aria-labelledby="shiftModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="{{ route('schedules.shift.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title" id="shiftModalLabel">Create Shift</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="site_id" value="{{ $site->id }}">
                            <div class="form-group mb-3" id="name_field">
                                <label for="name">Shift Name</label>
                                <input type="text" name="name" id="name" class="form-control" placeholder="Enter Shift Name">
                            </div>
                            <div class="form-group mb-3" id="shift_code_field">
                                <label for="shift_code">Shift Code</label>
                                <input type="text" name="shift_code" id="shift_code" class="form-control" placeholder="Create Shift Code">
                            </div>
                            <div class="form-group mb-3" id="clock_in_field">
                                <label for="clock_in">Clock In</label>
                                <input type="time" name="clock_in" id="clock_in" class="form-control">
                            </div>
                            <div class="form-group mb-3" id="clock_out_field">
                                <label for="clock_out">Clock Out</label>
                                <input type="time" name="clock_out" id="clock_out" class="form-control">
                            </div>
                            <div class="form-group mb-3">
                                <label for="type">Shift Type</label>
                                <select name="type" id="type" class="form-control">
                                    <option value="">None</option>
                                    <option value="off">OFF</option>
                                    <option value="leave">Leave</option>
                                </select>
                            </div>                    

                            <div class="mt-4">
                                <h6>Shift List</h6>
                                <div class="list-group">
                                    @foreach($shifts as $shift)
                                        <div class="list-group-item d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong>{{ $shift->name }}</strong> 
                                                ({{ $shift->shift_code }})<br>
                                                <small>Clock In: {{ $shift->clock_in }} | Clock Out: {{ $shift->clock_out }}</small>
                                            </div>
                                        </div>
                                    @endforeach                        
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light me-2" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary">Tambah</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

