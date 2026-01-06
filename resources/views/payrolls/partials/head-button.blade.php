<div class="btn-toolbar p-3" role="toolbar">
    <div class="btn-group me-2 mb-2 mb-sm-0">
        <form action=""></form>
        <button type="button" class="btn btn-primary waves-light waves-effect" data-bs-toggle="modal" data-bs-target="#retweetModal">
            <i class="fas fa-retweet"></i>
        </button>
    </div>
    <div class="btn-group me-2 mb-2 mb-sm-0">
        <button type="button" class="btn btn-primary waves-light waves-effect dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="fa fa-folder"></i> <i class="mdi mdi-chevron-down ms-1"></i>
        </button>
        <div class="dropdown-menu">
            <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#bulkUpdateModal">Bulk Update</a>
        </div>
    </div>
    <div class="btn-group me-2 mb-2 mb-sm-0">
        <button type="button"
            class="btn btn-primary waves-light waves-effect dropdown-toggle"
            data-bs-toggle="dropdown" aria-expanded="false">
            More <i class="mdi mdi-dots-vertical ms-2"></i>
        </button>
        <div class="dropdown-menu">
            <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#allowanceModal">+ Tunjangan</a>
            <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#deductionModal">+ Pengurangan lainnya</a>
        </div>
    </div>
</div>

