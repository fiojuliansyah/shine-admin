<div class="sidebar" id="sidebar">
    <!-- Logo -->
    <div class="sidebar-logo">
        <a href="{{ route('dashboard') }}" class="logo logo-normal">
            <img src="/admin/assets/img/logo-dark-ciptakarir.svg" alt="Logo" width="50">
        </a>
        <a href="{{ route('dashboard') }}" class="logo-small">
            <img src="/admin/assets/img/logo-dark-ciptakarir.svg" alt="Logo" width="50">
        </a>
        <a href="{{ route('dashboard') }}" class="dark-logo">
            <img src="/admin/assets/img/logo-dark-ciptakarir.svg" alt="Logo">
        </a>
    </div>
    <div class="sidebar-inner slimscroll">
        <div id="sidebar-menu" class="sidebar-menu">
            <ul>
                <li class="menu-title"><span>MAIN MENU</span></li>
                <li>
                    <ul>
                        <li class="{{ Route::is('dashboard') ? 'active' : '' }}">
                            <a href="{{ route('dashboard') }}">
                                <i class="ti ti-smart-home"></i><span>Dashboard</span>
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="menu-title"><span>CRM</span></li>
                <li>
                    <ul>
                        <li class="{{ Route::is('companies.index') ? 'active' : '' }}">
                            <a href="{{ route('companies.index') }}">
                                <i class="ti ti-building"></i><span>Perusahaan</span>
                            </a>
                        </li>
                        <li class="{{ Route::is('sites.index') ? 'active' : '' }}">
                            <a href="{{ route('sites.index') }}">
                                <i class="ti ti-box"></i><span>Site Project</span>
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="menu-title"><span>HRM</span></li>
                <li>
                    <ul>
                        <li class="{{ Route::is(['employees.index', 'user-account']) ? 'active' : '' }}">
                            <a href="{{ route('employees.index') }}">
                                <i class="ti ti-users"></i><span>Pegawai</span>
                            </a>
                        </li>
                        <li class="{{ Route::is(['roles.index']) ? 'active' : '' }}">
                            <a href="{{ route('roles.index') }}">
                                <i class="ti ti-shield"></i><span>Jabatan</span>
                            </a>
                        </li>
                        <li class="submenu">
                            <a href="javascript:void(0);"
                                class="{{ Route::is(['generates.index', 'type_letters.index', 'letters.index']) ? 'active subdrop' : '' }}"">
                                <i class="ti ti-edit"></i><span>Digital Letter</span>
                                <span class="menu-arrow"></span>
                            </a>
                            <ul>
                                <li>
                                    <a href="{{ route('type_letters.index') }}"
                                        class="{{ Route::is('type_letters.index') ? 'active' : ''    }}">Konfigurasi No Surat</a>
                                </li>
                                <li><a href="{{ route('generates.index') }}"
                                        class="{{ Route::is('generates.index') ? 'active' : '' }}">Buat Surat</a></li>
                                <li><a href="{{ route('letters.index') }}"
                                        class="{{ Route::is(['letters.index']) ? 'active' : '' }}">Buat
                                        Template Surat</a></li>
                            </ul>
                        </li>
                    </ul>
                </li>
                <li class="menu-title"><span>FINANCE & ACCOUNTS</span></li>
                <li>
                    <ul>
                        <li class="submenu">
                            <a href="javascript:void(0);"
                                class="{{ Route::is(['payrolls.main', 'payrolls.generate', 'payroll.generateDetail', 'payrolls.overtime']) ? 'active subdrop' : '' }}">
                                <i class="ti ti-cash"></i><span>Payroll</span>
                                <span class="menu-arrow"></span>
                            </a>
                            <ul>
                                <li><a href="{{ route('payrolls.main') }}"
                                        class="{{ Route::is('payrolls.main') ? 'active' : '' }}">Master</a></li>
                                <li><a href="{{ route('payrolls.generate') }}"
                                        class="{{ Route::is(['payrolls.generate', 'payroll.generateDetail']) ? 'active' : '' }}">Generate
                                        Payroll</a></li>
                            </ul>
                        </li>
                    </ul>
                </li>
                <li class="menu-title"><span>RECRUITMENT</span></li>
                <li>
                    <ul>
                        <li class="{{ Route::is('careers.index') ? 'active' : '' }}">
                            <a href="{{ route('careers.index') }}">
                                <i class="ti ti-timeline"></i><span>Lowongan</span>
                            </a>
                        </li>
                        <li class="submenu">
                            <a href="javascript:void(0);"
                                class="{{ Route::is(['applicants.index', 'statuses.show']) ? 'active subdrop' : '' }}"">
                                <i class="ti ti-box"></i><span>Pelamar</span>
                                <span class="menu-arrow"></span>
                            </a>
                            <ul>
                                <li>
                                    <a href="{{ route('applicants.index') }}"
                                        class="{{ Route::is('applicants.index') ? 'active' : '' }}">Pemberkasan
                                        @if ($pendingApplicants && $pendingApplicants->count() > 0)
                                            <span class="badge badge-xs rounded-pill bg-danger"
                                                style="color: white">{{ $pendingApplicants->count() }}</span>
                                        @endif
                                    </a>
                                </li>
                                @foreach ($statuses as $status)
                                    <li>
                                        <a href="{{ route('statuses.show', $status->slug) }}"
                                            class="{{ Request::is('manage/statuses/' . $status->slug) ? 'active' : '' }}">
                                            {{ $status->name }}

                                            @php
                                                $pendingCount = $status->applicants()->whereNull('done')->count();
                                            @endphp

                                            @if ($pendingCount > 0)
                                                <span class="badge badge-xs rounded-pill bg-danger"
                                                    style="color: white">
                                                    {{ $pendingCount }}
                                                </span>
                                            @endif
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </li>
                        <li class="{{ Route::is('statuses.index') ? 'active' : '' }}">
                            <a href="{{ route('statuses.index') }}">
                                <i class="ti ti-timeline-event-text"></i><span>Tingkatan</span>
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</div>
