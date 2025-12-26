@extends('layouts.main')

@section('content')
    <div class="page-wrapper">
        <div class="content">

            <!-- Breadcrumb -->
            <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
                <div class="my-auto mb-2">
                    <h2 class="mb-1">Jobdesk Patroll</h2>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="{{ route('dashboard') }}"><i class="ti ti-smart-home"></i></a>
                            </li>
                            <li class="breadcrumb-item">
                                PRODUCTIVITY
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">
                                Jobdesk Patrolls
                            </li>
                        </ol>
                    </nav>
                </div>

                {{-- tambah jobdesk patroll --}}
                <div class="d-flex my-xl-auto right-content align-items-center flex-wrap ">
                    {{-- <div class="me-2 mb-2">
                        <div class="dropdown">
                            <a href="{{ route('floors.export') }}" class="btn btn-white d-inline-flex align-items-center">
                                <i class="ti ti-file-export me-1"></i>Export Excel
                            </a>
                        </div>
                    </div>
                    <div class="me-2 mb-2">
                        <div class="dropdown">
                            <a href="#" data-bs-toggle="modal" data-bs-target="#importModal"
                                class="btn btn-white d-inline-flex align-items-center">
                                <i class="ti ti-file-import me-1"></i>Import
                            </a>
                        </div>
                    </div> --}}
                    <div class="mb-2">
                        <button data-bs-toggle="modal" data-bs-target="#jobdeskModal"
                            class="btn btn-primary d-flex align-items-center">
                            <i class="ti ti-circle-plus me-2"></i>Tambah Jobdesk Patrol
                        </button>
                    </div>

                    <!-- Add jobdessk Modal -->
                    <div class="modal fade" id="jobdeskModal" tabindex="-1" aria-labelledby="jobdeskModalLabel"
                        aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form action="{{ route('jobdesk-patrolls.addJob') }}" method="POST">
                                    @csrf
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="jobdeskModalLabel">buat Jobdesk</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <input type="hidden" name="site_id" value="{{ $id }}">
                                        <div class="form-group mb-3">
                                            <label for="work_type">Tipe Pekerjaan</label>
                                            <select name="work_type" id="work_type" class="form-control">
                                                <option value="daily">Daily</option>
                                                <option value="weekly">Weekly</option>
                                                <option value="monthly">Monthly</option>
                                            </select>
                                        </div>
                                        <div class="form-group mb-3" id="name_field">
                                            <label for="name">Nama Jobdesk Patroll</label>
                                            <input type="text" name="name" id="name" class="form-control"
                                                placeholder="Enter Jobdesk Name">
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
                                            <input type="hidden" name="service_type" value="patroll">
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-light me-2"
                                            data-bs-dismiss="modal">Batal</button>
                                        <button type="submit" class="btn btn-primary">Tambah</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /Breadcrumb -->

            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
                    <h5>Jobdesks Patroll</h5>
                </div>
                <div class="card-body p-0">
                    <div class="custom-datatable-filter table-responsive">
                        {{ $dataTable->table() }}
                    </div>
                </div>
            </div>

        </div>

    </div>
@endsection

@push('css')
    <link rel="stylesheet" href="/admin/assets/css/dataTables.bootstrap5.min.css">
@endpush

@push('js')
    <script src="/admin/assets/js/jquery.dataTables.min.js"></script>
    <script src="/admin/assets/js/dataTables.bootstrap5.min.js"></script>
    {{ $dataTable->scripts(attributes: ['type' => 'module']) }}
    <script>
        $(document).ready(function() {
            // untuk select2 di modal #jobdeskModal
            $('#jobdeskModal .select2').select2({
                dropdownParent: $('#jobdeskModal')
            });
        });
    </script>
@endpush
