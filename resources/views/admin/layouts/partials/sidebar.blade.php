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
                <li class="menu-title"><span>MASTER DATA</span></li>
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
                        <li class="{{ Route::is(['employees.index', 'user-account']) ? 'active' : '' }}">
                            <a href="{{ route('employees.index') }}">
                                <i class="ti ti-users"></i><span>Admin</span>
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
                                <i class="ti ti-edit"></i><span>Template SUrat</span>
                                <span class="menu-arrow"></span>
                            </a>
                            <ul>
                                <li>
                                    <a href="{{ route('type_letters.index') }}"
                                        class="{{ Route::is('type_letters.index') ? 'active' : ''    }}">Konfigurasi No Surat</a>
                                </li>
                                <li><a href="{{ route('letters.index') }}"
                                        class="{{ Route::is(['letters.index']) ? 'active' : '' }}">Buat
                                        Template Surat</a></li>
                            </ul>
                        </li>
                        <li class="{{ Route::is('statuses.index') ? 'active' : '' }}">
                            <a href="{{ route('statuses.index') }}">
                                <i class="ti ti-timeline-event-text"></i><span>Tingkatan</span>
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="menu-title"><span>DOKUMEN DIGITAL</span></li>
                <li>
                    <ul>
                        <li class="{{ Route::is('generate.index') ? 'active' : '' }}">
                            <a href="{{ route('generate.index') }}">
                                <i class="ti ti ti-edit"></i><span>Surat Terbit</span>
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="menu-title"><span>PROSES RECRUITMENT</span></li>
                <li>
                    <ul>
                        <li class="{{ Route::is('careers.index') ? 'active' : '' }}">
                            <a href="{{ route('careers.index') }}">
                                <i class="ti ti-timeline"></i><span>Buka Lowongan</span>
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
                    </ul>
                </li>
                <li>
                    <ul>
                        <li class="{{ Route::is('whatsapp.config') ? 'active' : '' }}">
                            <a href="{{ route('whatsapp.config') }}">
                                <i class="ti ti-smart-home"></i><span>Whatsapp Configuration</span>
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</div>
