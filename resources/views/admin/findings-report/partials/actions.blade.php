<div class="dropdown">
    <button class="btn btn-secondary text-white btn-sm dropdown-toggle" type="button"
        id="dropdownMenuButton-{{ $row->id }}" data-bs-toggle="dropdown" aria-expanded="false">
        Actions
    </button>
    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton-{{ $row->id }}">
        <li>
            <!-- Edit Action -->
            <button class="dropdown-item" data-bs-toggle="modal" data-bs-target="#editModal-{{ $row->id }}">
                Edit
            </button>
        </li>
        <li>
            <!-- Delete Action -->
            <button class="dropdown-item text-danger" data-bs-toggle="modal"
                data-bs-target="#deleteModal-{{ $row->id }}">
                Hapus
            </button>
        </li>
    </ul>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal-{{ $row->id }}" tabindex="-1"
    aria-labelledby="editModalLabel-{{ $row->id }}" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('findingReport.update', $row->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel-{{ $row->id }}">Edit Laporan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="col-form-label">Status</label>
                        <select class="form-select" name="status">
                            <option value="pending" {{ $row->status == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="solved" {{ $row->status == 'solved' ? 'selected' : '' }}>Solved</option>
                        </select>
                        @error('status')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="col-form-label">Tipe Laporan</label>
                        <select name="type" class="form-select">
                            <option selected disabled>Tipe Laporan Temuan</option>
                            <option value="low" {{ $row->type == 'low' ? 'selected' : '' }}>Low</option>
                            <option value="medium" {{ $row->type == 'medium' ? 'selected' : '' }}>Medium</option>
                            <option value="high" {{ $row->type == 'high' ? 'selected' : '' }}>High</option>
                        </select>
                        @error('type')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="col-form-label">Pegawai</label>
                        <select class="form-select" name="user_id" required>
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}"
                                    {{ $row->user_id == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('user_id')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="col-form-label">Site</label>
                        <select class="form-select" name="site_id" required>
                            @foreach ($sites as $site)
                                <option value="{{ $site->id }}"
                                    {{ $row->site_id == $site->id ? 'selected' : '' }}>
                                    {{ $site->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('site_id')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="col-form-label">Lokasi</label>
                        <input type="text" class="form-control" name="location" value="{{ $row->location }}">
                        @error('location')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="col-form-label">Deskripsi</label>
                        <textarea class="form-control" name="description">{{ $row->description }}</textarea>
                        @error('description')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="col-form-label">Tindakan Langsung</label>
                        <input type="text" class="form-control" name="direct_action"
                            value="{{ $row->direct_action }}">
                        @error('direct_action')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>

                </div>
                <div class="modal-footer gap-2">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal-{{ $row->id }}" tabindex="-1"
    aria-labelledby="deleteModalLabel-{{ $row->id }}" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('findingReport.destroy', $row->id) }}" method="POST">
                @csrf
                @method('DELETE')
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel-{{ $row->id }}">Hapus Laporan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Apakah Anda yakin ingin menghapus laporan ini?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Hapus</button>
                </div>
            </form>
        </div>
    </div>
</div>
