@extends('layouts.main')

@section('content')
    <div class="page-wrapper">
        <div class="content">

            <!-- Breadcrumb -->
            <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
                <div class="my-auto mb-2">
                    <h2 class="mb-1">Lantai</h2>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="{{ route('dashboard') }}"><i class="ti ti-smart-home"></i></a>
                            </li>
                            <li class="breadcrumb-item">
                                CRM
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">
                                Lantai
                            </li>
                        </ol>
                    </nav>
                </div>

                {{-- tambah lantai --}}
                <div class="d-flex my-xl-auto right-content align-items-center flex-wrap ">
                    <div class="me-2 mb-2">
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
                    </div>
                    <div class="mb-2">
                        <button data-bs-toggle="modal" data-bs-target="#addfloorModal"
                            class="btn btn-primary d-flex align-items-center">
                            <i class="ti ti-circle-plus me-2"></i>Tambah Floor
                        </button>
                    </div>
                </div>
            </div>
            <!-- /Breadcrumb -->

            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
                    <h5>List Lantai</h5>
                    <form method="GET" class="d-flex align-items-center gap-2">
                        <div class="mt-2">
                            <select name="site_id" class="form-control select2" style="width: 300px">
                                <option value="">Site Option</option>
                                @foreach ($sites as $site)
                                    <option value="{{ $site->id }}" {{ $filter['site_id'] == $site->id ? 'selected' : ''}}>{{ $site->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-2">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="ti ti-filter me-1"></i> Filter
                            </button>
                            <a href="{{ route('floors.index') }}" class="btn btn-outline-secondary">
                                <i class="ti ti-refresh me-1"></i> Reset
                            </a>
                        </div>
                    </form>
                </div>
                <div class="card-body p-0">
                    <div class="custom-datatable-filter table-responsive">
                        {{ $dataTable->table() }}
                    </div>
                </div>
            </div>

        </div>
        <!-- Add Floor Modal -->
        <div class="modal fade" id="addfloorModal" tabindex="-1" aria-labelledby="addFloorLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="{{ route('floors.store') }}" method="POST">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title" id="addFloorLabel-">Tambah Floor</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="site">Site</label>
                                <select class="form-select" name="site_id" id="site" aria-label="edit site">
                                    @foreach ($sites as $site)
                                        <option value ="{{ $site->id }}">{{ $site->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="name-" class="form-label">Nama Floor</label>
                                <input type="text" name="name" id="name-" class="form-control" value=""
                                    required>
                            </div>

                            <div class="mb-3">
                                <label for="description-" class="form-label">Deskripsi</label>
                                <textarea name="description" id="description-" class="form-control" rows="3"></textarea>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Tambah Site</h4>
                        <button type="button" class="btn-close custom-btn-close" data-bs-dismiss="modal"
                            aria-label="Close">
                            <i class="ti ti-x"></i>
                        </button>
                    </div>
                    <form action="{{ route('floors.import') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="modal-body pb-0">
                            <div class="row">
                                <div class="mb-3">
                                    <label class="form-label">Site</label>
                                    <select class="form-select select2" name="site_id" required>
                                        <option value="">-- Pilih Site --</option>
                                        @foreach ($sites as $site)
                                            <option value="{{ $site->id }}">{{ $site->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">File Import</label>
                                    <input type="file" class="form-control" name="file" required>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light me-2" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary">Simpan</button>
                        </div>
                    </form>
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
            $('#importModal').on('shown.bs.modal', function () {
                $(this).find('.select2').select2({
                    dropdownParent: $('#importModal')
                });
            });
        });
    </script>
@endpush
