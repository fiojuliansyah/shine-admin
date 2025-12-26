<div class="dropdown">
    <button class="btn btn-primary btn-sm rounded-pill" type="button" id="dropdownMenuButton-{{ $row->id }}"
        data-bs-toggle="dropdown" aria-expanded="false">
        Actions
    </button>
    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton-{{ $row->id }}">
        {{-- <li>
            <button class="dropdown-item" data-bs-toggle="modal" data-bs-target="#editModal-{{ $row->id }}">
                Edit
            </button>
        </li> --}}
        <li>
            <button class="dropdown-item text-danger" data-bs-toggle="modal"
                data-bs-target="#deleteModal-{{ $row->id }}">
                Hapus
            </button>
        </li>
    </ul>
</div>

<!-- Edit Modal -->
{{-- <div class="modal fade" id="editModal-{{ $row->id }}" tabindex="-1" aria-labelledby="editModalLabel-{{ $row->id }}" aria-hidden="true">
<div class="modal-dialog">
<div class="modal-content">
<form action="{{ route('overtimes.update', $row->id) }}" method="POST">
@csrf
@method('PUT')
<div class="modal-header">
<h5 class="modal-title" id="editModalLabel-{{ $row->id }}">Edit Overtime</h5>
<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body">
<div class="mb-3">
    <label class="col-form-label">Attendance ID</label>
    <input type="text" class="form-control" name="attendance_id" value="{{ $row->attendance_id }}" required>
</div>
<div class="mb-3">
    <label class="col-form-label">Clock In</label>
    <input type="time" class="form-control" name="clock_in" value="{{ $row->clock_in }}" required>
</div>
<div class="mb-3">
    <label class="col-form-label">Clock Out</label>
    <input type="time" class="form-control" name="clock_out" value="{{ $row->clock_out }}" required>
</div>
<div class="mb-3">
    <label class="col-form-label">Reason</label>
    <textarea class="form-control" name="reason">{{ $row->reason }}</textarea>
</div>
<div class="mb-3">
    <label class="col-form-label">Backup ID</label>
    <input type="text" class="form-control" name="backup_id" value="{{ $row->backup_id }}">
</div>
<div class="mb-3">
    <label class="col-form-label">Demand</label>
    <input type="text" class="form-control" name="demand" value="{{ $row->demand }}">
</div>
</div>
<div class="modal-footer">
<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
<button type="submit" class="btn btn-primary">Save changes</button>
</div>
</form>
</div>
</div>
</div> --}}

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal-{{ $row->id }}" tabindex="-1"
    aria-labelledby="deleteModalLabel-{{ $row->id }}" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('overtimes.destroy', $row->id) }}" method="POST">
                @csrf
                @method('DELETE')
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel-{{ $row->id }}">Hapus Overtime</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Apakah Anda yakin ingin menghapus overtime ini?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Hapus</button>
                </div>
            </form>
        </div>
    </div>
</div>
