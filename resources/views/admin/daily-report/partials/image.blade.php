<button class="btn btn-sm btn-dark" data-bs-toggle="modal" data-bs-target="#imageModal-{{ $row->id }}">
    Lihat
</button>

<!-- image Modal -->
<div class="modal fade" id="imageModal-{{ $row->id }}" tabindex="-1" aria-labelledby="editModalLabel-{{ $row->id }}" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Foto Mengerjakan Tugas</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <div class="row">
                            <div class="col text-center">
                                <h6>Foto Sebelum Tugas</h6>
                                <img src="{{ $image_before_url }}" width="100" />
                            </div>
                            <div class="col text-center">
                                <h6>Foto Progress</h6>
                                <img src="{{ $image_progress_url }}" width="100" />
                            </div>
                            <div class="col text-center">
                                <h6>Foto Setelah Tugas</h6>
                                <img src="{{ $image_after_url }}" width="100" />
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </form>
        </div>
    </div>
</div>
