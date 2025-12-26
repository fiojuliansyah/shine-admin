@extends('layouts.main')

@section('content')
    <div class="page-wrapper">
        <div class="content">
            <!-- Breadcrumb -->
            <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
                <div class="my-auto mb-2">
                    <h2 class="mb-1">Task Planner</h2>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href=""><i class="ti ti-smart-home"></i></a>
                            </li>
                            <li class="breadcrumb-item">
                                Productivity
                            </li>
                            <li class="breadcrumb-item" aria-current="page">Task Planner</li>
                            <li class="breadcrumb-item active" aria-current="page">{{ $site->name }}</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex my-xl-auto right-content align-items-center flex-wrap">
                    <div class="mb-2">
                        <a href="#" data-bs-toggle="modal" data-bs-target="#importModal"
                            class="btn btn-white d-flex align-items-center me-2">
                            <i class="ti ti-file-upload me-1"></i> Import Jadwal
                        </a>
                    </div>
                    <div class="mb-2">
                        <a href="#" id="btn_add" data-bs-toggle="modal" data-bs-target="#addJobdeskModal"
                            class="btn btn-primary d-flex align-items-center">
                            <i class="ti ti-circle-plus me-2"></i> Add Jobdesk to Task Plan
                        </a>
                    </div>
                </div>
            </div>

            <div class="row">

                <!-- Calendar Sidebar -->
                <div class="col-xxl-3 col-xl-4">
                    <div class="card">
                        <div class="card-body p-3">
                            <div class="border-bottom pb-2 mb-4">
                                <div class="datepic"></div>
                            </div>
                            <div class="border-bottom pb-2 mb-4">
                                <div class="d-flex align-items-center justify-content-between mb-2 fc-event">
                                    <h5>Jobdesk</h5>
                                    <a href="#" class="link-primary" data-bs-toggle="modal"
                                        data-bs-target="#jobdeskModal"><i
                                            class="ti ti-square-rounded-plus-filled fs-16"></i></a>
                                </div>
                                <div id="external-events">
                                    @foreach ($jobdesks as $job)
                                        <div class="rounded bg-primary text-white p-2 mb-2">{{ $job->name }} -
                                            {{ $job->service_type }}</div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <!-- List Task Sidebar -->

                <div class="col-xxl-9 col-xl-8 theiaStickySidebar">
                    <div class="card border-0 p-3">
                        <h3>List Task Planner</h2>
                            {{-- list task item --}}
                            <div class="mt-3">
                                <div class="mb-2">
                                    <span class="mb-2">Hari ini</span>
                                    @foreach ($tasksTodays as $taskToday)
                                        <div class="rounded shadow-md p-2 mb-2" style="background-color: #f8f9fa">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <h3 class="text-primary" style="font-size:18px">{{ $taskToday->name }}
                                                </h3>

                                                <i class="ti ti-dots-vertical" style="font-size:14px; cursor:pointer"
                                                    type="button" data-bs-toggle="dropdown"></i>
                                                <ul class="dropdown-menu">
                                                    <li>
                                                        <button class="btn_edit dropdown-item" data-bs-toggle="modal"
                                                            data-bs-target="#editJobdeskModal"
                                                            data-id="{{ $taskToday->id }}">Edit</button>
                                                    </li>
                                                    <li>
                                                        <form action="{{ route('tasks.delete', $taskToday->id) }}"
                                                            method="post">
                                                            @method('delete')
                                                            @csrf
                                                            <button class="form-control text-start"
                                                                style="color: red">Hapus</button>
                                                        </form>
                                                    </li>
                                                </ul>
                                            </div>

                                            <div class="mt-2 d-flex align-items-center gap-2">
                                                <small>
                                                    <i class="me-1 ti ti-clock" style="font-size: 15px"></i>
                                                    {{ $taskToday->start_time }}
                                                </small>
                                                <small>
                                                    <i class="me-1 ti ti-stairs" style="font-size: 15px"></i>
                                                    {{ $taskToday->floor->name }}
                                                </small>
                                                <small class="ms-auto badge bg-primary text-white">
                                                    <i class="me-1 ti ti-calendar" style="font-size: 15px"></i>
                                                    {{ $taskToday->date }}
                                                </small>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                {{-- Hari Selanjutnya --}}
                                <div class="my-2">
                                    <span class="mb-3">Hari Selanjutnya</span>
                                    @foreach ($tasksNextDays as $tasksNextDay)
                                        <div class="rounded shadow-md p-2 my-2" style="background-color: #f8f9fa">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <h3 class="text-primary" style="font-size:18px">{{ $tasksNextDay->name }}
                                                </h3>

                                                <i class="ti ti-dots-vertical" style="font-size:14px; cursor:pointer"
                                                    type="button" data-bs-toggle="dropdown"></i>
                                                <ul class="dropdown-menu">
                                                    <li>
                                                        <button class="btn_edit dropdown-item" data-bs-toggle="modal"
                                                            data-bs-target="#editJobdeskModal"
                                                            data-id="{{ $tasksNextDay->id }}">Edit</button>
                                                    </li>
                                                    <li>
                                                        <form action="{{ route('tasks.delete', $tasksNextDay->id) }}"
                                                            method="post">
                                                            @method('delete')
                                                            @csrf
                                                            <button class="form-control text-start"
                                                                style="color: red">Hapus</button>
                                                        </form>
                                                    </li>
                                                </ul>
                                            </div>
                                            <div class="mt-2 d-flex align-items-center gap-2">
                                                <small>
                                                    <i class="me-1 ti ti-clock" style="font-size: 15px"></i>
                                                    {{ $tasksNextDay->start_time }}
                                                </small>
                                                <small>
                                                    <i class="me-1 ti ti-stairs" style="font-size: 15px"></i>
                                                    {{ $tasksNextDay->floor->name }}
                                                </small>
                                                <small class="ms-auto badge bg-primary text-white">
                                                    <i class="me-1 ti ti-calendar" style="font-size: 15px"></i>
                                                    {{ $tasksNextDay->date }}
                                                </small>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <div class="mt-3 d-flex justify-content-end">
                                    {{ $tasksNextDays->links() }}
                                </div>
                            </div>
                    </div>
                </div>

            </div>

            <!-- Modal for Import -->
            <div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel"
                aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form id="bulk-update-form" action="{{ route('tasks.import') }}" method="POST"
                            enctype="multipart/form-data">
                            @csrf
                            <div class="modal-header">
                                <h5 class="modal-title" id="importModalLabel">Import Schedule</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
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
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light me-2" data-bs-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-primary">Upload</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- add task --}}
            <div class="modal fade" id="addJobdeskModal" tabindex="-1" aria-labelledby="addJobdeskModalLabel"
                aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form action="{{ route('jobdesk-to-task.store') }}" method="POST">
                            @csrf
                            <div class="modal-header">
                                <h5 class="modal-title" id="addJobdeskModalLabel">Add Jobdesk to Planner</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <input type="hidden" name="site_id" value="{{ $site->id }}">

                                <div class="mb-3">
                                    <label for="jobdesk" class="form-label">Select Jobdesk</label>
                                    <select name="jobdesk" id="jobdesk" class="form-control" required>
                                        @foreach ($jobdesks as $job)
                                            <option value="{{ $job->id }}">{{ $job->name }} -
                                                {{ $job->service_type }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="start_date" class="form-label">Start Date</label>
                                    <input type="date" id="start_date" class="form-control" name="start_date"
                                        required>
                                </div>

                                <div class="mb-3">
                                    <label for="end_date" class="form-label">End Date</label>
                                    <input type="date" id="end_date" class="form-control" name="end_date" required>
                                </div>

                                <div class="mb-3">
                                    <label for="time" class="form-label">Select Time</label>
                                    <input type="time" class="form-control" id="time" name="time" required>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light me-2" data-bs-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-primary">Add to Planner</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- edit task --}}
            <div class="modal fade" id="editJobdeskModal" tabindex="-1" aria-labelledby="editJobdeskModalLabel"
                aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form action="{{ route('jobdesk-to-task.update') }}" method="POST">
                            @csrf
                            <input type="text" hidden name="id_planner" class="id_planner">
                            <div class="modal-header">
                                <h5 class="modal-title" id="addJobdeskModalLabel">Edit Jobdesk to Planner</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <input type="hidden" name="site_id" value="{{ $site->id }}">

                                <div class="mb-3">
                                    <label for="jobdesk" class="form-label">Select Jobdesk</label>
                                    <select name="jobdesk" id="jobdesk" class="form-control jobdesk" required>
                                        @foreach ($jobdesks as $job)
                                            <option value="{{ $job->id }}">{{ $job->name }} -
                                                {{ $job->service_type }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="start_date" class="form-label">Start Date</label>
                                    <input type="date" id="start_date" class="form-control start_date"
                                        name="start_date" required>
                                </div>

                                <div class="mb-3">
                                    <label for="time" class="form-label">Select Time</label>
                                    <input type="time" class="form-control time" id="time" name="time"
                                        required>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light me-2" data-bs-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-primary">Edit to Planner</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Modal for Creating Jobdesk -->
            <div class="modal fade" id="jobdeskModal" tabindex="-1" aria-labelledby="jobdeskModalLabel"
                aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form action="{{ route('tasks.jobdesk.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="modal-header">
                                <h5 class="modal-title" id="jobdeskModalLabel">buat Jobdesk</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <input type="hidden" name="site_id" value="{{ $site->id }}">
                                <div class="form-group mb-3">
                                    <label for="work_type">Tipe Pekerjaan</label>
                                    <select name="work_type" id="work_type" class="form-control">
                                        <option value="">None</option>
                                        <option value="daily">Daily</option>
                                        <option value="weekly">Weekly</option>
                                        <option value="monthly">Monthly</option>
                                    </select>
                                </div>
                                <div class="form-group mb-3" id="name_field">
                                    <label for="name">Nama Jobdesk</label>
                                    <input type="text" name="name" id="name" class="form-control"
                                        placeholder="Enter Jobdesk Name">
                                </div>
                                <div class="form-group mb-3" id="job_code_field">
                                    <label for="job_code">Code Jobdesk</label>
                                    <input type="text" name="job_code" id="job_code" class="form-control"
                                        placeholder="Create Jobdesk Code">
                                </div>
                                <div class="dropdown mb-3">
                                    <label for="floor_id">Floor/Point area</label>
                                    <select name="floor_id" id="floor_id" class="form-select select2">
                                        @foreach ($floors as $floor)
                                            <option value="{{ $floor->id }}">
                                                {{ $floor->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group mb-3">
                                    <label for="service_type">Tipe Layanan</label>
                                    <select name="service_type" id="service_type" class="form-control">
                                        <option value="">None</option>
                                        <option value="cleaning">Cleaning</option>
                                        <option value="engineering">Engineering</option>
                                        <option value="pest-control">Pest Control</option>
                                    </select>
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

@push('js')
    <script src="/admin/assets/plugins/fullcalendar/index.global.min.js"></script>
    <script src="/admin/assets/js/tasksPlanner.js"></script>
    <script>
        $(document).ready(function() {
            // untuk select2 di modal #jobdeskModal
            $('#jobdeskModal .select2').select2({
                dropdownParent: $('#jobdeskModal')
            });
        });
    </script>
@endpush
