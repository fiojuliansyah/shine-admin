<button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#imageModal-{{ $row->id }}">
    Lihat
</button>

<!-- image Modal -->
<div class="modal fade" id="imageModal-{{ $row->id }}" tabindex="-1" aria-labelledby="editModalLabel-{{ $row->id }}" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Foto Kehadiran</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <div class="row">
                            <div class="col text-center">
                                <h6>Foto IN</h6>
                                <img src="{{ $imagein }}" width="50" />
                            </div>
                            <div class="col text-center">
                                <h6>Foto OUT</h6>
                                <img src="{{ $imageout }}" width="50" />
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