<div class="modal fade" id="retweetModal" tabindex="-1" aria-labelledby="retweetModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('payrolls.site.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="retweetModalLabel">Regenerate Payroll</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="site_id" value="{{ $site->id }}">
                    <input type="hidden" name="pay_type" value="daily">
                    <div class="form-group mb-3">
                        <label for="pay_type">Tipe Gaji</label>
                        <select name="pay_type" id="pay_type" class="form-control" onchange="toggleSalaryFields(this)">
                            <option value="daily" selected>Harian</option>
                            <option value="monthly">Bulanan</option>
                        </select>
                    </div>
                    <div class="form-group mb-3" id="salary_amount_field">
                        <label for="amount">Amount</label>
                        <input type="text" name="amount" id="amount" class="form-control" placeholder="Pendapatan" value="{{ old('salary_amount') }}">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light me-2" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Regenerasi Payroll</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="allowanceModal" tabindex="-1" aria-labelledby="allowanceModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('payrolls.allowance') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="allowanceModalLabel">Tunjangan Payroll</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="site_id" value="{{ $site->id }}">
                    <div class="form-group mb-3" id="name_field">
                        <label for="name">Nama Tunjangan</label>
                        <input type="text" name="name" id="name" class="form-control" placeholder="Masukkan Nama Tunjangan" value="{{ old('name') }}">
                    </div>
                    
                    <!-- Component Types List -->
                    <div class="mt-4">
                        <h6>Komponen</h6>
                        <div class="list-group">
                            @foreach($componentTypes as $componentType)
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    {{ $componentType->name }}
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light me-2" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Tambah</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="deductionModal" tabindex="-1" aria-labelledby="deductionModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('payrolls.deduction') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="deductionModalLabel">Pengurangan Payroll</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="site_id" value="{{ $site->id }}">
                    <div class="form-group mb-3" id="name_field">
                        <label for="name">Nama</label>
                        <input type="text" name="name" id="name" class="form-control" placeholder="Masukkan Nama Tunjangan" value="{{ old('name') }}">
                    </div>
                    
                    <!-- Component Types List -->
                    <div class="mt-4">
                        <h6>Tipe Pengurangan</h6>
                        <div class="list-group">
                            @foreach($deductionTypes as $deductionType)
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    {{ $deductionType->name }}
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light me-2" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Tambah</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bulk Update Modal -->
<div class="modal fade" id="bulkUpdateModal" tabindex="-1" aria-labelledby="bulkUpdateModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="bulkUpdateModalLabel">Bulk Update Karyawan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('payrolls.bulk-update') }}" method="POST" id="bulkUpdateForm">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info">
                        Update komponen gaji untuk semua karyawan yang dipilih. Kosongkan field yang tidak ingin diubah.
                    </div>
                    
                    <!-- Hidden input for selected payroll IDs -->
                    <div id="selected-payrolls-container"></div>
                    
                    <!-- Basic Info -->
                    <div class="form-group mb-3">
                        <label for="bulk_pay_type">Tipe Payslip</label>
                        <select name="pay_type" id="bulk_pay_type" class="form-control">
                            <option value="">- Tidak Diubah -</option>
                            <option value="monthly">Bulan</option>
                            <option value="daily">Harian</option>
                        </select>
                    </div>
                    <div class="form-group mb-3" id="update_amount_field">
                        <label for="amount">Amount</label>
                        <input type="text" name="amount" id="amount" class="form-control" placeholder="Masukkan Gaji Bulanan">
                    </div>
                    
                    <!-- BPJS Section -->
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">BPJS Settings</h6>
                        </div>
                        <div class="card-body">
                            <div class="form-group mb-3">
                                <label for="bulk_bpjs_base">Dasar Perhitungan BPJS</label>
                                <select name="bpjs_base_type" id="bulk_bpjs_base" class="form-control">
                                    <option value="">- Tidak Diubah -</option>
                                    <option value="amount_salary">Gaji Pokok (amount_salary)</option>
                                    <option value="salary_allowance">Gaji Pokok + Allowance Bulanan (salary_allowance)</option>
                                    <option value="base_budget">Base Budget (base_budget)</option>
                                </select>
                                <small class="text-muted">Pilih dasar penghitungan BPJS yang ingin diterapkan ke semua karyawan yang dipilih.</small>
                            </div>

                            <!-- BPJS Budget Fields -->
                            <div id="bulk-bpjs-budget-fields" class="d-none">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="bulk_bpjs_budget_tk">BPJS Budget TK</label>
                                        <input type="text" class="form-control" name="bpjs_budget_tk" id="bulk_bpjs_budget_tk" value="">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="bulk_bpjs_budget_kes">BPJS Budget KES</label>
                                        <input type="text" class="form-control" name="bpjs_budget_kes" id="bulk_bpjs_budget_kes" value="">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-check mb-3">
                                        <input class="form-check-input" type="radio" name="bpjs_type" id="bulk-bpjs-normatif" 
                                               value="normatif">
                                        <label class="form-check-label" for="bulk-bpjs-normatif">
                                            BPJS Normatif
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check mb-3">
                                        <input class="form-check-input" type="radio" name="bpjs_type" id="bulk-bpjs-unnormatif" 
                                               value="unnormatif">
                                        <label class="form-check-label" for="bulk-bpjs-unnormatif">
                                            BPJS Unnormatif
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check mb-3">
                                        <input class="form-check-input" type="radio" name="bpjs_type" id="bulk-bpjs-unchanged" 
                                               value="" checked>
                                        <label class="form-check-label" for="bulk-bpjs-unchanged">
                                            Tidak Diubah
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Normatif fields with checkboxes -->
                            <div id="bulk-bpjs-normatif-fields" class="d-none">
                                <div class="alert alert-info mb-3">
                                    Centang BPJS yang akan diaktifkan dengan nilai normatif standar.
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="bpjs_normatif[]" id="bulk-bpjs-jkk" 
                                                   value="jkk">
                                            <label class="form-check-label" for="bulk-bpjs-jkk">
                                                JKK (Jaminan Kecelakaan Kerja) - 0.24%
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="bpjs_normatif[]" id="bulk-bpjs-jkm" 
                                                   value="jkm">
                                            <label class="form-check-label" for="bulk-bpjs-jkm">
                                                JKM (Jaminan Kematian) - 0.30%
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="bpjs_normatif[]" id="bulk-bpjs-jht" 
                                                   value="jht">
                                            <label class="form-check-label" for="bulk-bpjs-jht">
                                                JHT (Jaminan Hari Tua) - 3.7% Perusahaan, 2% Karyawan
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="bpjs_normatif[]" id="bulk-bpjs-jp" 
                                                   value="jp">
                                            <label class="form-check-label" for="bulk-bpjs-jp">
                                                JP (Jaminan Pensiun) - 2% Perusahaan, 1% Karyawan
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="bpjs_normatif[]" id="bulk-bpjs-kes" 
                                                   value="kes">
                                            <label class="form-check-label" for="bulk-bpjs-kes">
                                                Kesehatan - 4% Perusahaan, 1% Karyawan
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Unnormatif fields (custom values) -->
                            <div id="bulk-bpjs-unnormatif-fields" class="d-none">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label>JKK (Perusahaan) %</label>
                                        <input type="text" name="jkk_company" class="form-control" value="">
                                    </div>
                                    <div class="col-md-6">
                                        <label>JKM (Perusahaan) %</label>
                                        <input type="text" name="jkm_company" class="form-control" value="">
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label>JHT (Perusahaan) %</label>
                                        <input type="text" name="jht_company" class="form-control" value="">
                                    </div>
                                    <div class="col-md-6">
                                        <label>JHT (Karyawan) %</label>
                                        <input type="text" name="jht_employee" class="form-control" value="">
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label>JP (Perusahaan) %</label>
                                        <input type="text" name="jp_company" class="form-control" value="">
                                    </div>
                                    <div class="col-md-6">
                                        <label>JP (Karyawan) %</label>
                                        <input type="text" name="jp_employee" class="form-control" value="">
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label>Kesehatan (Perusahaan) %</label>
                                        <input type="text" name="kes_company" class="form-control" value="">
                                    </div>
                                    <div class="col-md-6">
                                        <label>Kesehatan (Karyawan) %</label>
                                        <input type="text" name="kes_employee" class="form-control" value="">
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
                                <input class="form-check-input" type="checkbox" id="bulk-select-all-components" onchange="toggleAllBulkComponents()">
                                <label class="form-check-label" for="bulk-select-all-components">
                                    Pilih Semua
                                </label>
                            </div>
                        </div>
                        <div class="card-body">
                            @foreach ($componentTypes as $componentType)
                                <div class="form-group mb-3">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" name="bulk_component_checked[]" 
                                                    id="bulk-component-{{ $componentType->id }}" value="{{ $componentType->id }}"
                                                    onchange="toggleBulkComponentInput({{ $componentType->id }})">
                                                <label class="form-check-label" for="bulk-component-{{ $componentType->id }}">
                                                    {{ $componentType->name }}
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <input type="text" name="bulk_component_amount[{{ $componentType->id }}]" 
                                                    id="bulk-component-amount-{{ $componentType->id }}" 
                                                    class="form-control" placeholder="Masukkan Jumlah" disabled>
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
                                <input class="form-check-input" type="checkbox" id="bulk-select-all-deductions" onchange="toggleAllBulkDeductions()">
                                <label class="form-check-label" for="bulk-select-all-deductions">
                                    Pilih Semua
                                </label>
                            </div>
                        </div>
                        <div class="card-body">
                            @foreach ($deductionTypes as $deductionType)
                                <div class="form-group mb-3">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" name="bulk_deduction_checked[]" 
                                                    id="bulk-deduction-{{ $deductionType->id }}" value="{{ $deductionType->id }}"
                                                    onchange="toggleBulkDeductionInput({{ $deductionType->id }})">
                                                <label class="form-check-label" for="bulk-deduction-{{ $deductionType->id }}">
                                                    {{ $deductionType->name }}
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <input type="text" name="bulk_deduction_amount[{{ $deductionType->id }}]" 
                                                    id="bulk-deduction-amount-{{ $deductionType->id }}" 
                                                    class="form-control" placeholder="Masukkan Jumlah" disabled>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Time Deduction Section -->
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">Time Deduction (Potongan Waktu)</h6>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="bulk_time_deduction_late">Potongan Terlambat (Late)</label>
                                    <input type="text" name="bulk_time_deductions[late]" id="bulk_time_deduction_late" 
                                        class="form-control" placeholder="Masukkan jumlah potongan">
                                </div>
                                <div class="col-md-6">
                                    <label for="bulk_time_deduction_alpha">Potongan Alpha (Tidak Hadir)</label>
                                    <input type="text" name="bulk_time_deductions[alpha]" id="bulk_time_deduction_alpha" 
                                        class="form-control" placeholder="Masukkan jumlah potongan">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="bulk_time_deduction_permit">Potongan Izin (Permit)</label>
                                    <input type="text" name="bulk_time_deductions[permit]" id="bulk_time_deduction_permit" 
                                        class="form-control" placeholder="Masukkan jumlah potongan">
                                </div>
                                <div class="col-md-6">
                                    <label for="bulk_time_deduction_leave">Potongan Cuti (Leave)</label>
                                    <input type="text" name="bulk_time_deductions[leave]" id="bulk_time_deduction_leave" 
                                        class="form-control" placeholder="Masukkan jumlah potongan">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Overtime Section -->
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">Overtime (Lembur)</h6>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label for="bulk_overtime_pay_type">Tipe Pembayaran</label>
                                    <select name="bulk_overtime[pay_type]" id="bulk_overtime_pay_type" class="form-control">
                                        <option value="">- Tidak Diubah -</option>
                                        <option value="hourly">Per Jam</option>
                                        <option value="daily">Per Hari</option>
                                    </select>
                                </div>
                                <div class="col-md-8">
                                    <label for="bulk_overtime_amount">Jumlah Lembur</label>
                                    <input type="text" name="bulk_overtime[amount]" id="bulk_overtime_amount" 
                                        class="form-control" placeholder="Masukkan jumlah lembur">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">Metode PPH21</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="pph21_method" value="ter_gross">
                                        <label class="form-check-label">Gross</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="pph21_method" value="ter_gross_up">
                                        <label class="form-check-label">Gross Up</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light me-2" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="bulkUpdateSubmit">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>