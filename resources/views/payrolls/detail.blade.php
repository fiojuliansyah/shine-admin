
You said:
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
                            Finance
                        </li>
                        <li class="breadcrumb-item">
                            Master Payroll
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">{{ $site->name }}</li>
                    </ol>
                </nav>
            </div>
            <div class="d-flex my-xl-auto right-content align-items-center flex-wrap ">
                @include('payrolls.partials.head-button')
            </div>
        </div>
        <div class="card">
            
            <div class="card-body">
                <div style="overflow-x: auto; width: 100%; -webkit-overflow-scrolling: touch; position: relative;">
                    <table class="table table-bordered" style="font-size: 12px; min-width: 1200px; border-collapse: separate; border-spacing: 0;">
                        <thead>
                            <tr style="position: sticky; top: 0; background-color: #f8f9fa;">
                                <th style="text-align: center; vertical-align: middle; width: 40px; white-space: nowrap;">
                                    <input class="form-check-input" type="checkbox" id="select-all-payrolls">
                                </th>
                                <th style="text-align: center; vertical-align: middle; width: 80px; white-space: nowrap;">NIK</th>
                                <th style="text-align: center; vertical-align: middle; width: 150px; white-space: nowrap;">Nama</th>
                                <th style="text-align: center; vertical-align: middle; width: 100px; white-space: nowrap;">Jabatan</th>
                                <th style="text-align: center; vertical-align: middle; width: 80px; white-space: nowrap;">Tipe</th>
                                <th style="text-align: center; vertical-align: middle; width: 100px; white-space: nowrap;">Gaji</th>
                                @foreach ($componentTypes as $componentType)   
                                    <th style="text-align: center; vertical-align: middle; width: 100px; white-space: nowrap;">{{ $componentType->name }}</th>
                                @endforeach
                                @foreach ($deductionTypes as $deductionType)   
                                    <th style="text-align: center; vertical-align: middle; width: 100px; white-space: nowrap;">{{ $deductionType->name }}</th>
                                @endforeach
                                <th style="text-align: center; vertical-align: middle; width: 200px; white-space: nowrap;">Potongan Waktu <span style="color: red">*jika</span></th>
                                <th style="text-align: center; vertical-align: middle; width: 100px; white-space: nowrap;">Lembur</th>
                                <th style="text-align: center; vertical-align: middle; width: 120px; white-space: nowrap;">BPJS</th>
                                <th style="text-align: center; vertical-align: middle; width: 80px; white-space: nowrap;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($payrolls as $payroll)
                                <tr>
                                    <td style="text-align: center; vertical-align: middle; white-space: nowrap;">
                                        <input class="form-check-input payroll-checkbox" type="checkbox" value="{{ $payroll->id }}" name="selected_payrolls[]">
                                    </td>
                                    <td style="text-align: center; vertical-align: middle; white-space: nowrap;">{{ $payroll->user->employee_nik }}</td>
                                    <td style="text-align: center; vertical-align: middle; white-space: nowrap;">{{ $payroll->user->name }}</td>
                                    <td style="text-align: center; vertical-align: middle; white-space: nowrap;">
                                        @foreach ($payroll->user->getRoleNames() as $role) 
                                            {{ $role }}
                                        @endforeach
                                    </td>
                                    <td style="text-align: center; vertical-align: middle; white-space: nowrap;">
                                        @if ($payroll->pay_type == 'monthly')
                                            Gaji Pokok   
                                        @else
                                            Gaji Harian
                                        @endif
                                    </td>
                                    <td style="text-align: center; vertical-align: middle; white-space: nowrap;">
                                            {{ number_format($payroll->amount, 0, ',', '.') }}
                                    </td>
                                    @foreach ($componentTypes as $componentType)
                                        <td style="text-align: center; vertical-align: middle; white-space: nowrap;">
                                            @if(isset($componentsData[$payroll->id][$componentType->id]))
                                                {{ number_format($componentsData[$payroll->id][$componentType->id], 0, ',', '.') }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                    @endforeach
                                    @foreach ($deductionTypes as $deductionType)
                                        <td style="text-align: center; vertical-align: middle; white-space: nowrap;">
                                            @if(isset($deductionsData[$payroll->id][$deductionType->id]))
                                                {{ number_format($deductionsData[$payroll->id][$deductionType->id], 0, ',', '.') }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                    @endforeach
                                    <td style="vertical-align: middle;">
                                        <div style="display: flex; flex-wrap: wrap; gap: 5px;">
                                            <div style="flex: 0 0 calc(50% - 5px);">
                                                <span style="font-weight: bold; font-size: 11px;">Terlambat</span>
                                                <br>
                                                <span style="font-size: 11px;">{{ number_format($timeDeductionsData[$payroll->id]['late'], 0, ',', '.') }}</span>
                                            </div>
                                            <div style="flex: 0 0 calc(50% - 5px);">
                                                <span style="font-weight: bold; font-size: 11px;">Alpha</span>
                                                <br>
                                                <span style="font-size: 11px;">{{ number_format($timeDeductionsData[$payroll->id]['alpha'], 0, ',', '.') }}</span>
                                            </div>
                                            <div style="flex: 0 0 calc(50% - 5px);">
                                                <span style="font-weight: bold; font-size: 11px;">Izin</span>
                                                <br>
                                                <span style="font-size: 11px;">{{ number_format($timeDeductionsData[$payroll->id]['permit'], 0, ',', '.') }}</span>
                                            </div>
                                            <div style="flex: 0 0 calc(50% - 5px);">
                                                <span style="font-weight: bold; font-size: 11px;">Cuti</span>
                                                <br>
                                                <span style="font-size: 11px;">{{ number_format($timeDeductionsData[$payroll->id]['leave'], 0, ',', '.') }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td style="text-align: center; vertical-align: middle; white-space: nowrap;">
                                        @if(isset($overtimeData[$payroll->id]) && $overtimeData[$payroll->id]['amount'])
                                            <span style="font-weight: bold; font-size: 11px;">{{ $overtimeData[$payroll->id]['pay_type'] == 'hourly' ? 'Per Jam' : 'Per Hari' }}</span>
                                            <br>
                                            <span style="font-size: 11px;">{{ number_format($overtimeData[$payroll->id]['amount'], 0, ',', '.') }}</span>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td style="vertical-align: middle;">
                                        <div style="display: flex; flex-wrap: wrap; gap: 5px;">
                                            <div style="flex: 0 0 calc(50% - 5px);">
                                                <span style="font-weight: bold; font-size: 11px;">Perusahaan</span>
                                                <br>
                                                @if ($payroll->jkk_company)
                                                    <span style="font-size: 11px;">JKK = {{ $payroll->jkk_company }}%</span>
                                                    <br>
                                                @endif
                                                @if ($payroll->jkm_company)
                                                    <span style="font-size: 11px;">JKM = {{ $payroll->jkm_company }}%</span>
                                                    <br>
                                                @endif
                                                @if ($payroll->jht_company)
                                                    <span style="font-size: 11px;">JHT = {{ $payroll->jht_company }}%</span>
                                                    <br>
                                                @endif
                                                @if ($payroll->jp_company)
                                                    <span style="font-size: 11px;">JP = {{ $payroll->jp_company }}%</span>
                                                    <br>
                                                @endif
                                                @if ($payroll->kes_company)
                                                    <span style="font-size: 11px;">KES = {{ $payroll->kes_company }}%</span>
                                                @endif
                                            </div>
                                            <div style="flex: 0 0 calc(50% - 5px);">
                                                <span style="font-weight: bold; font-size: 11px;">Pegawai</span>
                                                <br>
                                                @if ($payroll->jht_employee)
                                                    <span style="font-size: 11px;">JHT = {{ $payroll->jht_employee }}%</span>
                                                    <br>
                                                @endif
                                                @if ($payroll->jp_employee)
                                                    <span style="font-size: 11px;">JP = {{ $payroll->jp_employee }}%</span>
                                                    <br>
                                                @endif
                                                @if ($payroll->kes_employee)
                                                    <span style="font-size: 11px;">KES = {{ $payroll->kes_employee }}%</span>
                                                @endif
                                            </div>
                                        </div>                                    
                                    </td>
                                    <td style="text-align: center; vertical-align: middle; white-space: nowrap;">
                                        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#componentModal-{{ $payroll->id }}" style="font-size: 10px">
                                            Lihat
                                        </button>
                                    </td>
                                </tr>
                                @include('payrolls.partials.modals')
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <!-- Simple JavaScript to enhance the table responsiveness -->
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        // Handle select all checkboxes
                        document.getElementById('select-all-payrolls').addEventListener('change', function() {
                            var checkboxes = document.querySelectorAll('.payroll-checkbox');
                            for (var i = 0; i < checkboxes.length; i++) {
                                checkboxes[i].checked = this.checked;
                            }
                        });
                    });
                </script>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
    function toggleBulkBPJSBudgetFields() {
        const bpjsBase = document.getElementById("bulk_bpjs_base").value;
        const bpjsBudgetFields = document.getElementById("bulk-bpjs-budget-fields");

        if (bpjsBase === "base_budget") {
            bpjsBudgetFields.classList.remove("d-none");
        } else {
            bpjsBudgetFields.classList.add("d-none");
        }
    }

    document.addEventListener("DOMContentLoaded", function () {
        const bpjsBaseSelect = document.getElementById("bulk_bpjs_base");
        bpjsBaseSelect.addEventListener("change", toggleBulkBPJSBudgetFields);

        // Jalankan fungsi untuk memeriksa nilai saat halaman pertama kali dimuat
        toggleBulkBPJSBudgetFields();
    });

    function toggleBPJSBudgetFields(payrollId) {
        const bpjsBase = document.getElementById(bpjs_base-${payrollId}).value;
        const bpjsBudgetFields = document.getElementById(bpjs-budget-fields-${payrollId});

        if (bpjsBase === 'base_budget') {
            bpjsBudgetFields.classList.remove('d-none');
        } else {
            bpjsBudgetFields.classList.add('d-none');
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('[id^="bpjs_base-"]').forEach(function(select) {
            select.addEventListener('change', function() {
                const payrollId = this.id.split('-')[1];
                toggleBPJSBudgetFields(payrollId);
            });

            // Jalankan fungsi untuk memeriksa nilai saat halaman dimuat
            const payrollId = select.id.split('-')[1];
            toggleBPJSBudgetFields(payrollId);
        });
    });

    function toggleComponentInput(payrollId, componentTypeId) {
        const checkbox = document.getElementById(component-${payrollId}-${componentTypeId});
        const input = document.getElementById(component-amount-${payrollId}-${componentTypeId});
        
        input.disabled = !checkbox.checked;
        
        if (!checkbox.checked) {
            input.value = '';
        }
    }
    
    // Select all components for individual payroll
    function toggleAllComponents(payrollId) {
        const selectAllCheckbox = document.getElementById(select-all-${payrollId});
        const componentCheckboxes = document.querySelectorAll(input[id^="component-${payrollId}-"]);
        
        componentCheckboxes.forEach(function(checkbox) {
            checkbox.checked = selectAllCheckbox.checked;
            
            // Extract component ID from the checkbox ID
            const componentId = checkbox.id.split('-')[2];
            const input = document.getElementById(component-amount-${payrollId}-${componentId});
            
            input.disabled = !selectAllCheckbox.checked;
            
            if (!selectAllCheckbox.checked) {
                input.value = '';
            }
        });
    }
    
    // Toggle deduction input fields on individual payrolls
    function toggleDeductionInput(payrollId, deductionTypeId) {
        const checkbox = document.getElementById(deduction-${payrollId}-${deductionTypeId});
        const input = document.getElementById(deduction-amount-${payrollId}-${deductionTypeId});
        
        input.disabled = !checkbox.checked;
        
        if (!checkbox.checked) {
            input.value = '';
        }
    }
    
    // Select all deductions for individual payroll
    function toggleAllDeductions(payrollId) {
        const selectAllCheckbox = document.getElementById(select-all-deductions-${payrollId});
        const deductionCheckboxes = document.querySelectorAll(input[id^="deduction-${payrollId}-"]);
        
        deductionCheckboxes.forEach(function(checkbox) {
            checkbox.checked = selectAllCheckbox.checked;
            
            // Extract deduction ID from the checkbox ID
            const deductionId = checkbox.id.split('-')[2];
            const input = document.getElementById(deduction-amount-${payrollId}-${deductionId});
            
            input.disabled = !selectAllCheckbox.checked;
            
            if (!selectAllCheckbox.checked) {
                input.value = '';
            }
        });
    }
    
    // Toggle BPJS fields on individual payroll modals
    function toggleBPJSFields(payrollId, type) {
        const normatifFields = document.getElementById(bpjs-normatif-fields-${payrollId});
        const unnormatifFields = document.getElementById(bpjs-unnormatif-fields-${payrollId});
        
        if (type === 'normatif') {
            normatifFields.classList.remove('d-none');
            unnormatifFields.classList.add('d-none');
        } else {
            normatifFields.classList.add('d-none');
            unnormatifFields.classList.remove('d-none');
        }
    }
    
    // Toggle component input field in bulk update
    function toggleBulkComponentInput(componentTypeId) {
        const checkbox = document.getElementById(bulk-component-${componentTypeId});
        const input = document.getElementById(bulk-component-amount-${componentTypeId});
        
        input.disabled = !checkbox.checked;
        
        if (!checkbox.checked) {
            input.value = '';
        }
    }
    
    // Toggle deduction input field in bulk update
    function toggleBulkDeductionInput(deductionTypeId) {
        const checkbox = document.getElementById(bulk-deduction-${deductionTypeId});
        const input = document.getElementById(bulk-deduction-amount-${deductionTypeId});
        
        input.disabled = !checkbox.checked;
        
        if (!checkbox.checked) {
            input.value = '';
        }
    }
    
    // Select all components in bulk update - FIXED VERSION
    function toggleAllBulkComponents() {
        try {
            const selectAllCheckbox = document.getElementById('bulk-select-all-components');
            // Better selector targeting all bulk component checkboxes by name
            const componentCheckboxes = document.querySelectorAll('input[name="bulk_component_checked[]"]');
            
            console.log("Toggling all bulk components. Found: " + componentCheckboxes.length + " checkboxes");
            
            componentCheckboxes.forEach(function(checkbox) {
                checkbox.checked = selectAllCheckbox.checked;
                
                // Get component ID from the value attribute
                const componentId = checkbox.value;
                const input = document.getElementById(bulk-component-amount-${componentId});
                
                if (input) {
                    input.disabled = !selectAllCheckbox.checked;
                    
                    if (!selectAllCheckbox.checked) {
                        input.value = '';
                    }
                }
            });
        } catch (error) {
            console.error("Error in toggleAllBulkComponents:", error);
        }
    }
    
    // Select all deductions in bulk update
    function toggleAllBulkDeductions() {
        try {
            const selectAllCheckbox = document.getElementById('bulk-select-all-deductions');
            const deductionCheckboxes = document.querySelectorAll('input[name="bulk_deduction_checked[]"]');
            
            console.log("Toggling all bulk deductions. Found: " + deductionCheckboxes.length + " checkboxes");
            
            deductionCheckboxes.forEach(function(checkbox) {
                checkbox.checked = selectAllCheckbox.checked;
                
                // Get deduction ID from the value attribute
                const deductionId = checkbox.value;
                const input = document.getElementById(bulk-deduction-amount-${deductionId});
                
                if (input) {
                    input.disabled = !selectAllCheckbox.checked;
                    
                    if (!selectAllCheckbox.checked) {
                        input.value = '';
                    }
                }
            });
        } catch (error) {
            console.error("Error in toggleAllBulkDeductions:", error);
        }
    }
    
    // Document ready function to initialize all event handlers
    document.addEventListener('DOMContentLoaded', function() {
        // Select all checkbox for payrolls in table
        const selectAllPayrolls = document.getElementById('select-all-payrolls');
        if (selectAllPayrolls) {
            selectAllPayrolls.addEventListener('change', function() {
                const checkboxes = document.querySelectorAll('.payroll-checkbox');
                checkboxes.forEach(function(checkbox) {
                    checkbox.checked = selectAllPayrolls.checked;
                });
            });
        }
        
        // Toggle BPJS fields in bulk update modal
        const bpjsTypeRadios = document.querySelectorAll('input[name="bpjs_type"]');
        bpjsTypeRadios.forEach(function(radio) {
            radio.addEventListener('change', function() {
                const normatifFields = document.getElementById('bulk-bpjs-normatif-fields');
                const unnormatifFields = document.getElementById('bulk-bpjs-unnormatif-fields');
                
                if (this.value === 'normatif') {
                    normatifFields.classList.remove('d-none');
                    unnormatifFields.classList.add('d-none');
                } else if (this.value === 'unnormatif') {
                    normatifFields.classList.add('d-none');
                    unnormatifFields.classList.remove('d-none');
                } else {
                    normatifFields.classList.add('d-none');
                    unnormatifFields.classList.add('d-none');
                }
            });
        });
        
        // Bulk "Select All" checkbox in Components section
        const bulkSelectAllComponents = document.getElementById('bulk-select-all-components');
        if (bulkSelectAllComponents) {
            bulkSelectAllComponents.addEventListener('change', function() {
                // Call the function directly to avoid any issues with event binding
                toggleAllBulkComponents();
            });
        }
        
        // Bulk "Select All" checkbox in Deductions section
        const bulkSelectAllDeductions = document.getElementById('bulk-select-all-deductions');
        if (bulkSelectAllDeductions) {
            bulkSelectAllDeductions.addEventListener('change', function() {
                // Call the function directly to avoid any issues with event binding
                toggleAllBulkDeductions();
            });
        }
        
        // Form submission handler for bulk update
        const bulkUpdateForm = document.getElementById('bulkUpdateForm');
        if (bulkUpdateForm) {
            bulkUpdateForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Get all selected payroll IDs
                const selectedPayrolls = [];
                document.querySelectorAll('.payroll-checkbox:checked').forEach(function(checkbox) {
                    selectedPayrolls.push(checkbox.value);
                });
                
                if (selectedPayrolls.length === 0) {
                    alert('Pilih minimal satu karyawan terlebih dahulu!');
                    return;
                }
                
                // Clear previous inputs
                const container = document.getElementById('selected-payrolls-container');
                container.innerHTML = '';
                
                // Add hidden inputs for selected payrolls
                selectedPayrolls.forEach(function(id) {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'selected_payrolls[]';
                    input.value = id;
                    container.appendChild(input);
                });
                
                // Add loading state to submit button
                const submitButton = document.getElementById('bulkUpdateSubmit');
                submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...';
                submitButton.disabled = true;
                
                // Submit the form
                this.submit();
            });
        }
        
        // Handle bulk update modal opening
        const bulkUpdateModal = document.getElementById('bulkUpdateModal');
        if (bulkUpdateModal) {
            bulkUpdateModal.addEventListener('show.bs.modal', function(event) {
                // Check if any payrolls are selected
                const selectedCount = document.querySelectorAll('.payroll-checkbox:checked').length;
                if (selectedCount === 0) {
                    alert('Pilih minimal satu karyawan terlebih dahulu!');
                    event.preventDefault();
                }
            });
        }
        
        // Enable/disable the bulk update button based on selected checkboxes
        const payrollCheckboxes = document.querySelectorAll('.payroll-checkbox');
        if (payrollCheckboxes.length > 0) {
            payrollCheckboxes.forEach(function(checkbox) {
                checkbox.addEventListener('change', function() {
                    const selectedCount = document.querySelectorAll('.payroll-checkbox:checked').length;
                    const bulkUpdateButton = document.querySelector('[data-bs-target="#bulkUpdateModal"]');
                    
                    if (bulkUpdateButton) {
                        bulkUpdateButton.disabled = selectedCount === 0;
                    }
                });
            });
            
            // Initial state of bulk update button
            const initialSelectedCount = document.querySelectorAll('.payroll-checkbox:checked').length;
            const bulkUpdateButton = document.querySelector('[data-bs-target="#bulkUpdateModal"]');
            if (bulkUpdateButton) {
                bulkUpdateButton.disabled = initialSelectedCount === 0;
            }
        }
    });
</script>
@endpush