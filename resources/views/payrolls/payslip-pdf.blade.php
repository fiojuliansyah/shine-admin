<!DOCTYPE html>
<html>
<head>
    <style>
        body { 
            font-family: 'Inter', 'Segoe UI', Helvetica, Arial, sans-serif; 
            background-color: #f1f5f9; 
            color: #1e293b; 
            margin: 0; 
            padding: 20px;
        }
        .payslip-container { 
            max-width: 850px; 
            margin: auto; 
            background: #ffffff; 
            padding: 40px; 
            border-radius: 8px; 
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
        }
        
        /* Header */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 20px;
            margin-bottom: 20px;
        }
        .company-info h2 { margin: 0; color: #2563eb; font-size: 22px; }
        .company-info p { margin: 4px 0; font-size: 13px; color: #64748b; }
        
        /* Grid Informasi Karyawan */
        .employee-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
            font-size: 13px;
        }
        .info-box { background: #f8fafc; padding: 15px; border-radius: 6px; }
        .info-row { display: flex; margin-bottom: 5px; }
        .info-label { width: 120px; color: #64748b; }
        .info-value { font-weight: 600; }

        /* Kolom Kanan Kiri (Pendapatan & Potongan) */
        .columns-container {
            display: flex;
            gap: 40px; /* Jarak antara kolom kiri dan kanan */
            min-height: 200px;
        }
        .column { flex: 1; }
        
        .column-title {
            font-size: 14px;
            font-weight: 700;
            padding-bottom: 8px;
            margin-bottom: 12px;
            border-bottom: 2px solid #334155;
            text-transform: uppercase;
        }

        .table-data { width: 100%; border-collapse: collapse; }
        .table-data td { padding: 8px 0; font-size: 13px; border-bottom: 1px dashed #e2e8f0; }
        .text-right { text-align: right; font-weight: 600; }
        .text-red { color: #dc2626; }

        /* Total Section */
        .thp-section {
            margin-top: 30px;
            background: #2563eb;
            color: white;
            padding: 15px 20px;
            border-radius: 6px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .thp-label { font-weight: 700; font-size: 15px; }
        .thp-value { font-size: 20px; font-weight: 800; }

        /* Tanda Tangan */
        .signature-section {
            margin-top: 40px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            text-align: center;
            font-size: 13px;
        }
        .sig-box { height: 80px; }

        .footer { 
            margin-top: 50px; 
            font-size: 11px; 
            color: #94a3b8; 
            text-align: center; 
            border-top: 1px solid #e2e8f0;
            padding-top: 15px;
        }
    </style>
</head>
<body>

<div class="payslip-container">
    <div class="header">
        <div class="company-info">
            <h2>{{ $payroll->site->name }}</h2>
            <p>Payroll System Internasional</p>
        </div>
        <div style="text-align: right;">
            <h3 style="margin:0; color: #64748b;">SLIP GAJI</h3>
            <p style="margin:4px 0; font-size: 13px;">Periode: <strong>{{ \Carbon\Carbon::parse($payroll->end_date)->format('F Y') }}</strong></p>
        </div>
    </div>

    <div class="employee-info">
        <div class="info-box">
            <div class="info-row"><span class="info-label">Nama</span>: <span class="info-value">{{ $payroll->user->name }}</span></div>
            <div class="info-row"><span class="info-label">Jabatan</span>: <span class="info-value">@foreach ($payroll->user->getRoleNames() as $role) {{ $role }} @endforeach</span></div>
        </div>
        <div class="info-box">
            <div class="info-row"><span class="info-label">Status PTKP</span>: <span class="info-value">{{ $payroll->user->profile->marriage_status }}</span></div>
            <div class="info-row"><span class="info-label">Lokasi</span>: <span class="info-value">{{ $payroll->site->name }}</span></div>
        </div>
    </div>

    <div class="columns-container">
        <div class="column">
            <div class="column-title">Pendapatan (+)</div>
            <table class="table-data">
                <tr>
                    <td>Gaji Pokok</td>
                    <td class="text-right">{{ number_format($payroll->salary, 0, ',', '.') }}</td>
                </tr>
                @foreach($payroll->payroll->payroll_components as $component)
                    @if(($component->amount ?? 0) > 0)
                    <tr>
                        <td>{{ $component->component_type->name }}</td>
                        <td class="text-right">{{ number_format($component->amount, 0, ',', '.') }}</td>
                    </tr>
                    @endif
                @endforeach
            </table>
        </div>

        <div class="column">
            <div class="column-title" style="border-bottom-color: #dc2626;">Potongan (-)</div>
            <table class="table-data">
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
                        <td class="text-right text-red">- {{ number_format($value, 0, ',', '.') }}</td>
                    </tr>
                    @endif
                @endforeach

                @foreach($payroll->payroll->payroll_deductions as $deduction)
                    @if(($deduction->amount ?? 0) > 0)
                    <tr>
                        <td>{{ $deduction->deduction_type->name }}</td>
                        <td class="text-right text-red">- {{ number_format($deduction->amount, 0, ',', '.') }}</td>
                    </tr>
                    @endif
                @endforeach
            </table>
        </div>
    </div>

    <div class="thp-section">
        <span class="thp-label">TOTAL GAJI BERSIH (TAKE HOME PAY)</span>
        <span class="thp-value">Rp {{ number_format($payroll->take_home_pay, 0, ',', '.') }}</span>
    </div>

    <div class="signature-section">
        <div>
            <p>Penerima,</p>
            <div class="sig-box"></div>
            <p><strong>( {{ $payroll->user->name }} )</strong></p>
        </div>
        <div>
            <p>HRD Manager,</p>
            <div class="sig-box"></div>
            <p><strong>( {{ $payroll->site->company->name }} )</strong></p>
        </div>
    </div>

    <div class="footer">
        <p>Slip gaji ini sah dan dihasilkan secara komputerisasi oleh sistem payroll.</p>
        <p>Dicetak pada: {{ now()->format('d M Y, H:i') }}</p>
    </div>
</div>

</body>
</html>