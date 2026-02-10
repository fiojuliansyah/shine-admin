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
    <div class="modal-dialog modal-lg"> {{-- pakai modal-lg biar lebih lega --}}
        <div class="modal-content">
            <form id="formupdateOrCreate-{{ $row->id }}" action="{{ route('patrollReport.update', $row->id) }}"
                method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel-{{ $row->id }}">Edit Laporan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    {{-- Name --}}
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" value="{{ old('name', $row->name) }}"
                            class="form-control @error('name') is-invalid @enderror">
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row mb-3">
                        <div class="col-4">
                            <label class="form-label">Site</label>
                            <select name="site_id" id="site" class="form-select select2">
                                @foreach ($sites as $site)
                                    <option value="{{ $site->id }}"
                                        {{ $row->site_id == $site->id ? 'selected' : '' }}>
                                        {{ $site->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-4">
                            <label class="form-label">Floor</label>
                            <select name="floor_id" id="floor_id" class="form-select select2">
                                @foreach ($floors as $floor)
                                    <option value="{{ $floor->id }}">
                                        {{ $floor->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-4">
                            <label class="form-label">Pegawai</label>
                            <select name="user_id" id="user" class="form-select select2">
                                @foreach ($users as $user)
                                    <option value="{{ $user->id }}"
                                        {{ $row->user_id == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Description --}}
                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="3">{{ old('description', $row->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- File Upload --}}
                    <div class="mb-3">
                        <label class="form-label">Upload Bukti</label>
                        <input type="file" name="image" id="file-upload-{{ $row->id }}"
                            class="form-control @error('image') is-invalid @enderror" accept="image/*">
                        @error('image')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="mt-2">
                            <img id="preview-{{ $row->id }}"
                                src="{{ $row->image_url ?? '/mobile/images/empty.png' }}" class="img-fluid rounded"
                                style="max-height:150px;">
                        </div>
                    </div>
                </div>

                <div class="modal-footer gap-2">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Save changes
                    </button>
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
            <form action="{{ route('patrollReport.destroy', $row->id) }}" method="POST">
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

<script>
    document.getElementById("file-upload-{{ $row->id }}").addEventListener("change", function(e) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById("preview-{{ $row->id }}").src = e.target.result;
        }
        reader.readAsDataURL(this.files[0]);
    });
</script>
