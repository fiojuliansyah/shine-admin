<!DOCTYPE html>
<html>
<head>
    <title>Payslip - {{ \Carbon\Carbon::parse($period)->format('F Y') }}</title>
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
        .header { 
            text-align: center; 
            padding-bottom: 10px; 
            border-bottom: 2px solid #007bff;
        }
        .header h2 { 
            margin: 0; 
            color: #007bff; 
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
</head>
<body>

@foreach($generatedPayrolls as $payroll)
    <div class="payslip-container">
        <div class="header">
            <h2>PAYSLIP</h2>
        </div>
        <div class="company-info">
            <p>{{ $site->company->name ?? '' }}</p>
            <p>Payroll Periode: <strong>{{ \Carbon\Carbon::parse($payroll->end_date)->format('F Y') }}</strong></p>
        </div>

        <div class="details">
            <p><strong>Nama Pegawai:</strong> {{ $payroll->user->name }}</p>
            <p><strong>Status PTKP:</strong> {{ $payroll->user->profile->marriage_status }}</p>
            <p><strong>Jabatan:</strong> @foreach ($payroll->user->getRoleNames() as $role) 
                {{ $role }}
            @endforeach</p>
            <p><strong>Lokasi Kerja:</strong> {{ $payroll->site->name }}</p>
        </div>

        <div class="table-container">
            <table class="table">
                <tr>
                    <th>Keterangan</th>
                    <th>Jumlah</th>
                </tr>
                <tr>
                    <td>Gaji Pokok</td>
                    <td>{{ number_format($payroll->salary, 2) }}</td>
                </tr>
                @foreach($payroll->payroll->payroll_components as $component)
                    @if(($component->amount ?? 0) > 0)
                    <tr>
                        <td>{{ $component->component_type->name }}</td>
                        <td>{{ number_format($component->amount, 2) }}</td>
                    </tr>
                    @endif
                @endforeach
                <tr>
                    <th colspan="2" style="background-color: #F1F1F1; color: #8687A7;">Potongan</th>
                </tr>
                @php
                $deductions = [
                    'Iuran Hari Tua' => $payroll->jht_employee,
                    'Iuran Pensiun' => $payroll->jp_employee,
                    'Iuran Kesehatan' => $payroll->kes_employee,
                    'Potongan Telat' => $payroll->late_time_deduction,
                    'Potongan Alpha' => $payroll->alpha_time_deduction,
                    'pph21' => $payroll->pph21 ?? '< PTKP',
                ];
                @endphp

                @foreach($deductions as $label => $value)
                    @if(($value ?? 0) > 0)
                    <tr>
                        <td>{{ $label }}</td>
                        <td>{{ number_format($value, 2) }}</td>
                    </tr>
                    @endif
                @endforeach
                @foreach($payroll->payroll->payroll_deductions as $deduction)
                    @if(($deduction->amount ?? 0) > 0)
                    <tr>
                        <td>{{ $deduction->deduction_type->name }}</td>
                        <td>{{ number_format($deduction->amount, 2) }}</td>
                    </tr>
                    @endif
                @endforeach
                <tr>
                    <th>Take Home Pay</th>
                    <th>{{ number_format($payroll->take_home_pay, 2) }}</th>
                </tr>
            </table>
        </div>

        <div class="footer">
            <p>Generated by {{ $payroll->site->company->name }} Payroll System | {{ now()->format('d M Y, H:i') }}</p>
        </div>
    </div>

    @if (!$loop->last)
        <div class="page-break"></div>
    @endif
@endforeach

</body>
</html>
