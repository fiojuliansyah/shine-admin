<div class="dropdown">
    <button class="btn btn-primary btn-sm rounded-pill" type="button" id="dropdownMenuButton-{{ $row->id }}" data-bs-toggle="dropdown">
        Actions
    </button>
    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton-{{ $row->id }}">
        <li>
            <button class="dropdown-item" data-bs-toggle="modal" data-bs-target="#editModal-{{ $row->id }}">
                Edit
            </button>
        </li>
        <li>
            <button class="dropdown-item text-danger" data-bs-toggle="modal" data-bs-target="#deleteModal-{{ $row->id }}">
                Hapus
            </button>
        </li>
    </ul>
</div>

<div class="modal fade" id="editModal-{{ $row->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('attendances.update', $row->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Edit Absensi - {{ $row->user->name ?? '' }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-start">
                    <div class="mb-3">
                        <label class="form-label">Tanggal</label>
                        <input type="date" class="form-control" name="date" value="{{ $row->date ? $row->date->format('Y-m-d') : '' }}" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Latlong</label>
                        <input type="text" class="form-control" name="latlong" value="{{ $row->latlong }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Tipe</label>
                        <select class="form-select" name="type">
                            @foreach(['off', 'late', 'alpha', 'regular', 'leave', 'permit', 'minute'] as $type)
                                <option value="{{ $type }}" {{ $row->type == $type ? 'selected' : '' }}>{{ ucfirst($type) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Site</label>
                        <select class="form-select" name="site_id">
                            @foreach($sites as $site)
                                <option value="{{ $site->id }}" {{ $row->site_id == $site->id ? 'selected' : '' }}>{{ $site->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Clock IN</label>
                            <input type="time" class="form-control" name="clock_in" value="{{ $row->clock_in ? $row->clock_in->format('H:i') : '' }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Clock OUT</label>
                            <input type="time" class="form-control" name="clock_out" value="{{ $row->clock_out ? $row->clock_out->format('H:i') : '' }}">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light me-2" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>