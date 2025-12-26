@extends('layouts.main')

@section('content')
<div class="page-wrapper">
    <div class="content">
        <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
            <div class="my-auto mb-2">
                <h2 class="mb-1">Payroll {{ $site->name }}</h2>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item">
                            <a href="index.html"><i class="ti ti-smart-home"></i></a>
                        </li>
                        <li class="breadcrumb-item">
                            Finance & Accounts
                        </li>
                        <li class="breadcrumb-item">
                            Generate Payroll
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">{{ $site->name }}</li>
                    </ol>
                </nav>
            </div>
        </div>
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered" style="font-size: 12px;">
                                <thead>
                                    <tr>
                                        <th>Employee NIK</th>
                                        <th>Name</th>
                                        <th>Jabatan</th>
                                        <th>Salary</th>
                                        <th>Allowances</th>
                                        <th>Deductions</th>
                                        <th>BPJS Karyawan</th>
                                        <th>BPJS Company</th>
                                        <th>PPh21 (Bulan)</th>
                                        <th>Telat</th>
                                        <th>Alpha</th>
                                        <th>Take Home Pay</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($generatedPayrolls as $payroll)
                                    <tr>
                                        <td>{{ $payroll->user->employee_nik }}</td>
                                        <td>{{ $payroll->user->name }}</td>
                                        <td>
                                            @foreach ($payroll->user->getRoleNames() as $role) 
                                                {{ $role }}
                                            @endforeach
                                        </td>
                                        <td>{{ number_format($payroll->salary) }}</td>
                                        <td>{{ number_format($payroll->allowance_fix + $payroll->allowance_non_fix) }}</td>
                                        <td>{{ number_format($payroll->deduction_fix + $payroll->deduction_non_fix) }}</td>
                                        <td>{{ number_format($payroll->jht_employee + $payroll->jp_employee + $payroll->kes_employee) }}</td>
                                        <td>{{ number_format($payroll->jkk_company + $payroll->jkm_company + $payroll->jht_company + $payroll->jp_company + $payroll->kes_company) }}</td>
                                        <td>{{ number_format($payroll->pph21) }}</td>
                                        <td>{{ number_format($payroll->late_time_deduction) }}</td>
                                        <td>{{ number_format($payroll->alpha_time_deduction) }}</td>
                                        <td>{{ number_format($payroll->take_home_pay) }}</td>
                                        <td>
                                            <a href="{{ route('payroll.viewPayslip', ['id' => $payroll->id]) }}" class="btn btn-xs rouded-pill btn-primary">
                                                View Payslip
                                            </a>
                                            <button type="button" class="btn btn-xs rouded-pill btn-primary" data-bs-toggle="modal" data-bs-target="#pph21Modal{{ $payroll->id }}">
                                                View Detail PPh21
                                            </button>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="9" class="text-center">No payroll data available for this period.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <a href="{{ route('payroll.generate') }}" class="btn btn-secondary mt-3">Back to Generate Payroll</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal for PPh21 Detail -->
@foreach($generatedPayrolls as $payroll)
<div class="modal fade" id="pph21Modal{{ $payroll->id }}" tabindex="-1" aria-labelledby="pph21ModalLabel{{ $payroll->id }}" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="pph21ModalLabel{{ $payroll->id }}">Detail PPh21 for {{ $payroll->user->name }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Form with disabled inputs -->
                <form>
                    <div class="mb-3">
                        <label class="form-label">Jenis Potongan</label>
                        <input type="text" class="form-control" value="PPh21 Bulanan" disabled>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Skema Perhitungan</label>
                        <input type="text" class="form-control" 
                               value="{{ $payroll->payroll->pph21_method == 'ter_gross' ? 'GROSS' : 'GROSS UP' }}" disabled>
                    </div>                    
                    <div class="mb-3">
                        <label class="form-label">Penghasilan Bruto</label>
                        <input type="text" class="form-control" value="{{ number_format($payroll->salary + $payroll->allowance_fix + $payroll->allowance_non_fix) }}" disabled>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">PTKP</label>
                        <input type="text" class="form-control" value="{{ $payroll->user->profile->marriage_status }}" disabled>
                    </div>
                    <!-- DPP -->
                    <div class="mb-3">
                        <label class="form-label">DPP (Dasar Pengenaan Pajak)</label>
                        <input type="text" class="form-control" 
                               value="{{ number_format($payroll->salary + $payroll->allowance_fix + $payroll->allowance_non_fix - $payroll->deduction_fix) }}" disabled>
                    </div>
                    <!-- Tarif Pajak -->
                    <div class="mb-3">
                        <label class="form-label">Tarif Pajak</label>
                        <input type="text" class="form-control" value="5%" disabled>
                    </div>
                    <!-- PPh21 -->
                    <div class="mb-3">
                        <label class="form-label">PPh21</label>
                        <input type="text" class="form-control" 
                               value="{{ number_format(($payroll->salary + $payroll->allowance_fix + $payroll->allowance_non_fix - $payroll->deduction_fix) * 0.05) }}" disabled>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endforeach


@endsection
