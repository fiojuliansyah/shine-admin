<div class="dropdown">
    <button class="btn btn-primary btn-sm rounded-pill" type="button" id="dropdownMenuButton-{{ $row->id }}"
        data-bs-toggle="dropdown" aria-expanded="false">
        Actions
    </button>
    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton-{{ $row->id }}">
        <!-- Edit Link -->
        <li>
            <button class="dropdown-item text-primary" data-bs-toggle="modal"
                data-bs-target="#updateModal-{{ $row->id }}">
                Edit
            </button>
        </li>

        <!-- Delete Button -->
        <li>
            <button class="dropdown-item text-danger" data-bs-toggle="modal"
                data-bs-target="#deleteModal-{{ $row->id }}">
                Hapus
            </button>
        </li>
    </ul>
</div>

<div class="modal fade" id="deleteModal-{{ $row->id }}" tabindex="-1"
    aria-labelledby="deleteModalLabel-{{ $row->id }}" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('jobdesk-patrolls.delete', $row->id) }}" method="POST">
                @csrf
                @method('DELETE')
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel-{{ $row->id }}">Hapus Jobdeks</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete jobdesks patroll <strong>{{ $row->name }}</strong>?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light me-2" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">Hapus</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="updateModal-{{ $row->id }}" tabindex="-1"
    aria-labelledby="updateModalLabel-{{ $row->id }}" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('jobdesk-patrolls.update', $row->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title" id="updateModalLabel-{{ $row->id }}">Edit Jobdek Patrolls</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="site_id" value="{{ $id }}">
                    <div class="form-group mb-3">
                        <label for="work_type">Tipe Pekerjaan</label>
                        <select name="work_type" id="work_type" class="form-control">
                            <option value="daily" {{ $row->work_type == 'daily' ? 'selected' : '' }}>Daily</option>
                            <option value="weekly" {{ $row->work_type == 'weekly' ? 'selected' : '' }}>Weekly</option>
                            <option value="monthly" {{ $row->work_type == 'monthly' ? 'selected' : '' }}>Monthly</option>
                        </select>
                    </div>
                    <div class="form-group mb-3" id="name_field">
                        <label for="name">Nama Jobdesk Patroll</label>
                        <input type="text" name="name" id="name" class="form-control"
                            placeholder="Enter Jobdesk Name" value="{{ $row->name }}">
                    </div>
                    <div class="dropdown mb-3">
                        <label for="floor_id">Floor/Point area</label>
                        <select name="floor_id" id="floor_id" class="form-select select2">
                            @foreach ($floors as $floor)
                                <option value="{{ $floor->id }}" {{ $row->floor_id == $floor->id ? 'selected' : '' }}>
                                    {{ $floor->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group mb-3">
                        <input type="hidden" name="service_type" value="patroll">
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light me-2" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Edit</button>
                </div>
            </form>
        </div>
    </div>
</div>
