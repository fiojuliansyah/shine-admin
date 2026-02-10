<div class="btn-toolbar p-3" role="toolbar">
    <button type="button" class="btn btn-primary me-2 mb-2 mb-sm-0 btn-block waves-effect waves-light"
        data-bs-toggle="modal" data-bs-target="#importModal">
        <i class="fas fa-cloud-upload-alt"></i>
        Import
    </button>
    <div class="btn-group me-2 mb-2 mb-sm-0">
        <button type="button"
            class="btn btn-primary waves-light waves-effect dropdown-toggle"
            data-bs-toggle="dropdown" aria-expanded="false">
            <i class="fa fa-tag"></i> <i class="mdi mdi-chevron-down ms-1"></i>
        </button>
        <div class="dropdown-menu">
            <a class="dropdown-item" href="#" class="btn btn-primary btn-block waves-effect waves-light"
            data-bs-toggle="modal" data-bs-target="#signaturemodal">Buat Tanda Tangan Digital</a>
        </div>
    </div>
    <div class="btn-group me-2 mb-2 mb-sm-0">
        <button type="button"
            class="btn btn-primary waves-light waves-effect dropdown-toggle"
            data-bs-toggle="dropdown" aria-expanded="false">
            <i class="fa fa-folder"></i> <i class="mdi mdi-chevron-down ms-1"></i>
        </button>
        <div class="dropdown-menu">
            <button type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#approvemodal">
                Bulk Approve
            </button>
            <button type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#deletemodal">
                Bulk Delete
            </button>
        </div>
    </div>
</div>