@extends('layouts.main')

@section('content')
<div class="page-wrapper">
    <div class="content">
        <div class="card">
            <div class="card-body">
                <style>
                    body { 
                        font-family: 'Arial', sans-serif; 
                        background-color: #f8f9fa; 
                        color: #333; 
                        margin: 0; 
                        padding: 20px;
                    }
                    .payslip-container { 
                        max-width: 800px; 
                        margin: auto; 
                        background: #ffffff; 
                        padding: 20px; 
                        border-radius: 10px; 
                        box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1); 
                    }

                    .company-info { 
                        text-align: center; 
                        font-size: 14px; 
                        color: #777; 
                        margin-bottom: 20px; 
                    }
                    .details { 
                        margin-top: 20px; 
                        padding: 10px; 
                        background: #f1f1f1; 
                        border-radius: 5px; 
                    }
                    .table-container { 
                        margin-top: 20px; 
                    }
                    .table { 
                        width: 100%; 
                        border-collapse: collapse; 
                    }
                    .table th, .table td { 
                        padding: 10px; 
                        border: 1px solid #ddd; 
                        text-align: left; 
                    }
                    .table th { 
                        background: #007bff; 
                        color: white; 
                    }
                    .footer { 
                        margin-top: 30px; 
                        text-align: center; 
                        font-size: 12px; 
                        color: #777; 
                    }
                    .signature { 
                        margin-top: 40px; 
                        text-align: right; 
                    }
                    .signature p { 
                        margin: 0; 
                        font-weight: bold; 
                    }
                    .page-break { 
                        page-break-after: always; 
                    }
                </style>
        
                <div class="row mt-4">
                    <div class="company-info">
                        <p>{{ $payroll->site->name }}</p>
                        <p>Payroll Period: <strong>{{ \Carbon\Carbon::parse($payroll->end_date)->format('F Y') }}</strong></p>
                    </div>
            
                    <div class="details">
                        <p><strong>Employee Name:</strong> {{ $payroll->user->name }}</p>
                        <p><strong>Position:</strong> @foreach ($payroll->user->getRoleNames() as $role) 
                            {{ $role }}
                        @endforeach</p>
                        <p><strong>Site:</strong> {{ $payroll->site->name }}</p>
                    </div>
            
                    <div class="table-container">
                        <table class="table">
                            <tr>
                                <th>Description</th>
                                <th>Amount</th>
                            </tr>
                            <tr>
                                <td>Basic Salary</td>
                                <td>{{ number_format($payroll->salary, 2) }}</td>
                            </tr>
                            <tr>
                                <td>Allowances</td>
                                <td>{{ number_format($payroll->allowance_fix, 2) }}</td>
                            </tr>
                            <tr>
                                <th colspan="2" style="background-color: #F1F1F1; color: #8687A7;">Potongan</th>
                            </tr>
                            <tr>
                                <td>Iuran Hari Tua</td>
                                <td>{{ number_format($payroll->jht_employee, 2) }}</td>
                            </tr>
                            <tr>
                                <td>Iuran Pensiun</td>
                                <td>{{ number_format($payroll->jp_employee, 2) }}</td>
                            </tr>
                            <tr>
                                <td>Iuran Kesehatan</td>
                                <td>{{ number_format($payroll->kes_employee, 2) }}</td>
                            </tr>
                            <tr>
                                <td>Potongan Lain</td>
                                <td>{{ number_format($payroll->deduction_fix, 2) }}</td>
                            </tr>
                            <tr>
                                <td>Potongan Telat</td>
                                <td>{{ number_format($payroll->late_time_deduction, 2) }}</td>
                            </tr>
                            <tr>
                                <td>Potongan Alpha</td>
                                <td>{{ number_format($payroll->alpha_time_deduction, 2) }}</td>
                            </tr>
                            <tr>
                                <td>Pph21</td>
                                <td>{{ number_format($payroll->pph21, 2) }}</td>
                            </tr>
                            <tr>
                                <th>Net Pay</th>
                                <th>{{ number_format($payroll->take_home_pay, 2) }}</th>
                            </tr>
                        </table>
                    </div>
            
                    <div class="text-center mt-4">
                        <a href="{{ route('payroll.generateDetail', ['id' => $payroll->site_id, 'period' => $payroll->end_date]) }}" class="btn btn-secondary">Back</a>
                        <a href="{{ route('payroll.downloadPayslip', ['id' => $payroll->id]) }}" class="btn btn-success">Download PDF</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
