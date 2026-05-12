@extends('admin.layouts.main')

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
                            <a href="#"><i class="ti ti-smart-home"></i></a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">Data Jabatan</li>
                    </ol>
                </nav>
            </div>
            <div class="d-flex my-xl-auto right-content align-items-center flex-wrap ">
                <div class="mb-2">
                    <a href="#" data-bs-toggle="modal" data-bs-target="#createModal" class="btn btn-primary d-flex align-items-center">
                        <i class="ti ti-circle-plus me-2"></i>Tambah Role
                    </a>
                </div>
            </div>
        </div>

        <!-- Table Layout -->
        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-nowrap mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th style="width: 50px;">No</th>
                                <th>Nama Jabatan</th>
                                <th>Kode</th>
                                <th>Hak Akses (Permissions)</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($roles as $index => $role)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td><strong>{{ $role->name }}</strong></td>
                                    <td><span class="badge badge-soft-secondary">{{ $role->code }}</span></td>
                                    <td>
                                        @foreach ($role->permissions->groupBy('category') as $category => $permissionGroup)
                                            <div class="mb-1">
                                                <small class="text-muted fw-bold">{{ $category }}:</small>
                                                @foreach ($permissionGroup as $permission)
                                                    @if ($permission->status == '1')
                                                        <span class="badge bg-primary-transparent text-primary" style="font-size: 11px;">
                                                            {{ $permission->mock }}
                                                        </span>
                                                    @endif
                                                @endforeach
                                            </div>
                                        @endforeach
                                    </td>
                                    <td class="text-end">
                                        <div class="dropdown dropdown-action">
                                            <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editModal-{{ $role->id }}">
                                                <i class="ti ti-edit"></i> Edit
                                            </button>
                                            <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal-{{ $role->id }}">
                                                <i class="ti ti-trash"></i> Hapus
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- MODAL SECTION (Edit & Delete) --}}
@foreach ($roles as $role)
    <!-- Edit Modal -->
    <div class="modal fade" id="editModal-{{ $role->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg"> 
            <div class="modal-content">
                <form action="{{ route('roles.update', $role->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Jabatan: {{ $role->name }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nama Jabatan</label>
                                <input type="text" class="form-control" name="name" value="{{ $role->name }}" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Kode Jabatan</label>
                                <input type="text" class="form-control" name="code" value="{{ $role->code }}" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Hak & Akses</label>
                            <div class="border rounded p-3">
                                @foreach ($permissions->groupBy('category') as $category => $permissionGroup)
                                    <div class="mb-3">
                                        <h6 class="border-bottom pb-1">{{ $category }}</h6>
                                        <div class="row">
                                            @foreach ($permissionGroup as $permission)
                                                @if ($permission->status == '1')
                                                    <div class="col-md-4">
                                                        <div class="form-check form-check-inline">
                                                            <input class="form-check-input" type="checkbox" name="permissions[]" 
                                                                   value="{{ $permission->name }}" 
                                                                   id="perm-{{ $role->id }}-{{ $permission->id }}"
                                                                   {{ $role->hasPermissionTo($permission->name) ? 'checked' : '' }}>
                                                            <label class="form-check-label" for="perm-{{ $role->id }}-{{ $permission->id }}">
                                                                {{ $permission->mock }}
                                                            </label>
                                                        </div>
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Modal -->
    <div class="modal fade" id="deleteModal-{{ $role->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="{{ route('roles.destroy', $role->id) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="modal-body text-center p-4">
                        <i class="ti ti-alert-triangle text-danger display-4 mb-3"></i>
                        <h5>Konfirmasi Hapus</h5>
                        <p class="text-muted">Apakah Anda yakin ingin menghapus jabatan <strong>{{ $role->name }}</strong>? Tindakan ini tidak dapat dibatalkan.</p>
                        <div class="d-flex justify-content-center mt-4">
                            <button type="button" class="btn btn-light me-2" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-danger">Ya, Hapus</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endforeach

<!-- Create Modal -->
<div class="modal fade" id="createModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('roles.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Jabatan Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nama Jabatan</label>
                            <input type="text" class="form-control" name="name" placeholder="Contoh: Manager" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Kode Jabatan</label>
                            <input type="text" class="form-control" name="code" placeholder="Contoh: MNG" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Hak & Akses</label>
                        <div class="border rounded p-3">
                            @foreach ($permissions->groupBy('category') as $category => $permissionGroup)
                                <div class="mb-3">
                                    <h6 class="border-bottom pb-1">{{ $category }}</h6>
                                    <div class="row">
                                        @foreach ($permissionGroup as $permission)
                                            @if ($permission->status == '1')
                                                <div class="col-md-4">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="permissions[]" 
                                                               value="{{ $permission->name }}" 
                                                               id="create-perm-{{ $permission->id }}">
                                                        <label class="form-check-label" for="create-perm-{{ $permission->id }}">
                                                            {{ $permission->mock }}
                                                        </label>
                                                    </div>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Jabatan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection