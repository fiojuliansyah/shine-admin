<div class="dropdown">
    <button class="btn btn-primary btn-sm rounded-pill" type="button" id="dropdownMenuButton-{{ $row->id }}" data-bs-toggle="dropdown" aria-expanded="false">
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
            <button class="dropdown-item text-danger" data-bs-toggle="modal" data-bs-target="#deleteModal-{{ $row->id }}">
                Hapus
            </button>
        </li>
    </ul>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal-{{ $row->id }}" tabindex="-1" aria-labelledby="editModalLabel-{{ $row->id }}" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('permits.update', $row->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel-{{ $row->id }}">Edit Permit</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="col-form-label">Start Date</label>
                        <input type="date" class="form-control" name="start_date" value="{{ $row->start_date->format('Y-m-d') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="col-form-label">End Date</label>
                        <input type="date" class="form-control" name="end_date" value="{{ $row->end_date->format('Y-m-d') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="col-form-label">Reason</label>
                        <textarea class="form-control" name="reason">{{ $row->reason }}</textarea>
                    </div>
                    <div class="mb-3">
                        <label class="col-form-label">Is Paid</label>
                        <select class="form-select" name="is_paid">
                            <option value="1" {{ $row->is_paid == 1 ? 'selected' : '' }}>Yes</option>
                            <option value="0" {{ $row->is_paid == 0 ? 'selected' : '' }}>No</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="col-form-label">Contact</label>
                        <input type="text" class="form-control" name="contact" value="{{ $row->contact }}">
                    </div>
                    <div class="mb-3">
                        <label class="col-form-label">Status</label>
                        <select class="form-select" name="status" required>
                            <option value="pending" {{ $row->status == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="approved" {{ $row->status == 'approved' ? 'selected' : '' }}>Approved</option>
                            <option value="rejected" {{ $row->status == 'rejected' ? 'selected' : '' }}>Rejected</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal-{{ $row->id }}" tabindex="-1" aria-labelledby="deleteModalLabel-{{ $row->id }}" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('permits.destroy', $row->id) }}" method="POST">
                @csrf
                @method('DELETE')
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel-{{ $row->id }}">Hapus Permit</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Apakah Anda yakin ingin menghapus permit ini?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Hapus</button>
                </div>
            </form>
        </div>
    </div>
</div>