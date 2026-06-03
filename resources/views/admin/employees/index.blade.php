@extends('admin.layouts.main')

@section('content')
<div class="page-wrapper">
    <div class="content">

        <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
            <div class="my-auto mb-2">
                <h2 class="mb-1">Data Karyawan</h2>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="#"><i class="ti ti-smart-home"></i></a></li>
                        <li class="breadcrumb-item active" aria-current="page">Data Karyawan</li>
                    </ol>
                </nav>
            </div>
            <div class="d-flex my-xl-auto right-content align-items-center flex-wrap">
                <div class="me-2 mb-2">
                    <a href="#" data-bs-toggle="modal" data-bs-target="#exportModal" class="btn btn-success d-inline-flex align-items-center">
                        <i class="ti ti-file-export me-1"></i>Export Excel
                    </a>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header pb-0 border-bottom-0">
                <ul class="nav nav-tabs nav-tabs-bottom" id="companyTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button type="button"
                                class="nav-link active company-tab"
                                data-company-id=""
                                id="company-tab-all"
                                data-bs-toggle="tab"
                                data-bs-target="#company-pane-all"
                                role="tab">
                            <i class="ti ti-building me-1"></i>Semua Company
                        </button>
                    </li>
                    @foreach ($companies as $company)
                        <li class="nav-item" role="presentation">
                            <button type="button"
                                    class="nav-link company-tab"
                                    data-company-id="{{ $company->id }}"
                                    id="company-tab-{{ $company->id }}"
                                    data-bs-toggle="tab"
                                    data-bs-target="#company-pane-{{ $company->id }}"
                                    role="tab">
                                <i class="ti ti-building-skyscraper me-1"></i>{{ $company->name }}
                                <span class="badge bg-light text-dark ms-1">{{ $company->sites->count() }}</span>
                            </button>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="card-body p-0">
                <div class="tab-content border-top">
                    <div class="tab-pane fade show active px-3 pt-3" id="company-pane-all" role="tabpanel">
                        <div class="d-flex flex-wrap gap-2 mb-2">
                            <button type="button" class="btn btn-sm btn-primary site-chip active" data-company-id="" data-site-id="">
                                Semua Site
                            </button>
                            @foreach ($sites as $site)
                                <button type="button" class="btn btn-sm btn-outline-primary site-chip"
                                        data-company-id="{{ $site->company_id }}"
                                        data-site-id="{{ $site->id }}">
                                    {{ $site->name }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    @foreach ($companies as $company)
                        <div class="tab-pane fade px-3 pt-3" id="company-pane-{{ $company->id }}" role="tabpanel">
                            <div class="d-flex flex-wrap gap-2 mb-2">
                                <button type="button" class="btn btn-sm btn-primary site-chip active"
                                        data-company-id="{{ $company->id }}" data-site-id="">
                                    Semua Site
                                </button>
                                @forelse ($company->sites as $site)
                                    <button type="button" class="btn btn-sm btn-outline-primary site-chip"
                                            data-company-id="{{ $company->id }}"
                                            data-site-id="{{ $site->id }}">
                                        {{ $site->name }}
                                    </button>
                                @empty
                                    <span class="text-muted small">Belum ada site untuk company ini.</span>
                                @endforelse
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="px-3 pb-2 d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <small class="text-muted" id="activeFilterLabel"></small>
                    <button type="button" id="resetCompanyFilter" class="btn btn-sm btn-outline-secondary">
                        <i class="ti ti-refresh me-1"></i> Reset Filter
                    </button>
                </div>

                <input type="hidden" id="filter_company_id" value="">
                <input type="hidden" id="filter_site_id" value="">

                <div class="custom-datatable-filter table-responsive">
                    {{ $dataTable->table(['id' => 'employees-table']) }}
                </div>
            </div>
        </div>

    </div>

    {{-- Modal Export --}}
    <div class="modal fade" id="exportModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">Export Data Karyawan</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('employees.export') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Filter Site</label>
                            <select name="site_id" class="form-select select2-modal">
                                <option value="">Semua Site</option>
                                @foreach ($sites as $site)
                                    <option value="{{ $site->id }}">{{ $site->name }}</option>
                                @endforeach
                            </select>
                            <div class="form-text">Kosongkan untuk export semua site.</div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success">
                            <i class="ti ti-download me-1"></i>Download Excel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection

@push('css')
<link rel="stylesheet" href="/admin/assets/css/dataTables.bootstrap5.min.css">
@endpush

@push('js')
<script src="/admin/assets/js/jquery.dataTables.min.js"></script>
<script src="/admin/assets/js/dataTables.bootstrap5.min.js"></script>
{{ $dataTable->scripts(attributes: ['type' => 'module']) }}

<script>
$(document).ready(function () {
    $('.select2').select2();

    window.LaravelDataTables = window.LaravelDataTables || {};
    if (window.LaravelDataTables['employees-table']) {
        window.LaravelDataTables['employees-table'].on('preXhr.dt', function (e, settings, data) {
            data.company_id = $('#filter_company_id').val();
            data.site_id    = $('#filter_site_id').val();
        });
    }

    function reloadTable() {
        if ($.fn.DataTable.isDataTable('#employees-table')) {
            $('#employees-table').DataTable().ajax.reload(null, false);
        }
        renderActiveLabel();
    }

    function renderActiveLabel() {
        var companyId = $('#filter_company_id').val();
        var siteId    = $('#filter_site_id').val();
        var parts = [];
        if (companyId) {
            var label = $('.company-tab[data-company-id="' + companyId + '"]').text().trim();
            if (label) parts.push('Company: ' + label);
        }
        if (siteId) {
            var siteLabel = $('.site-chip[data-site-id="' + siteId + '"]').first().text().trim();
            if (siteLabel) parts.push('Site: ' + siteLabel);
        }
        $('#activeFilterLabel').text(parts.length ? 'Filter aktif → ' + parts.join(' • ') : '');
    }

    $('.company-tab').on('shown.bs.tab', function () {
        var companyId = $(this).data('company-id') || '';
        $('#filter_company_id').val(companyId);
        $('#filter_site_id').val('');
        var paneSelector = $(this).data('bs-target');
        $(paneSelector).find('.site-chip').removeClass('btn-primary active').addClass('btn-outline-primary');
        $(paneSelector).find('.site-chip[data-site-id=""]').addClass('btn-primary active').removeClass('btn-outline-primary');
        reloadTable();
    });

    $(document).on('click', '.site-chip', function () {
        var $btn = $(this);
        var siteId = $btn.data('site-id') || '';

        var $pane = $btn.closest('.tab-pane');
        $pane.find('.site-chip').removeClass('btn-primary active').addClass('btn-outline-primary');
        $btn.addClass('btn-primary active').removeClass('btn-outline-primary');

        $('#filter_company_id').val($('.company-tab.active').data('company-id') || '');
        $('#filter_site_id').val(siteId);
        reloadTable();
    });

    $('#resetCompanyFilter').on('click', function () {
        $('#filter_company_id').val('');
        $('#filter_site_id').val('');
        $('.company-tab').removeClass('active');
        $('#company-tab-all').addClass('active');
        $('.tab-pane').removeClass('show active');
        $('#company-pane-all').addClass('show active');
        $('.site-chip').removeClass('btn-primary active').addClass('btn-outline-primary');
        $('#company-pane-all .site-chip[data-site-id=""]').addClass('btn-primary active').removeClass('btn-outline-primary');
        reloadTable();
    });

    $('#exportModal').on('shown.bs.modal', function () {
        $(this).find('.select2-modal').select2({
            dropdownParent: $(this),
            placeholder: 'Semua Site',
            allowClear: true,
        });
    });

    renderActiveLabel();
});
</script>
@endpush
