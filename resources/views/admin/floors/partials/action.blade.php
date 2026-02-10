<div class="dropdown">
    <button class="btn btn-sm btn-primary rounded-pill" type="button" data-bs-toggle="dropdown" aria-expanded="false">
        Action
    </button>
    <ul class="dropdown-menu">
        <li><button class="dropdown-item" data-bs-toggle="modal" data-bs-target="#update-{{ $row->id }}">Edit</button></li>
        <li><button class="dropdown-item text-danger" data-bs-toggle="modal" data-bs-target="#delete-{{ $row->id }}">Hapus</button></li>
    </ul>
</div>

<!-- Modal edit-->
<div class="modal fade" id="update-{{ $row->id }}" tabindex="-1" aria-labelledby="updateLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('floors.update', $row->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="modal-header">
                    <h5 class="modal-title" id="updateLabel-">Tambah Floor</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <label for="site">Site</label>
                    <select class="form-select" name="site_id" id="site" aria-label="edit site">
                        @foreach ($sites as $site)
                            <option value ="{{ $site->id }}" {{ $site->id == $row->site->id ? 'selected' : '' }}>{{ $site->name }}</option>
                        @endforeach
                    </select>

                    <div class="mb-3">
                        <label for="name-" class="form-label">Nama Floor</label>
                        <input type="text" id="name" name="name" value="{{ $row->name }}"
                            class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Deskripsi</label>
                        <textarea name="description" id="description" class="form-control" rows="3">{{ $row->description }}</textarea>
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

<!-- Modal delete-->
<div class="modal fade" id="delete-{{ $row->id }}" tabindex="-1" aria-labelledby="deleteLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title" id="deleteLabel">Hapus Floor/Lantai</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Apakah Anda yakin ingin menghapus lantai {{ $row->name }}?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <form action="{{ route('floors.destroy', $row->id) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="sumbit" class="btn btn-primary">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>
