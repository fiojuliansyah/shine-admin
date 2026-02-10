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

<div class="modal fade" id="editModal-{{ $row->id }}" tabindex="-1" aria-labelledby="editModalLabel-{{ $row->id }}" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('careers.update', $row->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel-{{ $row->id }}">Edit Career</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-12 mb-3">
                            <label for="company_id-{{ $row->id }}" class="col-form-label">Perusahaan</label>
                            <select class="form-control" id="company_id-{{ $row->id }}" name="company_id">
                                @foreach ($companies as $company)
                                    <option value="{{ $company->id }}" {{ $row->company_id == $company->id ? 'selected' : '' }}>
                                        {{ $company->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 mb-3">
                            <label for="name-{{ $row->id }}" class="col-form-label">Nama Lowongan</label>
                            <input type="text" class="form-control" id="name-{{ $row->id }}" name="name" value="{{ $row->name }}" required>
                        </div>
                        <div class="col-12 mb-3">
                            <label for="description" class="col-form-label">Deskripsi</label>
                            <textarea class="form-control" id="description-{{ $row->id }}" name="description" rows="5">{{ $row->description }}</textarea>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="department-{{ $row->id }}" class="col-form-label">Department</label>
                            <input type="text" class="form-control" id="department-{{ $row->id }}" name="department" value="{{ $row->department }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="location-{{ $row->id }}" class="col-form-label">Lokasi</label>
                            <input type="text" class="form-control" id="location-{{ $row->id }}" name="location" value="{{ $row->location }}">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="workfunction-{{ $row->id }}" class="col-form-label">Fungsi Pekerjaan</label>
                            <input type="text" class="form-control" id="workfunction-{{ $row->id }}" name="workfunction" value="{{ $row->workfunction }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="experience-{{ $row->id }}" class="col-form-label">Pengalaman</label>
                            <input type="text" class="form-control" id="experience-{{ $row->id }}" name="experience" value="{{ $row->experience }}">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="graduate-{{ $row->id }}" class="col-form-label">Lulusan</label>
                            <input type="text" class="form-control" id="graduate-{{ $row->id }}" name="graduate" value="{{ $row->graduate }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="major-{{ $row->id }}" class="col-form-label">Jurusan</label>
                            <input type="text" class="form-control" id="major-{{ $row->id }}" name="major" value="{{ $row->major }}">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="candidate-{{ $row->id }}" class="col-form-label">Berapa Pelamar?</label>
                            <input type="text" class="form-control" id="candidate-{{ $row->id }}" name="candidate" value="{{ $row->candidate }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="until_date-{{ $row->id }}" class="col-form-label">Sampai tanggal?</label>
                            <input type="date" class="form-control" id="until_date-{{ $row->id }}" name="until_date" value="{{ $row->until_date }}">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="salary-{{ $row->id }}" class="col-form-label">Gaji</label>
                            <input type="text" class="form-control" id="salary-{{ $row->id }}" name="salary" value="{{ $row->salary }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="col-form-label">Status</label>
                            <select class="form-control" name="status">
                                <option value="unhide" {{ $row->status == 'unhide' ? 'selected' : '' }}>Unhide</option>
                                <option value="hide" {{ $row->status == 'hide' ? 'selected' : '' }}>Hide</option>
                            </select>
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
            <form action="{{ route('careers.destroy', $row->id) }}" method="POST">
                @csrf
                @method('DELETE')
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel-{{ $row->id }}">Hapus Career</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Apakah Anda yakin ingin menghapus career <strong>{{ $row->name }}</strong>?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light me-2" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">Hapus</button>
                </div>
            </form>
        </div>
    </div>
</div>