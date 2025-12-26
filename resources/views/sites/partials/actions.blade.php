<div class="dropdown">
    <button class="btn btn-primary btn-sm rounded-pill" type="button" id="dropdownMenuButton-{{ $row->id }}" data-bs-toggle="dropdown">
        Actions
    </button>
    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton-{{ $row->id }}">
        <li>
            <!-- Edit Button -->
            <button class="dropdown-item" data-bs-toggle="modal" data-bs-target="#editModal-{{ $row->id }}">Edit</button>
        </li>
        <li>
            <!-- Delete Button -->
            <button class="dropdown-item text-danger" data-bs-toggle="modal" data-bs-target="#deleteModal-{{ $row->id }}">Hapus</button>
        </li>
    </ul>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal-{{ $row->id }}" tabindex="-1" aria-labelledby="editModalLabel-{{ $row->id }}" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('sites.update', $row->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel-{{ $row->id }}">Edit Site</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Form Fields for Edit -->
                    <div class="mb-3">
                        <label for="company_id-{{ $row->id }}" class="form-label">Perusahaan</label>
                        <select class="form-select" name="company_id" id="company_id-{{ $row->id }}">
                            @foreach ($companies as $company)
                                <option value="{{ $company->id }}" {{ $company->id == $row->company_id ? 'selected' : '' }}>
                                    {{ $company->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="name-{{ $row->id }}" class="form-label">Nama</label>
                        <input type="text" class="form-control" id="name-{{ $row->id }}" name="name" value="{{ $row->name }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="description-{{ $row->id }}" class="form-label">Deskripsi</label>
                        <input type="text" class="form-control" id="description-{{ $row->id }}" name="description" value="{{ $row->description }}">
                    </div>
                    <div class="row">
                        <div class="col">
                            <div class="mb-3">
                                <label for="lat" class="col-form-label">Lat</label>
                                <input type="text" class="form-control" id="lat" name="lat" value="{{ $row->lat }}">
                            </div>
                        </div>
                        <div class="col">
                            <div class="mb-3">
                                <label for="long" class="col-form-label">Long</label>
                                <input type="text" class="form-control" id="long" name="long" value="{{ $row->long }}">
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="radius" class="col-form-label">Radius</label>
                        <input type="text" class="form-control" id="radius" name="radius" value="{{ $row->radius }}">
                    </div>
                    <div class="row">
                        <div class="col">
                            <div class="mb-3">
                                <label for="client_name" class="col-form-label">Nama Client</label>
                                <input type="text" class="form-control" id="client_name" name="client_name" value="{{ $row->client_name }}">
                            </div>
                        </div>
                        <div class="col">
                            <div class="mb-3">
                                <label for="client_phone" class="col-form-label">Phone Client</label>
                                <input type="text" class="form-control" id="client_phone" name="client_phone" value="{{ $row->client_phone }}">
                            </div>
                        </div>
                        <div class="col">
                            <div class="mb-3">
                                <label for="client_email" class="col-form-label">Email Client</label>
                                <input type="email" class="form-control" id="client_email" name="client_email" value="{{ $row->client_email }}">
                            </div>
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

<div class="modal fade" id="deleteModal-{{ $row->id }}" tabindex="-1" aria-labelledby="deleteModalLabel-{{ $row->id }}" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('sites.destroy', $row->id) }}" method="POST">
                @csrf
                @method('DELETE')
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel-{{ $row->id }}">Hapus Site/Project</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Apakah Anda yakin ingin menghapus perusahaan <strong>{{ $row->name }}</strong>?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light me-2" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Hapus</button>
                </div>
            </form>
        </div>
    </div>
</div>