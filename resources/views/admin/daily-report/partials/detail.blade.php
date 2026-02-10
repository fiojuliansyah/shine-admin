<!-- Button Trigger Modal -->
<button class="btn btn-sm btn-dark" data-bs-toggle="modal" data-bs-target="#detailModal-{{ $row->id }}">
    Detail
</button>

<!-- Detail Modal -->
<div class="modal fade" id="detailModal-{{ $row->id }}" tabindex="-1" aria-labelledby="detailModalLabel-{{ $row->id }}" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Task Planner</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Informasi Tugas -->
                <table class="table table-borderless">
                    <tr>
                        <th>Tanggal</th>
                        <td>{{ $row->date }}</td>
                    </tr>
                    <tr>
                        <th>Pegawai</th>
                        <td>{{ $row->user->name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Site</th>
                        <td>{{ $row->site->name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Status</th>
                        <td>
                            @if($row->is_worked === 'worked')
                                <span class="badge bg-success">Worked</span>
                            @else
                                <span class="badge bg-danger">Not Worked</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Mulai - Selesai</th>
                        <td>{{ $start_time }} - {{ $end_time}}</td>
                    </tr>
                    <tr>
                        <th>Keterangan</th>
                        <td>{{ $row->progress_description ?? '-' }}</td>
                    </tr>
                </table>

                <!-- Foto Sebelum & Sesudah -->
                <div class="row mt-3 text-center">
                    <div class="col">
                        <h6>Foto Sebelum Tugas</h6>
                        <img src="{{ $row->image_before_url ?? '' }}" class="img-fluid" style="max-height:150px;">
                    </div>
                    <div class="col">
                        <h6>Foto Progress</h6>
                        <img src="{{ $row->image_progress_url ?? '' }}" class="img-fluid" style="max-height:150px;">
                    </div>
                    <div class="col">
                        <h6>Foto Setelah Tugas</h6>
                        <img src="{{ $row->image_after_url ?? '' }}" class="img-fluid" style="max-height:150px;">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
