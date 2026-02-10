<div class="dropdown">
    <button class="btn btn-primary btn-sm rounded-pill" type="button" id="dropdownMenuButton-{{ $row->id }}"
        data-bs-toggle="dropdown">
        Actions
    </button>
    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton-{{ $row->id }}">
        <li>
            <!-- Edit Button -->
            <button class="dropdown-item" data-bs-toggle="modal" data-bs-target="#editModal-{{ $row->id }}">
                Edit
            </button>
        </li>
        <li>
            <!-- Delete Button -->
            <button class="dropdown-item text-danger" data-bs-toggle="modal"
                data-bs-target="#deleteModal-{{ $row->id }}">
                Hapus
            </button>
        </li>
    </ul>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal-{{ $row->id }}" tabindex="-1"
    aria-labelledby="deleteModalLabel-{{ $row->id }}" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('dailyReport.destroy', $row->id) }}" method="POST">
                @csrf
                @method('DELETE')
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel-{{ $row->id }}">Hapus Laporan?</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Apakah Anda yakin ingin menghapus Laporan?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Hapus</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal-{{ $row->id }}" tabindex="-1"
    aria-labelledby="editModalLabel-{{ $row->id }}" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('dailyReport.update', $row->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Edit Laporan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-2">
                        <label class="col-form-label">Tanggal</label>
                        <input type="date" class="form-control" name="date" value="{{ $row->date }}" required>
                    </div>
                    <div class="row">
                        <div class="col-4">
                            <div class="mb-2">
                                <label class="col-form-label">Pegawai</label>
                                <select class="form-select" name="user_id" data-placeholder="Select user">
                                    @foreach ($users as $user)
                                        <option value="{{ $user->id }}"
                                            {{ $row->user_id == $user->id ? 'selected' : '' }}>{{ $user->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="mb-2">
                                <label class="col-form-label">Site</label>
                                <select class="form-select" name="site_id" data-placeholder="Select site">
                                    @foreach ($sites as $site)
                                        <option value="{{ $site->id }}"
                                            {{ $row->site_id == $site->id ? 'selected' : '' }}>{{ $site->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="mb-2">
                                <label class="col-form-label">Status</label>
                                <select name="is_worked" class="form-control" required>
                                    <option value="worked" {{ $row->is_worked == 'worked' ? 'selected' : '' }}>Worked
                                    </option>
                                    <option value="not_worked" {{ $row->is_worked == 'not_worked' ? 'selected' : '' }}>
                                        Not
                                        Worked</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <div class="mb-2">
                                <label class="col-form-label">Foto Sebelum Tugas</label>
                                <input type="file" class="form-control" name="image_before" id="input_image_before">
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="mb-2">
                                <label class="col-form-label">Foto Setelah Tugas</label>
                                <input type="file" class="form-control" name="image_after" id="input_image_after">
                            </div>
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="col-form-label">Keterangan</label>
                        <textarea name="progress_description" style="resize: none; height:70px" class="form-control" required>{{ $row->progress_description }}</textarea>
                    </div>

                    <div class="d-flex justify-content-between w-100">
                        <div class="">
                            <label class="col-form-label text-center">Foto Lama</label>
                            <div class="d-flex gap-2">
                                <img src="{{ $row->image_before_url }}" alt="img lama before" class="img-fluid"
                                    style="max-width:100px">
                                <input type="text" name="image_before_public_id" hidden
                                    value="{{ $row->image_before_public_id }}">
                                <img src="{{ $row->image_after_url }}" alt="img lama before" class="img-fluid"
                                    style="max-width:100px">
                                <input type="text" name="image_after_public_id" hidden
                                    value="{{ $row->image_after_public_id }}">
                            </div>

                        </div>
                        <div class="">
                            <label class="col-form-label text-center">Image Preview</label>
                            <div class="d-flex gap-2">
                                <img id="image_prev_before" class="img-fluid" style="max-width:100px">
                                <img id="image_prev_after" class="img-fluid" style="max-width:100px">
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


<script>
    $(document).on('change', 'input[name="image_before"]', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(ev) {
                // cari modal terdekat biar ga salah img
                let modal = $(e.target).closest('.modal');
                modal.find('#image_prev_before').attr('src', ev.target.result);
            }
            reader.readAsDataURL(file);
        }
    });

    $(document).on('change', 'input[name="image_after"]', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(ev) {
                let modal = $(e.target).closest('.modal');
                modal.find('#image_prev_after').attr('src', ev.target.result);
            }
            reader.readAsDataURL(file);
        }
    });
</script>
