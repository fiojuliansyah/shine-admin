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
                                        <td>
                                            @if(($payroll->pph21 ?? 0) <= 0)
                                                &lt; PTKP
                                            @else
                                                {{ number_format($payroll->pph21) }}
                                            @endif
                                        </td>
                                        <td>{{ number_format($payroll->late_time_deduction) }}</td>
                                        <td>{{ number_format($payroll->alpha_time_deduction) }}</td>
                                        <td>{{ number_format($payroll->take_home_pay) }}</td>
                                        <td>
                                            <a href="{{ route('payroll.viewPayslip', ['id' => $payroll->id]) }}" class="btn btn-xs rouded-pill btn-primary">
                                                View Payslip
                                            </a>
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
@endsection
