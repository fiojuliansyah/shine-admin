@extends('admin.layouts.main')

@section('content')
    <div class="page-wrapper">
        <div class="content">
            <!-- Breadcrumb -->
            <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
                <div class="my-auto mb-2">
                    @php
                        $folderName = $currentType === 'none'
                            ? 'Tanpa Tipe'
                            : ($currentType ? $currentType->name : null);
                    @endphp
                    <h2 class="mb-1">{{ $folderName ? 'Surat Terbit - ' . $folderName : 'Surat Terbit' }}</h2>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="{{ route('dashboard') }}"><i class="ti ti-smart-home"></i></a>
                            </li>
                            <li class="breadcrumb-item">HR</li>
                            <li class="breadcrumb-item">
                                <a href="{{ route('generates.folders') }}">Surat Terbit</a>
                            </li>
                            @if ($folderName)
                                <li class="breadcrumb-item active" aria-current="page">{{ $folderName }}</li>
                            @endif
                        </ol>
                    </nav>
                </div>
                <div class="d-flex align-items-center flex-wrap">
                    <a href="{{ route('generates.folders') }}" class="btn btn-outline-secondary me-2 mb-2">
                        <i class="ti ti-arrow-left me-1"></i> Kembali ke Folder
                    </a>
                    @include('admin.generates.partials.toolbar-mains')
                </div>
            </div>
            <!-- /Breadcrumb -->
            
            
            
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
                    <h5>List Surat</h5>
                    <form id="attendance-filter-form" action="{{ route('generates.index') }}" method="GET" class="d-flex my-xl-auto right-content align-items-center flex-wrap row-gap-3">
                        <!-- Site Filter -->
                        <div class="me-3">
                            <select name="site_id" id="siteFilter" class="form-control select2">
                                <option value="">Select Site</option>
                                @foreach($sites as $site)
                                    <option value="{{ $site->id }}" {{ isset($filters['site_id']) && $filters['site_id'] == $site->id ? 'selected' : '' }}>
                                        {{ $site->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
            
                        <!-- Type Filter -->
                        <div class="me-3">
                            <select name="type_id" id="typeFilter" class="form-control select2">
                                <option value="">Select Type</option>
                                <option value="none" {{ isset($filters['type_id']) && $filters['type_id'] === 'none' ? 'selected' : '' }}>Tanpa Tipe</option>
                                @foreach($types as $type)
                                    <option value="{{ $type->id }}" {{ isset($filters['type_id']) && $filters['type_id'] == $type->id ? 'selected' : '' }}>
                                        {{ $type->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <!-- Date Range Filters -->
                        <div class="me-3">
                            <div class="input-icon-end position-relative">
                                <input type="date" name="start_date" id="startDate" class="form-control" value="{{ $filters['start_date'] ?? '' }}" placeholder="Start Date">
                            </div>
                        </div>
                        <div class="me-3">
                            <div class="input-icon-end position-relative">
                                <input type="date" name="end_date" id="endDate" class="form-control" value="{{ $filters['end_date'] ?? '' }}" placeholder="End Date">
                            </div>
                        </div>
                        
                        <div class="mb-2">
                            <button type="button" id="filterButton" class="btn btn-primary me-2">
                                <i class="ti ti-filter me-1"></i> Filter
                            </button>
                            <a href="{{ route('generates.index', array_filter(['type_id' => $filters['type_id'] ?? null])) }}" class="btn btn-outline-secondary">
                                <i class="ti ti-refresh me-1"></i> Reset
                            </a>
                        </div>
                    </form>
                </div>
                <div class="card-body p-0">
                    <div class="custom-datatable-filter table-responsive">
                        <table class="table table-bordered data-table" style="font-size: 12px; width: 100%;">
                            <thead>
                                <tr>
                                    <th style="text-align: center; vertical-align: middle; width: 40px;">
                                        <input type="checkbox" id="select-all" class="form-check-input">
                                    </th>
                                    <th style="text-align: center; vertical-align: middle; width: 120px;">Tanggal Dibuat</th>
                                    <th style="text-align: center; vertical-align: middle; width: 25%;">Template</th>
                                    <th style="text-align: center; vertical-align: middle; width: 25%;">Nama</th>
                                    <th style="text-align: center; vertical-align: middle; width: 20%;">Tanda Tangan</th>
                                    <th style="text-align: center; vertical-align: middle; width: 120px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            @include('admin.generates.partials.toolbar-modals')
        </div>
    </div>
@endsection

@push('css')
<link rel="stylesheet" href="/admin/assets/css/dataTables.bootstrap5.min.css">
@endpush

@push('js')
<script src="/admin/assets/js/jquery.dataTables.min.js"></script>
<script src="/admin/assets/js/dataTables.bootstrap5.min.js"></script>
<script type="text/javascript">
    $(function () {
        var selectedIds = [];

        var table = $('.data-table').DataTable({
            processing: true,
            serverSide: true,
            autoWidth: false,
            ajax: {
                url: "{{ route('generates.index') }}",
                type: "GET",
                data: function (d) {
                    d.site_id = $('#siteFilter').val();
                    d.type_id = $('#typeFilter').val();
                    d.start_date = $('#startDate').val();
                    d.end_date = $('#endDate').val();
                }
            },
            columns: [
                { data: 'checkbox', name: 'checkbox', orderable: false, searchable: false, width: '40px', className: 'text-center' },
                { data: 'created_at', name: 'created_at', width: '120px', className: 'text-center' },
                { data: 'template', name: 'template', width: '25%' },
                { data: 'name', name: 'name', width: '25%' },
                { data: 'signature', name: 'signature', width: '20%', className: 'text-center' },
                { data: 'action', name: 'action', orderable: false, searchable: false, width: '120px', className: 'text-center' }
            ],
            drawCallback: function() {
                $('.data-table input[type="checkbox"]').each(function() {
                    if (selectedIds.includes($(this).val())) {
                        $(this).prop('checked', true);
                    }
                });
                toggleBulkUpdateButton();
            }
        });

        $('#filterButton').on('click', function() {
            table.ajax.reload();
        });

        $('#siteFilter, #typeFilter').change(function() {
            table.ajax.reload();
        });
        
        $('#startDate, #endDate').change(function() {
            table.ajax.reload();
        });

        $('#select-all').on('click', function () {
            var rows = table.rows({ 'search': 'applied' }).nodes();
            $('input[type="checkbox"]', rows).prop('checked', this.checked);
            updateSelectedIds(); 
            toggleBulkUpdateButton();
        });
        
        $('.data-table').on('change', 'input[type="checkbox"]', function () {
            var id = $(this).val();
            if ($(this).prop('checked')) {
                selectedIds.push(id);
            } else {
                selectedIds = selectedIds.filter(function(value) {
                    return value !== id;
                });
            }
            toggleBulkUpdateButton();
        });

        function updateSelectedIds() {
            selectedIds = [];

            $('.data-table input[type="checkbox"]:checked').each(function() {
                var id = $(this).val();
                if (id !== 'on') { 
                    selectedIds.push(id);
                }
            });
        }

        function toggleBulkUpdateButton() {
            var selected = selectedIds.length;
            if (selected > 0) {
                $('#bulk-update-btn').prop('disabled', false);
                $('#bulk-delete-btn').prop('disabled', false);
            } else {
                $('#bulk-update-btn').prop('disabled', true);
                $('#bulk-delete-btn').prop('disabled', true);
            }
        }

        $('#bulkApproveForm').submit(function(e) {
            e.preventDefault();

            updateSelectedIds();

            if (selectedIds.length === 0) {
                alert('Pilih setidaknya satu surat untuk di-approve.');
                return;
            }

            $('#bulkApproveIds').val(selectedIds.join(',')); 
            this.submit();
        });

        $('#bulkDeleteForm').submit(function(e) {
            e.preventDefault();

            updateSelectedIds();

            if (selectedIds.length === 0) {
                alert('Pilih setidaknya satu surat untuk di-delete.');
                return;
            }

            $('#bulkDeleteIds').val(selectedIds.join(',')); 
            this.submit(); 
        });
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
<script>
    const canvas = document.getElementById('signatureCanvas');
    const signaturePad = new SignaturePad(canvas);

    document.getElementById('saveSignature').addEventListener('click', function() {
        if (signaturePad.isEmpty()) {
            alert('Tanda tangan kosong!');
            return;
        }

        const signatureData = signaturePad.toDataURL('image/png');
        document.getElementById('signatureInput').value = signatureData;

    });

    document.getElementById('resetSignature').addEventListener('click', function() {
        signaturePad.clear();
        document.getElementById('signatureInput').value = '';
    });
    
</script>

<script>
    $(document).ready(function() {
        $('#importModal').on('shown.bs.modal', function () {
            $(this).find('.select2').select2({
                dropdownParent: $('#importModal')
            });
        });
    });
</script>

<script>
    (function () {
        const tplSelect = document.getElementById('templateSelect');
        if (!tplSelect) return;

        const exportId = document.getElementById('exportLetterId');
        const importId = document.getElementById('importLetterId');
        const exportBtn = document.getElementById('exportTemplateBtn');
        const importBtn = document.getElementById('importTemplateBtn');

        tplSelect.addEventListener('change', function () {
            const val = this.value;
            if (exportId) exportId.value = val;
            if (importId) importId.value = val;
            const disabled = !val;
            if (exportBtn) exportBtn.disabled = disabled;
            if (importBtn) importBtn.disabled = disabled;
        });
    })();
</script>
@endpush
