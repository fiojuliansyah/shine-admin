<div class="modal fade bs-example-modal-l" id="componentModal-{{ $payroll->id }}" tabindex="-1" aria-labelledby="componentModal-{{ $payroll->id }}" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Komponen Gaji</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('payrolls.update', $payroll->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <!-- Employee and payroll basic info -->
                    <div class="form-group mb-3">
                        <label for="pay_type">Karyawan</label>
                        <input type="text" id="user_id" class="form-control" value="{{ $payroll->user->name }}" disabled>
                    </div>
                    <div class="form-group mb-3">
                        <label for="pay_type">Tipe Payslip</label>
                        <select name="pay_type" id="pay_type_update" class="form-control">
                            <option value="monthly" @if($payroll->pay_type == 'monthly') selected @endif>Bulan</option>
                            <option value="daily" @if($payroll->pay_type == 'daily') selected @endif>Harian</option>
                        </select>
                    </div>
                    <div class="form-group mb-3" id="update_amount_field">
                        <label for="amount">Amount</label>
                        <input type="text" name="amount" id="amount" class="form-control" placeholder="Masukkan Gaji Bulanan" value="{{ $payroll->amount }}">
                    </div>

                    <!-- BPJS Section -->
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">BPJS Settings</h6>
                        </div>
                        <div class="card-body">
                            <div class="form-group mb-3">
                                <label for="bpjs_base-{{ $payroll->id }}">Dasar Perhitungan BPJS</label>
                                <select name="bpjs_base_type" id="bpjs_base-{{ $payroll->id }}" class="form-control">
                                    <option value="amount_salary" {{ $payroll->bpjs_base_type == 'amount_salary' ? 'selected' : '' }}>
                                        Gaji Pokok (amount_salary)
                                    </option>
                                    <option value="salary_allowance" {{ $payroll->bpjs_base_type == 'salary_allowance' ? 'selected' : '' }}>
                                        Gaji Pokok + Allowance Bulanan (salary_allowance)
                                    </option>
                                    <option value="base_budget" {{ $payroll->bpjs_base_type == 'base_budget' ? 'selected' : '' }}>
                                        Base Budget (base_budget)
                                    </option>
                                </select>
                                <small class="text-muted">Pilih dasar penghitungan BPJS sesuai kebutuhan.</small>
                            </div>
                            
                            <!-- BPJS Budget Fields -->
                            <div id="bpjs-budget-fields-{{ $payroll->id }}" class="{{ $payroll->bpjs_base_type == 'base_budget' ? '' : 'd-none' }}">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="bpjs_budget_tk-{{ $payroll->id }}">BPJS Budget TK</label>
                                        <input type="text" class="form-control" name="bpjs_budget_tk" id="bpjs_budget_tk-{{ $payroll->id }}" value="{{ $payroll->bpjs_budget_tk ?? '' }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="bpjs_budget_kes-{{ $payroll->id }}">BPJS Budget KES</label>
                                        <input type="text" class="form-control" name="bpjs_budget_kes" id="bpjs_budget_kes-{{ $payroll->id }}" value="{{ $payroll->bpjs_budget_kes ?? '' }}">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-check mb-3">
                                        <input class="form-check-input" type="radio" name="bpjs_type" id="bpjs-normatif-{{ $payroll->id }}" 
                                               value="normatif" {{ $payroll->bpjs_type == 'normatif' ? 'checked' : '' }}
                                               onchange="toggleBPJSFields({{ $payroll->id }}, 'normatif')">
                                        <label class="form-check-label" for="bpjs-normatif-{{ $payroll->id }}">
                                            BPJS Normatif
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check mb-3">
                                        <input class="form-check-input" type="radio" name="bpjs_type" id="bpjs-unnormatif-{{ $payroll->id }}" 
                                               value="unnormatif" {{ $payroll->bpjs_type == 'unnormatif' ? 'checked' : '' }}
                                               onchange="toggleBPJSFields({{ $payroll->id }}, 'unnormatif')">
                                        <label class="form-check-label" for="bpjs-unnormatif-{{ $payroll->id }}">
                                            BPJS Unnormatif
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Normatif fields with checkboxes -->
                            <div id="bpjs-normatif-fields-{{ $payroll->id }}" class="{{ $payroll->bpjs_type == 'normatif' ? '' : 'd-none' }}">
                                <div class="alert alert-info mb-3">
                                    Centang BPJS yang akan diaktifkan untuk karyawan ini dengan nilai normatif standar.
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="bpjs_normatif[]" id="bpjs-jkk-{{ $payroll->id }}" 
                                                   value="jkk" {{ $payroll->jkk_company > 0 ? 'checked' : '' }}>
                                            <label class="form-check-label" for="bpjs-jkk-{{ $payroll->id }}">
                                                JKK (Jaminan Kecelakaan Kerja) - 0.24%
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="bpjs_normatif[]" id="bpjs-jkm-{{ $payroll->id }}" 
                                                   value="jkm" {{ $payroll->jkm_company > 0 ? 'checked' : '' }}>
                                            <label class="form-check-label" for="bpjs-jkm-{{ $payroll->id }}">
                                                JKM (Jaminan Kematian) - 0.30%
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="bpjs_normatif[]" id="bpjs-jht-{{ $payroll->id }}" 
                                                   value="jht" {{ $payroll->jht_company > 0 ? 'checked' : '' }}>
                                            <label class="form-check-label" for="bpjs-jht-{{ $payroll->id }}">
                                                JHT (Jaminan Hari Tua) - 3.7% Perusahaan, 2% Karyawan
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="bpjs_normatif[]" id="bpjs-jp-{{ $payroll->id }}" 
                                                   value="jp" {{ $payroll->jp_company > 0 ? 'checked' : '' }}>
                                            <label class="form-check-label" for="bpjs-jp-{{ $payroll->id }}">
                                                JP (Jaminan Pensiun) - 2% Perusahaan, 1% Karyawan
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="bpjs_normatif[]" id="bpjs-kes-{{ $payroll->id }}" 
                                                   value="kes" {{ $payroll->kes_company > 0 ? 'checked' : '' }}>
                                            <label class="form-check-label" for="bpjs-kes-{{ $payroll->id }}">
                                                Kesehatan - 4% Perusahaan, 1% Karyawan
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Unnormatif fields (custom values) -->
                            <div id="bpjs-unnormatif-fields-{{ $payroll->id }}" class="{{ $payroll->bpjs_type == 'unnormatif' ? '' : 'd-none' }}">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label>JKK (Perusahaan) %</label>
                                        <input type="text" name="jkk_company" class="form-control" 
                                               value="{{ $payroll->jkk_company }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label>JKM (Perusahaan) %</label>
                                        <input type="text" name="jkm_company" class="form-control" 
                                               value="{{ $payroll->jkm_company }}">
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label>JHT (Perusahaan) %</label>
                                        <input type="text" name="jht_company" class="form-control" 
                                               value="{{ $payroll->jht_company }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label>JHT (Karyawan) %</label>
                                        <input type="text" name="jht_employee" class="form-control" 
                                               value="{{ $payroll->jht_employee }}">
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label>JP (Perusahaan) %</label>
                                        <input type="text" name="jp_company" class="form-control" 
                                               value="{{ $payroll->jp_company }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label>JP (Karyawan) %</label>
                                        <input type="text" name="jp_employee" class="form-control" 
                                               value="{{ $payroll->jp_employee }}">
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label>Kesehatan (Perusahaan) %</label>
                                        <input type="text" name="kes_company" class="form-control" 
                                               value="{{ $payroll->kes_company }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label>Kesehatan (Karyawan) %</label>
                                        <input type="text" name="kes_employee" class="form-control" 
                                               value="{{ $payroll->kes_employee }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Components section -->
                    <div class="card mb-4">
                        <div class="card-header bg-light d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">Allowance</h6>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="select-all-{{ $payroll->id }}" onclick="toggleAllComponents({{ $payroll->id }})">
                                <label class="form-check-label" for="select-all-{{ $payroll->id }}">
                                    Pilih Semua
                                </label>
                            </div>
                        </div>
                        <div class="card-body">
                            @foreach ($componentTypes as $componentType)
                                <div class="form-group mb-3">
                                    <div class="row align-items-center">
                                        <div class="col-md-6">
                                            @php
                                                $isChecked = isset($componentsData[$payroll->id][$componentType->id]);
                                                $value = $isChecked ? $componentsData[$payroll->id][$componentType->id] : '';
                                            @endphp
                                            <div class="form-check">
                                                <!-- This hidden field always sends the component_type_id -->
                                                <input type="hidden" name="component_types[]" value="{{ $componentType->id }}">
                                                <input class="form-check-input" type="checkbox" name="selected_components[]" 
                                                    id="component-{{ $payroll->id }}-{{ $componentType->id }}" 
                                                    value="{{ $componentType->id }}" {{ $isChecked ? 'checked' : '' }}
                                                    onchange="toggleComponentInput({{ $payroll->id }}, {{ $componentType->id }})">
                                                <label class="form-check-label" for="component-{{ $payroll->id }}-{{ $componentType->id }}">
                                                    {{ $componentType->name }}
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <input type="text" name="component_amounts[{{ $componentType->id }}]" 
                                                id="component-amount-{{ $payroll->id }}-{{ $componentType->id }}" 
                                                class="form-control" placeholder="Masukkan Jumlah" 
                                                value="{{ $value }}" {{ $isChecked ? '' : 'disabled' }}>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Deductions section -->
                    <div class="card mb-4">
                        <div class="card-header bg-light d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">Deductions</h6>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="select-all-deductions-{{ $payroll->id }}" onclick="toggleAllDeductions({{ $payroll->id }})">
                                <label class="form-check-label" for="select-all-deductions-{{ $payroll->id }}">
                                    Pilih Semua
                                </label>
                            </div>
                        </div>
                        <div class="card-body">
                            @foreach ($deductionTypes as $deductionType)
                                <div class="form-group mb-3">
                                    <div class="row align-items-center">
                                        <div class="col-md-6">
                                            @php
                                                $isChecked = isset($deductionsData[$payroll->id][$deductionType->id]);
                                                $value = $isChecked ? $deductionsData[$payroll->id][$deductionType->id] : '';
                                            @endphp
                                            <div class="form-check">
                                                <!-- This hidden field always sends the deduction_type_id -->
                                                <input type="hidden" name="deduction_types[]" value="{{ $deductionType->id }}">
                                                <input class="form-check-input" type="checkbox" name="selected_deductions[]" 
                                                    id="deduction-{{ $payroll->id }}-{{ $deductionType->id }}" 
                                                    value="{{ $deductionType->id }}" {{ $isChecked ? 'checked' : '' }}
                                                    onchange="toggleDeductionInput({{ $payroll->id }}, {{ $deductionType->id }})">
                                                <label class="form-check-label" for="deduction-{{ $payroll->id }}-{{ $deductionType->id }}">
                                                    {{ $deductionType->name }}
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <input type="text" name="deduction_amounts[{{ $deductionType->id }}]" 
                                                id="deduction-amount-{{ $payroll->id }}-{{ $deductionType->id }}" 
                                                class="form-control" placeholder="Masukkan Jumlah" 
                                                value="{{ $value }}" {{ $isChecked ? '' : 'disabled' }}>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="card mb-4">
                        <div class="card-header bg-light d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">Time Deduction (Potongan Waktu)</h6>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="time_deduction_late-{{ $payroll->id }}">Potongan Terlambat</label>
                                    <input type="text" name="time_deductions[late]" id="time_deduction_late-{{ $payroll->id }}" 
                                           class="form-control" placeholder="Masukkan jumlah potongan" 
                                           value="{{ $timeDeductionsData[$payroll->id]['late'] ?? '' }}">
                                </div>
                                <div class="col-md-6">
                                    <label for="time_deduction_alpha-{{ $payroll->id }}">Potongan Alpha (Tidak Hadir)</label>
                                    <input type="text" name="time_deductions[alpha]" id="time_deduction_alpha-{{ $payroll->id }}" 
                                           class="form-control" placeholder="Masukkan jumlah potongan" 
                                           value="{{ $timeDeductionsData[$payroll->id]['alpha'] ?? '' }}">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="time_deduction_permit-{{ $payroll->id }}">Potongan Izin</label>
                                    <input type="text" name="time_deductions[permit]" id="time_deduction_permit-{{ $payroll->id }}" 
                                           class="form-control" placeholder="Masukkan jumlah potongan" 
                                           value="{{ $timeDeductionsData[$payroll->id]['permit'] ?? '' }}">
                                </div>
                                <div class="col-md-6">
                                    <label for="time_deduction_leave-{{ $payroll->id }}">Potongan Cuti</label>
                                    <input type="text" name="time_deductions[leave]" id="time_deduction_leave-{{ $payroll->id }}" 
                                           class="form-control" placeholder="Masukkan jumlah potongan" 
                                           value="{{ $timeDeductionsData[$payroll->id]['leave'] ?? '' }}">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Overtime Section -->
                    <div class="card mb-4">
                        <div class="card-header bg-light d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">Overtime (Lembur)</h6>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label for="overtime_pay_type-{{ $payroll->id }}">Tipe Pembayaran</label>
                                    <select name="overtime[pay_type]" id="overtime_pay_type-{{ $payroll->id }}" class="form-control">
                                        <option value="hourly" {{ isset($overtime->pay_type) && $overtime->pay_type == 'hourly' ? 'selected' : '' }}>Per Jam</option>
                                        <option value="daily" {{ isset($overtime->pay_type) && $overtime->pay_type == 'daily' ? 'selected' : '' }}>Per Hari</option>
                                    </select>
                                </div>
                                <div class="col-md-8">
                                    <label for="overtime_amount-{{ $payroll->id }}">Jumlah Lembur</label>
                                    <input type="text" name="overtime[amount]" id="overtime_amount-{{ $payroll->id }}" 
                                        class="form-control" placeholder="Masukkan jumlah lembur" 
                                        value="{{ $overtime->amount ?? '' }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-4">
                        <div class="card-header bg-light d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">Metode PPH21</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="pph21_method" value="ter_gross" {{ $payroll->pph21_method == 'ter_gross' ? 'checked' : '' }}>
                                        <label class="form-check-label">Gross</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="pph21_method" value="ter_gross_up" {{ $payroll->pph21_method == 'ter_gross_up' ? 'checked' : '' }}>
                                        <label class="form-check-label">Gross Up</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                </div>            
                <div class="modal-footer">
                    <button type="button" class="btn btn-light me-2" data-bs-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
