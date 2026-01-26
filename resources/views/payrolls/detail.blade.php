@extends('layouts.main')

@push('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<style>
    .table-responsive {
        overflow-x: auto;
        width: 100%;
    }
    table.dataTable thead th {
        white-space: nowrap;
        text-align: center;
        vertical-align: middle;
        background-color: #f8f9fa;
    }
    .dataTables_wrapper .dataTables_filter {
        margin-bottom: 15px;
    }
</style>
@endpush

@section('content')
<div class="page-wrapper">
    <div class="content">
        <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
            <div class="my-auto mb-2">
                <h2 class="mb-1">Payroll {{ $site->name }}</h2>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="#"><i class="ti ti-smart-home"></i></a></li>
                        <li class="breadcrumb-item">Finance</li>
                        <li class="breadcrumb-item">Master Payroll</li>
                        <li class="breadcrumb-item active">{{ $site->name }}</li>
                    </ol>
                </nav>
            </div>
            <div class="d-flex my-xl-auto right-content align-items-center flex-wrap">
                @include('payrolls.partials.head-button')
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="mainPayrollTable" class="table table-bordered" style="font-size: 12px; min-width: 1200px;">
                        <thead>
                            <tr>
                                <th style="width: 40px;"><input class="form-check-input" type="checkbox" id="select-all-payrolls"></th>
                                <th>NIK</th>
                                <th>Nama</th>
                                <th>Jabatan</th>
                                <th>Tipe</th>
                                <th>Cutoff Day</th>
                                <th>Gaji</th>
                                @foreach ($componentTypes as $componentType)   
                                    <th>{{ $componentType->name }}</th>
                                @endforeach
                                @foreach ($deductionTypes as $deductionType)   
                                    <th>{{ $deductionType->name }}</th>
                                @endforeach
                                <th>Potongan Waktu</th>
                                <th>Lembur</th>
                                <th>BPJS</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($payrolls as $payroll)
                                <tr>
                                    <td class="text-center">
                                        <input class="form-check-input payroll-checkbox" type="checkbox" value="{{ $payroll->id }}" name="selected_payrolls[]">
                                    </td>
                                    <td>{{ $payroll->user->employee_nik }}</td>
                                    <td>{{ $payroll->user->name }}</td>
                                    <td>
                                        @foreach ($payroll->user->getRoleNames() as $role) 
                                            <span class="badge bg-outline-primary">{{ $role }}</span>
                                        @endforeach
                                    </td>
                                    <td>{{ $payroll->pay_type == 'monthly' ? 'Gaji Pokok' : 'Gaji Harian' }}</td>
                                    <td>{{ $payroll->cutoff_day }}</td>
                                    <td data-order="{{ $payroll->amount }}">
                                        {{ number_format($payroll->amount, 0, ',', '.') }}
                                    </td>
                                    @foreach ($componentTypes as $componentType)
                                        @php $cVal = $componentsData[$payroll->id][$componentType->id] ?? 0; @endphp
                                        <td data-order="{{ $cVal }}">
                                            {{ $cVal ? number_format($cVal, 0, ',', '.') : '-' }}
                                        </td>
                                    @endforeach
                                    @foreach ($deductionTypes as $deductionType)
                                        @php $dVal = $deductionsData[$payroll->id][$deductionType->id] ?? 0; @endphp
                                        <td data-order="{{ $dVal }}">
                                            {{ $dVal ? number_format($dVal, 0, ',', '.') : '-' }}
                                        </td>
                                    @endforeach
                                    <td>
                                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2px; font-size: 10px;">
                                            <span><b>Telat:</b> {{ number_format($timeDeductionsData[$payroll->id]['late'], 0, ',', '.') }}</span>
                                            <span><b>Alpa:</b> {{ number_format($timeDeductionsData[$payroll->id]['alpha'], 0, ',', '.') }}</span>
                                            <span><b>Ijin:</b> {{ number_format($timeDeductionsData[$payroll->id]['permit'], 0, ',', '.') }}</span>
                                            <span><b>Cuti:</b> {{ number_format($timeDeductionsData[$payroll->id]['leave'], 0, ',', '.') }}</span>
                                        </div>
                                    </td>
                                    @php $oVal = $overtimeData[$payroll->id]['amount'] ?? 0; @endphp
                                    <td data-order="{{ $oVal }}">
                                        @if($oVal > 0)
                                            <small>{{ $overtimeData[$payroll->id]['pay_type'] == 'hourly' ? 'Jam' : 'Hari' }}</small><br>
                                            {{ number_format($oVal, 0, ',', '.') }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        <div style="font-size: 10px; line-height: 1.4;">
                                            <div>
                                                <strong>JHT:</strong> 
                                                E: {{ number_format($payroll->jht_employee, 2) }}% | C: {{ number_format($payroll->jht_company, 2) }}%
                                            </div>
                                            <div>
                                                <strong>JP:</strong> 
                                                E: {{ number_format($payroll->jp_employee, 2) }}% | C: {{ number_format($payroll->jp_company, 2) }}%
                                            </div>
                                            <div>
                                                <strong>KES:</strong> 
                                                E: {{ number_format($payroll->kes_employee, 2) }}% | C: {{ number_format($payroll->kes_company, 2) }}%
                                            </div>
                                            <div>
                                                <strong>JKK:</strong> {{ number_format($payroll->jkk_company, 2) }}% | 
                                                <strong>JKM:</strong> {{ number_format($payroll->jkm_company, 2) }}%
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#componentModal-{{ $payroll->id }}">
                                            Lihat
                                        </button>
                                    </td>
                                </tr>
                                @include('payrolls.partials.modals')
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script src="https://code.jquery.com/jquery-3.7.0.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
$(document).ready(function () {
    const table = $('#mainPayrollTable').DataTable({
        paging: true,
        ordering: true,
        info: true,
        scrollX: true,
        columnDefs: [
            { orderable: false, targets: [0, -1] }
        ],
        language: {
            search: 'Cari Karyawan:',
            lengthMenu: 'Tampilkan _MENU_ data'
        }
    });

    $('#select-all-payrolls').on('change', function () {
        const rows = table.rows({ search: 'applied' }).nodes();
        $('input.payroll-checkbox', rows).prop('checked', this.checked);
        toggleBulkButtonState();
    });

    $('#mainPayrollTable tbody').on('change', 'input.payroll-checkbox', function () {
        toggleBulkButtonState();
    });

    function toggleBulkButtonState() {
        const selectedCount = $('.payroll-checkbox:checked').length;
        const bulkBtn = $('[data-bs-target="#bulkUpdateModal"]');
        if (bulkBtn.length) {
            bulkBtn.prop('disabled', selectedCount === 0);
        }
    }

    $('#bulkUpdateForm').on('submit', function () {
        const container = $('#selected-payrolls-container');
        container.empty();

        $('.payroll-checkbox:checked').each(function () {
            container.append(
                `<input type="hidden" name="selected_payrolls[]" value="${this.value}">`
            );
        });

        if (!container.children().length) {
            alert('Pilih minimal satu karyawan!');
            return false;
        }

        $('#bulkUpdateSubmit')
            .html('<span class="spinner-border spinner-border-sm"></span> Processing...')
            .prop('disabled', true);

        return true;
    });
});
</script>

<script>
document.addEventListener('change', function (e) {
    if (e.target.matches('[data-bpjs-base]')) {
        const context = e.target.dataset.context;
        const value = e.target.value;

        if (context === 'bulk') {
            const fields = document.getElementById('bulk-bpjs-budget-fields');
            if (fields) fields.classList.toggle('d-none', value !== 'base_budget');
        }

        if (context === 'detail') {
            const payrollId = e.target.dataset.payrollId;
            const fields = document.getElementById(`bpjs-budget-fields-${payrollId}`);
            if (fields) fields.classList.toggle('d-none', value !== 'base_budget');
        }
    }

    if (e.target.matches('[data-bpjs-type]')) {
        const context = e.target.dataset.context;
        const type = e.target.value;

        if (context === 'bulk') {
            toggleBPJSType('bulk-bpjs-normatif-fields', 'bulk-bpjs-unnormatif-fields', type);
        }

        if (context === 'detail') {
            const payrollId = e.target.dataset.payrollId;
            toggleBPJSType(`bpjs-normatif-fields-${payrollId}`, `bpjs-unnormatif-fields-${payrollId}`, type);
        }
    }

    if (e.target.matches('[data-component-checkbox]')) {
        const id = e.target.dataset.id;
        const amountInput = document.getElementById(`component-amount-${id}`);
        const expiryInput = document.getElementById(`component-expiry-${id}`);
        const typeInput = document.getElementById(`component-type-${id}`);
        const isChecked = e.target.checked;

        if (amountInput) {
            amountInput.disabled = !isChecked;
            if (!isChecked) amountInput.value = '';
        }
        if (expiryInput) {
            expiryInput.disabled = !isChecked;
            if (!isChecked) expiryInput.value = '';
        }
        if (typeInput) {
            typeInput.disabled = !isChecked;
            if (!isChecked) typeInput.value = '';
        }
    }

    if (e.target.matches('[data-deduction-checkbox]')) {
        const id = e.target.dataset.id;
        const amountInput = document.getElementById(`deduction-amount-${id}`);
        const expiryInput = document.getElementById(`deduction-expiry-${id}`);
        const isChecked = e.target.checked;

        if (amountInput) {
            amountInput.disabled = !isChecked;
            if (!isChecked) amountInput.value = '';
        }
        if (expiryInput) {
            expiryInput.disabled = !isChecked;
            if (!isChecked) expiryInput.value = '';
        }
    }

    if (e.target.matches('[data-bulk-component-checkbox]')) {
        const id = e.target.value;
        const amountInput = document.getElementById(`bulk-component-amount-${id}`);
        const expiryInput = document.getElementById(`bulk-component-expiry-${id}`);
        const typeInput = document.getElementById(`bulk-component-type-${id}`);
        const isChecked = e.target.checked;

        if (amountInput) {
            amountInput.disabled = !isChecked;
            if (!isChecked) amountInput.value = '';
        }
        if (expiryInput) {
            expiryInput.disabled = !isChecked;
            if (!isChecked) expiryInput.value = '';
        }
        if (typeInput) {
            typeInput.disabled = !isChecked;
            if (!isChecked) typeInput.value = '';
        }
    }

    if (e.target.matches('[data-bulk-deduction-checkbox]')) {
        const id = e.target.value;
        const amountInput = document.getElementById(`bulk-deduction-amount-${id}`);
        const expiryInput = document.getElementById(`bulk-deduction-expiry-${id}`);
        const isChecked = e.target.checked;

        if (amountInput) {
            amountInput.disabled = !isChecked;
            if (!isChecked) amountInput.value = '';
        }
        if (expiryInput) {
            expiryInput.disabled = !isChecked;
            if (!isChecked) expiryInput.value = '';
        }
    }
});
</script>

<script>
function toggleBPJSType(normatifId, unnormatifId, type) {
    const normatif = document.getElementById(normatifId);
    const unnormatif = document.getElementById(unnormatifId);

    if (!normatif || !unnormatif) return;

    if (type === 'normatif') {
        normatif.classList.remove('d-none');
        unnormatif.classList.add('d-none');
    } else if (type === 'unnormatif') {
        unnormatif.classList.remove('d-none');
        normatif.classList.add('d-none');
    } else {
        normatif.classList.add('d-none');
        unnormatif.classList.add('d-none');
    }
}
</script>
@endpush
