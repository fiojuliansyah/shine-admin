@extends('layouts.main')

@section('content')
<div class="page-wrapper">
    <div class="content">
        <!-- Page Title -->
        <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
            <div class="my-auto mb-2">
                <h2 class="mb-1">Jabatan</h2>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item">
                            <a href="index.html"><i class="ti ti-smart-home"></i></a>
                        </li>
                        <li class="breadcrumb-item">
                            HRM
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">Data Jabatan</li>
                    </ol>
                </nav>
            </div>
            <div class="d-flex my-xl-auto right-content align-items-center flex-wrap ">
                <div class="mb-2">
                    <a href="#" data-bs-toggle="modal" data-bs-target="#createModal" class="btn btn-primary d-flex align-items-center"><i class="ti ti-circle-plus me-2"></i>Tambah Role</a>
                </div>
            </div>
        </div>


        <!-- Card Layout -->
        <div class="row">
            @foreach ($roles as $role)
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">{{ $role->name }}</h5>
                        </div>
                        <div class="card-body">
                            <h6 class="text-muted">Permissions:</h6>
                            @foreach ($role->permissions->groupBy('category') as $category => $permissionGroup)
                                <div class="mb-2">
                                    <strong>{{ $category }}:</strong>
                                    @foreach ($permissionGroup as $permission)
                                        @if ($permission->status == '1')
                                            <span class="badge bg-primary">{{ $permission->mock }}</span>
                                        @endif
                                    @endforeach
                                </div>
                            @endforeach
                        </div>
                        <div class="card-footer d-flex justify-content-between">
                            <!-- Edit Button -->
                            <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editModal-{{ $role->id }}">
                                Edit
                            </button>
                            <!-- Delete Button -->
                            <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal-{{ $role->id }}">
                                Delete
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

@foreach ($roles as $role)
    <!-- Edit Modal -->
    <div class="modal fade" id="editModal-{{ $role->id }}" tabindex="-1" aria-labelledby="editModalLabel-{{ $role->id }}" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('roles.update', $role->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title" id="editModalLabel-{{ $role->id }}">Edit Jabatan</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="col-form-label">nama Jabatan</label>
                            <input type="text" class="form-control" name="name" value="{{ $role->name }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="col-form-label">Hak & Akses</label>
                            @foreach ($permissions->groupBy('category') as $category => $permissionGroup)
                                <div class="mb-2">
                                    <strong>{{ $category }}:</strong>
                                    <div>
                                        @foreach ($permissionGroup as $permission)
                                            @if ($permission->status == '1')
                                                <label class="form-check form-check-inline">
                                                    <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $permission->name }}" {{ $role->hasPermissionTo($permission->name) ? 'checked' : '' }}>
                                                    <span class="form-check-label">{{ $permission->mock }}</span>
                                                </label>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
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

    <!-- Delete Modal -->
    <div class="modal fade" id="deleteModal-{{ $role->id }}" tabindex="-1" aria-labelledby="deleteModalLabel-{{ $role->id }}" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('roles.destroy', $role->id) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="modal-header">
                        <h5 class="modal-title" id="deleteModalLabel-{{ $role->id }}">hapus Jabatan</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>Apakah kamu yakin ingin menghapus jabatan <strong>{{ $role->name }}</strong>?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light me-2" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger">Hapus</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endforeach

<!-- Create Modal -->
<div class="modal fade" id="createModal" tabindex="-1" aria-labelledby="createModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('roles.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="createModalLabel">Tambah Jabatan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="col-form-label">Nama Jabatan</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label class="col-form-label">Hak & Akses</label>
                        @foreach ($permissions->groupBy('category') as $category => $permissionGroup)
                            <div class="mb-2">
                                <strong>{{ $category }}:</strong>
                                <div>
                                    @foreach ($permissionGroup as $permission)
                                        @if ($permission->status == '1')
                                            <label class="form-check form-check-inline">
                                                <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $permission->name }}">
                                                <span class="form-check-label">{{ $permission->mock }}</span>
                                            </label>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
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
@endsection


@push('js')
    <!-- Required datatable js -->
<script src="/dashboard/assets/libs/datatables.net/js/jquery.dataTables.min.js"></script>
<script src="/dashboard/assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js"></script>
<!-- Buttons examples -->
<script src="/dashboard/assets/libs/datatables.net-buttons/js/dataTables.buttons.min.js"></script>
<script src="/dashboard/assets/libs/datatables.net-buttons-bs4/js/buttons.bootstrap4.min.js"></script>
<script src="/dashboard/assets/libs/jszip/jszip.min.js"></script>
<script src="/dashboard/assets/libs/pdfmake/build/pdfmake.min.js"></script>
<script src="/dashboard/assets/libs/pdfmake/build/vfs_fonts.js"></script>
<script src="/dashboard/assets/libs/datatables.net-buttons/js/buttons.html5.min.js"></script>
<script src="/dashboard/assets/libs/datatables.net-buttons/js/buttons.print.min.js"></script>
<script src="/dashboard/assets/libs/datatables.net-buttons/js/buttons.colVis.min.js"></script>
<!-- Responsive examples -->
<script src="/dashboard/assets/libs/datatables.net-responsive/js/dataTables.responsive.min.js"></script>
<script src="/dashboard/assets/libs/datatables.net-responsive-bs4/js/responsive.bootstrap4.min.js"></script>

<!-- Datatable init js -->
<script src="/dashboard/assets/js/pages/datatables.init.js"></script>
@endpush