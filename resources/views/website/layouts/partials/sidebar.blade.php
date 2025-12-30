<div class="sidebar" id="sidebar">
    <!-- Logo -->
    <div class="sidebar-logo">
        <a href="{{ route('dashboard') }}" class="logo logo-normal">
            <img src="/admin/assets/img/logo-dark.svg" alt="Logo" width="150">
        </a>
        <a href="{{ route('dashboard') }}" class="logo-small">
            <img src="/admin/assets/img/logo-small.svg" alt="Logo">
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
                        <li class="{{ Route::is('web.applicants.dashboard') ? 'active' : '' }}">
                            <a href="{{ route('web.applicants.dashboard') }}">
                                <i class="ti ti-smart-home"></i><span>Dashboard</span>
                            </a>
                        </li>
                        <li class="{{ Route::is(['web.applicants.career','web.applicants.career.detail']) ? 'active' : '' }}">
                            <a href="{{ route('web.applicants.career') }}">
                                <i class="ti ti-briefcase"></i><span>Lowongan</span>
                            </a>
                        </li>
                        <li class="{{ Route::is('web.applicants.faq') ? 'active' : '' }}">
                            <a href="{{ route('web.applicants.faq') }}">
                                <i class="ti ti-help-octagon"></i><span>Pertanyaan</span>
                            </a>
                        </li>
                        <li class="{{ Route::is('web.applicants.history') ? 'active' : '' }}">
                            <a href="{{ route('web.applicants.history') }}">
                                <i class="ti ti-file-description"></i><span>Riwayat Lamaran</span>
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="menu-title"><span>DOKUMEN DIGITAL</span></li>
                <li>
                    <ul>
                        <li class="{{ Route::is('web.applicants.letter') ? 'active' : '' }}">
                            <a href="{{ route('web.applicants.letter') }}">
                                <i class="ti ti-edit"></i><span>Tanda Tangan</span>
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</div>