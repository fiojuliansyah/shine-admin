<button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#resumeModal-{{ $row->id }}">
    Lihat
</button>

<!-- Resume Modal -->
<div class="modal fade bs-example-modal-l" id="resumeModal-{{ $row->id }}" tabindex="-1" aria-labelledby="editModalLabel-{{ $row->id }}" aria-hidden="true">
    <div class="modal-dialog modal-l">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Resume</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <img src="{{ $row->user->profile['avatar_url'] ?? '/assets/media/avatars/blank.png' }}" alt=""
                class="mb-3 text-center" width="200px">
                
                <h6 class="mt-3"><strong>Profil Umum</strong></h6>
                <div class="row">
                    <div class="col">
                        <label>Nama</label>
                        <input type="text" class="form-control mb-3" value="{{ $row->user->name ?? 'N/A' }}" disabled>
                        <label>Email</label>
                        <input type="text" class="form-control mb-3" value="{{ $row->user->email ?? 'N/A' }}" disabled>
                        <label>No Telepon</label>
                        <input type="text" class="form-control" value="{{ $row->user->phone ?? 'N/A' }}" disabled>
                    </div>
                    <div class="col">
                        <label>Jenis Kelamin</label>
                        <input type="text" class="form-control mb-3" value="{{ $row->user->profile['gender'] ?? 'N/A' }}" disabled>
                        <label>Tempat Tanggal lahir</label>
                        <div class="row mb-3">
                            <div class="col">
                                <input type="text" class="form-control" value="{{ $row->user->profile['birth_place'] ?? 'N/A' }}" disabled>    
                            </div>
                            <div class="col">
                                <input type="text" class="form-control" value="{{ $row->user->profile['birth_date'] ?? 'N/A' }}" disabled> 
                            </div>
                        </div>
                        <label>Nama Ibu Kandung</label>
                        <input type="text" class="form-control mb-3" value="{{ $row->user->profile['mother_name'] ?? 'N/A' }}" disabled>
                    </div>
                </div>
                <label class="mt-3">Alamat</label>
                <input type="text" class="form-control mb-3" value="{{ $row->user->profile['address'] ?? 'N/A' }}" disabled>
                <label>NPWP</label>
                <input type="text" class="form-control mb-3" value="{{ $row->user->profile['npwp_number'] ?? 'N/A' }}" disabled>
                <label>Status Pernikahan</label>
                <input type="text" class="form-control mb-3" value="{{ $row->user->profile['marriage_status'] ?? 'N/A' }}" disabled>
                
                <h6 class="mt-3"><strong>Detail BANK</strong></h6>
                <label>Nama Rekening</label>
                <input type="text" class="form-control mb-3" value="{{ $row->user->profile['account_number'] ?? 'N/A' }}" disabled>
                <label>No Rekening</label>
                <input type="text" class="form-control mb-3" value="{{ $row->user->profile['account_name'] ?? 'N/A' }}" disabled>
                <label>Nama BANK</label>
                <input type="text" class="form-control" value="{{ $row->user->profile['bank_name'] ?? 'N/A' }}" disabled>
            
                <h6 class="mt-3"><strong>Detail Dokumen</strong></h6>
                @foreach ($documents as $doc)
                    <label>{{ $doc->name }}</label>
                    <p>{{ $doc->description }}</p>
                    <img src="{{ $doc->file_url }}" alt=""
                    class="text-center" width="200px">
                    <br>
                    <br>
                    <br>
                @endforeach
                
                <select class="form-select" onchange="event.preventDefault(); document.getElementById('status-input-{{ $row->id }}').value = this.value; document.getElementById('update-status-{{ $row->id }}').submit();">
                    <option disabled selected>Choose Status</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status->id }}" {{ $row->status_id == $status->id ? 'selected' : '' }}>{{ $status->name }}</option>
                    @endforeach
                </select>
                <form id="update-status-{{ $row->id }}" action="{{ route('update-status', $row->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="status_id" id="status-input-{{ $row->id }}">
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>