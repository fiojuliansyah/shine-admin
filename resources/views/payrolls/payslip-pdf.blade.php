<!DOCTYPE html>
<html>
<head>
    <style>
        body { 
            font-family: 'Inter', 'Segoe UI', Arial, sans-serif; 
            background-color: #eef2f6; 
            color: #334155; 
            margin: 0; 
            padding: 30px;
            line-height: 1.5;
        }
        .payslip-container { 
            max-width: 900px; 
            margin: auto; 
            background: #ffffff; 
            padding: 40px; 
            border-radius: 15px; 
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        
        /* Header & Info Section */
        .header { 
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 3px solid #3b82f6;
            padding-bottom: 20px;
            margin-bottom: 25px;
        }
        .company-title h2 { margin: 0; color: #1e3a8a; text-transform: uppercase; letter-spacing: 1px; }
        .company-title p { margin: 5px 0 0; font-size: 14px; color: #64748b; }

        .employee-details {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            background: #f8fafc;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
        }
        .detail-item { font-size: 13px; }
        .detail-item span { display: block; color: #64748b; font-weight: 600; text-transform: uppercase; font-size: 11px; }
        .detail-item strong { color: #1e293b; font-size: 15px; }

        /* Column Layout for Salary */
        .salary-columns {
            display: flex;
            gap: 25px;
            margin-bottom: 30px;
        }
        .column { flex: 1; }
        
        .column-title {
            background: #f1f5f9;
            padding: 10px 15px;
            border-radius: 8px 8px 0 0;
            font-weight: bold;
            font-size: 14px;
            color: #475569;
            border-bottom: 2px solid #cbd5e1;
        }

        .table-salary {
            width: 100%;
            border-collapse: collapse;
        }
        .table-salary td {
            padding: 12px 15px;
            font-size: 13px;
            border-bottom: 1px solid #f1f5f9;
        }
        .amount { text-align: right; font-weight: 600; color: #1e293b; }
        .deduction-amount { text-align: right; font-weight: 600; color: #e11d48; }

        /* Total Section */
        .total-row {
            background: #1e3a8a;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 25px;
            border-radius: 10px;
            margin-top: 20px;
        }
        .total-label { font-size: 16px; font-weight: bold; }
        .total-value { font-size: 24px; font-weight: 800; }

        .footer { 
            margin-top: 40px; 
            text-align: center; 
            font-size: 11px; 
            color: #94a3b8;
            border-top: 1px solid #f1f5f9;
            padding-top: 20px;
        }
    </style>
</head>
<body>
    <div class="payslip-container">
        <div class="header">
            <div class="company-title">
                <h2>{{ $payroll->site->name }}</h2>
                <p>Slip Gaji Resmi - Periode {{ \Carbon\Carbon::parse($payroll->end_date)->format('F Y') }}</p>
            </div>
            <div style="text-align: right">
                <div style="background: #dbeafe; color: #1e40af; padding: 5px 15px; border-radius: 5px; font-weight: bold; font-size: 12px;">CONFIDENTIAL</div>
            </div>
        </div>

        <div class="employee-details">
            <div class="detail-item">
                <span>Nama Pegawai</span>
                <strong>{{ $payroll->user->name }}</strong>
            </div>
            <div class="detail-item">
                <span>Status PTKP</span>
                <strong>{{ $payroll->user->profile->marriage_status }}</strong>
            </div>
            <div class="detail-item">
                <span>Jabatan / Role</span>
                <strong>@foreach ($payroll->user->getRoleNames() as $role) {{ $role }} @endforeach</strong>
            </div>
            <div class="detail-item">
                <span>Lokasi Kerja</span>
                <strong>{{ $payroll->site->name }}</strong>
            </div>
        </div>

        <div class="salary-columns">
            <div class="column">
                <div class="column-title">PENDAPATAN (EARNINGS)</div>
                <table class="table-salary">
                    <tr>
                        <td>Gaji Pokok</td>
                        <td class="amount">{{ number_format($payroll->salary, 0, ',', '.') }}</td>
                    </tr>
                    @foreach($payroll->payroll->payroll_components as $component)
                        @if(($component->amount ?? 0) > 0)
                        <tr>
                            <td>{{ $component->component_type->name }}</td>
                            <td class="amount">{{ number_format($component->amount, 0, ',', '.') }}</td>
                        </tr>
                        @endif
                    @endforeach
                </table>
            </div>

            <div class="column">
                <div class="column-title" style="color: #be123c;">POTONGAN (DEDUCTIONS)</div>
                <table class="table-salary">
                    @php
                    $deductions = [
                        'Iuran Hari Tua' => $payroll->jht_employee,
                        'Iuran Pensiun' => $payroll->jp_employee,
                        'Iuran Kesehatan' => $payroll->kes_employee,
                        'Potongan Telat' => $payroll->late_time_deduction,
                        'Potongan Alpha' => $payroll->alpha_time_deduction,
                        'PPH21' => $payroll->pph21,
                    ];
                    @endphp

                    @foreach($deductions as $label => $value)
                        @if(($value ?? 0) > 0)
                        <tr>
                            <td>{{ $label }}</td>
                            <td class="deduction-amount">- {{ number_format($value, 0, ',', '.') }}</td>
                        </tr>
                        @endif
                    @endforeach

                    @foreach($payroll->payroll->payroll_deductions as $deduction)
                        @if(($deduction->amount ?? 0) > 0)
                        <tr>
                            <td>{{ $deduction->deduction_type->name }}</td>
                            <td class="deduction-amount">- {{ number_format($deduction->amount, 0, ',', '.') }}</td>
                        </tr>
                        @endif
                    @endforeach
                </table>
            </div>
        </div>

        <div class="total-row">
            <div class="total-label">TAKE HOME PAY (THP)</div>
            <div class="total-value">Rp {{ number_format($payroll->take_home_pay, 0, ',', '.') }}</div>
        </div>

        <div class="footer">
            <p>Generated by {{ $payroll->site->company->name }} Payroll System</p>
            <p>Waktu Cetak: {{ now()->format('d M Y, H:i:s') }}</p>
        </div>
    </div>
</body>
</html>