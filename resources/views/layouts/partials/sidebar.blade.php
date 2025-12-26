<div class="sidebar" id="sidebar">
    <!-- Logo -->
    <div class="sidebar-logo">
        <a href="{{ route('dashboard') }}" class="logo logo-normal">
            <img src="/admin/assets/img/logo-dark.svg" alt="Logo" width="50">
        </a>
        <a href="{{ route('dashboard') }}" class="logo-small">
            <img src="/admin/assets/img/logo-dark.svg" alt="Logo" width="50">
        </a>
        <a href="{{ route('dashboard') }}" class="dark-logo">
            <img src="/admin/assets/img/logo-white.svg" alt="Logo">
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
                        <li class="{{ Route::is('floors.*') ? 'active' : '' }}">
                            <a href="{{ route('floors.index') }}">
                                <i class="ti ti-stairs"></i><span>Lantai</span>
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
                                <li><a href="{{ route('generates.index') }}"
                                        class="{{ Route::is('generates.index') ? 'active' : '' }}">Buat Letter</a></li>
                                <li><a href="{{ route('letters.index') }}"
                                        class="{{ Route::is(['letters.index', 'type_letters.index']) ? 'active' : '' }}">Buat
                                        Template</a></li>
                            </ul>
                        </li>
                        <li class="{{ Route::is(['schedules.index', 'schedules.show']) ? 'active' : '' }}">
                            <a href="{{ route('schedules.index') }}">
                                <i class="ti ti-ticket"></i><span>Jadwal</span>
                            </a>
                        </li>
                        <li class="{{ Route::is(['attendances.index']) ? 'active' : '' }}">
                            <a href="{{ route('attendances.index') }}">
                                <i class="ti ti-file-time"></i><span>Data Absensi</span>
                            </a>
                        </li>
                        <li class="{{ Route::is(['overtimes.index']) ? 'active' : '' }}">
                            <a href="{{ route('overtimes.index') }}">
                                <i class="ti ti-clock"></i><span>Data Lembur</span>
                            </a>
                        </li>
                        <li class="{{ Route::is(['minutes.index']) ? 'active' : '' }}">
                            <a href="{{ route('minutes.index') }}">
                                <i class="ti ti-file-description"></i><span>Berita Acara</span>
                            </a>
                        </li>
                        <li class="submenu submenu-two">
                            <a href="javascript:void(0);"
                                class="{{ Route::is(['leaves.index', 'types.index']) ? 'active subdrop' : '' }}"">
                                <i class="ti ti-edit"></i><span>Cuti</span>
                                <span class="menu-arrow"></span>
                            </a>
                            <ul>
                                <li><a href="{{ route('leaves.index') }}"
                                        class="{{ Route::is('leaves.index') ? 'active' : '' }}">Data Cuti</a></li>
                                <li><a href="{{ route('types.index') }}"
                                        class="{{ Route::is('types.index') ? 'active' : '' }}">Pengaturan Cuti</a></li>
                            </ul>
                        </li>
                        <li class="{{ Route::is(['permits.index']) ? 'active' : '' }}">
                            <a href="{{ route('permits.index') }}">
                                <i class="ti ti-file-description"></i><span>Data Izin</span>
                            </a>
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
                                <li><a href="{{ route('payrolls.overtime') }}"
                                        class="{{ Route::is('payrolls.overtime') ? 'active' : '' }}">Pengajuan
                                        Lembur</a></li>
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
                                <i class="ti ti-box"></i><span>Kandidat</span>
                                <span class="menu-arrow"></span>
                            </a>
                            <ul>
                                <li><a href="{{ route('applicants.index') }}"
                                        class="{{ Route::is('applicants.index') ? 'active' : '' }}">Pemberkasan</a>
                                </li>
                                @foreach ($statuses as $status)
                                    <li>
                                        <a href="{{ route('statuses.show', $status->slug) }}"
                                            class="{{ Request::is('manage/statuses/' . $status->slug) ? 'active' : '' }}">
                                            {{ $status->name }}
                                            @if ($status->unapprovedApplicants() && $status->unapprovedApplicants()->count() > 0)
                                                <span class="badge badge-xs rounded-pill bg-danger"
                                                    style="color: white">{{ $status->unapprovedApplicants()->count() }}</span>
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
                <li class="menu-title"><span>PRODUCTIVITY</span>
                </li>
                <li>
                    <ul>
                        <li class="{{ Route::is(['tasks.index', 'tasks.show']) ? 'active' : '' }}">
                            <a href="{{ route('tasks.index') }}">
                                <i class="ti ti-users"></i><span>Task Planner</span>
                            </a>
                        </li>
                        <li class="{{ Route::is('securty-patroll.*') ? 'active' : '' }}">
                            <a href="{{ route('securty-patroll.index') }}">
                                <i class="ti ti-users"></i><span>Securty Patroll</span>
                            </a>
                        </li>
                        <li class="{{ Route::is(['jobdesk-patrolls.*']) ? 'active' : '' }}">
                            <a href="{{ route('jobdesk-patrolls.index') }}">
                                <i class="ti ti-briefcase"></i><span>Jobdesk Patroll</span>
                            </a>
                        </li>
                        {{-- <li class="{{ Route::is(['valets.index']) ? 'active' : '' }}">
                            <a href="{{ route('valets.index') }}">
                                <i class="ti ti-car"></i><span>Valet Parking</span>
                            </a>
                        </li> --}}
                    </ul>
                </li>
                <li class="menu-title"><span>REPORTS</span></li>
                <li>
                    <ul>
                        <li class="{{ Route::is('attendance.report') ? 'active' : '' }}">
                            <a href="{{ route('attendance.report') }}">
                                <i class="ti ti-file-description"></i><span>Reports</span>
                            </a>
                        </li>
                        <li class="{{ Route::is('findingReport.index') ? 'active' : '' }}">
                            <a href="{{ route('findingReport.index') }}">
                                <i class="ti ti-file-description"></i><span>Report Temuan</span>
                            </a>
                        </li>
                        <li class="{{ Route::is('dailyReport.index') ? 'active' : '' }}">
                            <a href="{{ route('dailyReport.index') }}">
                                <i class="ti ti-file-description"></i><span>Report Harian</span>
                            </a>
                        </li>
                        <li class="{{ Route::is('patrollReport.index') ? 'active' : '' }}">
                            <a href="{{ route('patrollReport.index') }}">
                                <i class="ti ti-file-description"></i><span>Report Patroli</span>
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</div>
