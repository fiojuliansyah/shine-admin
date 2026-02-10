<!-- Button trigger modal -->
<div class="dropdown">
    <button class="btn btn-primary btn-sm rounded-pill" type="button" data-bs-toggle="modal"
        data-bs-target="#addfloor-{{ $row->id }}">
        Add Floor
    </button>
</div>

<!-- Add Floor Modal -->
<div class="modal fade" id="addfloor-{{ $row->id }}" tabindex="-1" aria-labelledby="addFloorLabel-{{ $row->id }}"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('floors.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="addFloorLabel-{{ $row->id }}">Tambah Floor</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <input type="text" name="site_id" value="{{ $row->id }}" class="form-control" hidden>
                    </div>

                    <div class="mb-3">
                        <label for="name-{{ $row->id }}" class="form-label">Nama Floor</label>
                        <input type="text" name="name" id="name-{{ $row->id }}" class="form-control"
                            value="{{ old('name') }}" required>
                    </div>

                    <div class="mb-3">
                        <label for="description-{{ $row->id }}" class="form-label">Deskripsi</label>
                        <textarea name="description" id="description-{{ $row->id }}" class="form-control" rows="3">{{ old('description') }}</textarea>
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
