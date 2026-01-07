<!DOCTYPE html>
<html>
<head>
    <style>
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background-color: #f0f2f5; 
            color: #334155; 
            margin: 0; 
            padding: 40px;
        }
        .payslip-container { 
            max-width: 850px; 
            margin: auto; 
            background: #ffffff; 
            padding: 40px; 
            border-radius: 12px; 
            box-shadow: 0 10px 25px rgba(0,0,0,0.05);
            border-top: 8px solid #1e40af;
        }
        .header-section {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #f1f5f9;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .company-brand h2 { 
            margin: 0; 
            color: #1e40af; 
            font-size: 24px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .period-badge {
            background: #eff6ff;
            color: #1e40af;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 14px;
        }
        .employee-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
            background: #f8fafc;
            padding: 20px;
            border-radius: 8px;
        }
        .info-group p { margin: 5px 0; font-size: 13px; color: #64748b; }
        .info-group strong { color: #1e293b; font-size: 14px; }

        .salary-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .salary-table th {
            text-align: left;
            padding: 12px;
            background: #f1f5f9;
            color: #475569;
            font-size: 13px;
            text-transform: uppercase;
        }
        .salary-table td {
            padding: 12px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 14px;
        }
        .amount { text-align: right; font-family: 'Courier New', Courier, monospace; font-weight: 600; }
        
        .summary-box {
            background: #1e40af;
            color: white;
            padding: 20px;
            border-radius: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 30px;
        }
        .summary-box h3 { margin: 0; font-size: 18px; opacity: 0.9; }
        .summary-box .total-amount { font-size: 24px; font-weight: 800; }

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
        <div class="header-section">
            <div class="company-brand">
                <h2>{{ $payroll->site->name }}</h2>
                <p style="margin:5px 0; font-size: 12px; color: #64748b;">Slip Gaji Karyawan Swasta</p>
            </div>
            <div class="period-badge">
                {{ \Carbon\Carbon::parse($payroll->end_date)->format('F Y') }}
            </div>
        </div>

        <div class="employee-grid">
            <div class="info-group">
                <p>Nama Pegawai</p>
                <strong>{{ $payroll->user->name }}</strong>
                <p style="margin-top:10px">Jabatan</p>
                <strong>@foreach ($payroll->user->getRoleNames() as $role) {{ $role }} @endforeach</strong>
            </div>
            <div class="info-group">
                <p>Status PTKP</p>
                <strong>{{ $payroll->user->profile->marriage_status }}</strong>
                <p style="margin-top:10px">Lokasi Kerja</p>
                <strong>{{ $payroll->site->name }}</strong>
            </div>
        </div>

        <div style="display: flex; gap: 30px;">
            <div style="flex: 1;">
                <table class="salary-table">
                    <thead>
                        <tr>
                            <th colspan="2">Pendapatan / Earnings</th>
                        </tr>
                    </thead>
                    <tbody>
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
                    </tbody>
                </table>
            </div>

            <div style="flex: 1;">
                <table class="salary-table">
                    <thead>
                        <tr>
                            <th colspan="2" style="background: #fff1f2; color: #be123c;">Potongan / Deductions</th>
                        </tr>
                    </thead>
                    <tbody>
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
                            @if($value > 0)
                            <tr>
                                <td>{{ $label }}</td>
                                <td class="amount" style="color: #be123c;">({{ number_format($value, 0, ',', '.') }})</td>
                            </tr>
                            @endif
                        @endforeach

                        @foreach($payroll->payroll->payroll_deductions as $deduction)
                            @if(($deduction->amount ?? 0) > 0)
                            <tr>
                                <td>{{ $deduction->deduction_type->name }}</td>
                                <td class="amount" style="color: #be123c;">({{ number_format($deduction->amount, 0, ',', '.') }})</td>
                            </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="summary-box">
            <h3>TOTAL GAJI BERSIH (THP)</h3>
            <div class="total-amount">IDR {{ number_format($payroll->take_home_pay, 0, ',', '.') }}</div>
        </div>

        <div class="footer">
            <p>Dokumen ini dihasilkan secara otomatis oleh sistem payroll <strong>{{ $payroll->site->company->name }}</strong>.</p>
            <p>Dicetak pada: {{ now()->format('d M Y, H:i') }}</p>
        </div>
    </div>
</body>
</html>