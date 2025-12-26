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
            <form action="{{ route('statuses.update', $row->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel-{{ $row->id }}">Edit Tingkatan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <!-- Pilih Warna -->
                        <div class="col">
                            <div class="mb-3">
                                <label for="color" class="col-form-label">Pilih Warna</label>
                                <select name="color" class="form-control" id="color">
                                    <option value="primary" {{ $row->color == 'primary' ? 'selected' : '' }}>Primary</option>
                                    <option value="success" {{ $row->color == 'success' ? 'selected' : '' }}>Success</option>
                                    <option value="warning" {{ $row->color == 'warning' ? 'selected' : '' }}>Warning</option>
                                    <option value="danger" {{ $row->color == 'danger' ? 'selected' : '' }}>Danger</option>
                                    <option value="info" {{ $row->color == 'info' ? 'selected' : '' }}>Info</option>
                                    <option value="secondary" {{ $row->color == 'secondary' ? 'selected' : '' }}>Secondary</option>
                                </select>
                            </div>
                        </div>
                        <!-- Nama -->
                        <div class="col">
                            <div class="mb-3">
                                <label for="name" class="col-form-label">Nama</label>
                                <input type="text" class="form-control" id="name" name="name" value="{{ $row->name }}" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Is Request Approval -->
                        <div class="col">
                            <div class="mb-3">
                                <label for="is_approve" class="col-form-label">Apakah perlu Approval ? </label>
                                <select name="is_approve" class="form-control" id="is_approve">
                                    <option value="no" {{ $row->is_approve == 'no' ? 'selected' : '' }}>No</option>
                                    <option value="yes" {{ $row->is_approve == 'yes' ? 'selected' : '' }}>Yes</option>
                                </select>
                            </div>
                        </div>

                        <!-- Is Bulk Letter -->
                        <div class="col">
                            <div class="mb-3">
                                <label for="process_to_offering" class="col-form-label">Apakah ini PKWT ?</label>
                                <select name="process_to_offering" class="form-control" id="process_to_offering">
                                    <option value="no" {{ $row->is_bulk_letter == 'no' ? 'selected' : '' }}>No</option>
                                    <option value="yes" {{ $row->is_bulk_letter == 'yes' ? 'selected' : '' }}>Yes</option>
                                </select>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light me-2" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save changes</button>
                </div>
            </form>
        </div>
    </div>
</div>


<div class="modal fade" id="deleteModal-{{ $row->id }}" tabindex="-1" aria-labelledby="deleteModalLabel-{{ $row->id }}" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('statuses.destroy', $row->id) }}" method="POST">
                @csrf
                @method('DELETE')
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel-{{ $row->id }}">Hapus Tingkat</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Apakah Anda yakin ingin menghapus tingkatan <strong>{{ $row->name }}</strong>?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light me-2" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Hapus</button>
                </div>
            </form>
        </div>
    </div>
</div>