<div class="dropdown">
    <button class="btn btn-primary btn-sm rounded-pill" type="button" id="dropdownMenuButton-{{ $row->id }}" data-bs-toggle="dropdown">
        Actions
    </button>
    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton-{{ $row->id }}">
        <li>
            <button class="dropdown-item" data-bs-toggle="modal" data-bs-target="#editModal-{{ $row->id }}">Edit</button>
        </li>
        <li>
            <button class="dropdown-item text-danger" data-bs-toggle="modal" data-bs-target="#deleteModal-{{ $row->id }}">Hapus</button>
        </li>
    </ul>
</div>

<div class="modal fade" id="editModal-{{ $row->id }}" tabindex="-1" aria-labelledby="editModalLabel-{{ $row->id }}" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('loans.update', $row->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel-{{ $row->id }}">Edit Loan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <div class="mb-3">
                        <label class="form-label">User</label>
                        <select class="form-select" name="user_id">
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}" {{ $user->id == $row->user_id ? 'selected' : '' }}>
                                    {{ $user->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Jumlah Pinjaman</label>
                        <input type="number" class="form-control" name="amount" value="{{ $row->amount }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Bunga (%)</label>
                        <input type="number" class="form-control" name="interest_rate" value="{{ $row->interest_rate }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Tenor (bulan)</label>
                        <input type="number" class="form-control" name="tenor" value="{{ $row->tenor }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Saldo Tersisa</label>
                        <input type="number" class="form-control" name="remaining_balance" value="{{ $row->remaining_balance }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status">
                            <option value="ongoing"  @selected($row->status == 'ongoing')>Ongoing</option>
                            <option value="paid"     @selected($row->status == 'paid')>Paid</option>
                            <option value="overdue"  @selected($row->status == 'overdue')>Overdue</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Tanggal Mulai</label>
                        <input type="date" class="form-control" name="start_date" value="{{ $row->start_date }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Tanggal Jatuh Tempo</label>
                        <input type="date" class="form-control" name="due_date" value="{{ $row->due_date }}">
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>

            </form>
        </div>
    </div>
</div>


<div class="modal fade" id="deleteModal-{{ $row->id }}" tabindex="-1" aria-labelledby="deleteModalLabel-{{ $row->id }}" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">

            <form action="{{ route('loans.destroy', $row->id) }}" method="POST">
                @csrf
                @method('DELETE')

                <div class="modal-header">
                    <h5 class="modal-title">Hapus Loan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <p>Apakah Anda yakin ingin menghapus pinjaman <strong>ID #{{ $row->id }}</strong>?</p>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">Hapus</button>
                </div>

            </form>

        </div>
    </div>
</div>