<div class="header">
    <div class="main-header">
    
        <div class="header-left">
            <a href="{{ route('web.applicants.dashboard') }}" class="logo">
                <img src="/admin/assets/img/logo-dark.svg" alt="Logo" width="150">
            </a>
            <a href="{{ route('web.applicants.dashboard') }}" class="dark-logo">
                <img src="/admin/assets/img/logo-white.svg" alt="Logo">
            </a>
        </div>

        <a id="mobile_btn" class="mobile_btn" href="#sidebar">
            <span class="bar-icon">
                <span></span>
                <span></span>
                <span></span>
            </span>
        </a>

        <div class="header-user">
            <div class="nav user-menu nav-list">

                <div class="me-auto d-flex align-items-center" id="header-search">					
                    <a id="toggle_btn" href="javascript:void(0);" class="btn btn-menubar me-1">
                        <i class="ti ti-arrow-bar-to-left"></i>
                    </a>			
                </div>

                <div class="d-flex align-items-center">
                    <div class="me-1 notification_item">
                        <a href="#" class="btn btn-menubar position-relative me-1"
                            id="notification_popup" data-bs-toggle="dropdown">
                            <i class="ti ti-bell"></i>
                            <span class="notification-status-dot"></span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end notification-dropdown p-4">
                            <div class="d-flex align-items-center justify-content-between border-bottom p-0 pb-3 mb-3">
                                <h4 class="notification-title">Notifications (2)</h4>
                                <div class="d-flex align-items-center">
                                    <a href="#" class="text-primary fs-15 me-3 lh-1">Mark all as read</a>
                                    <div class="dropdown">
                                        <a href="javascript:void(0);" class="bg-white dropdown-toggle"
                                            data-bs-toggle="dropdown"><i class="ti ti-calendar-due me-1"></i>Today
                                        </a>
                                        <ul class="dropdown-menu mt-2 p-3">
                                            <li>
                                                <a href="javascript:void(0);" class="dropdown-item rounded-1">
                                                    This Week
                                                </a>
                                            </li>
                                            <li>
                                                <a href="javascript:void(0);" class="dropdown-item rounded-1">
                                                    Last Week
                                                </a>
                                            </li>
                                            <li>
                                                <a href="javascript:void(0);" class="dropdown-item rounded-1">
                                                    Last Month
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="noti-content">
                                <div class="d-flex flex-column">
                                    <div class="border-bottom mb-3 pb-3">
                                        <a href="activity.html">
                                            <div class="d-flex">
                                                <span class="avatar avatar-lg me-2 flex-shrink-0">
                                                    <img src="/admin/assets/img/profiles/avatar-27.jpg" alt="Profile">
                                                </span>
                                                <div class="flex-grow-1">
                                                    <p class="mb-1"><span class="text-dark fw-semibold">Shawn</span>
                                                        performance in Math is
                                                        below the threshold.</p>
                                                    <span>Just Now</span>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                    <div class="border-bottom mb-3 pb-3">
                                        <a href="activity.html" class="pb-0">
                                            <div class="d-flex">
                                                <span class="avatar avatar-lg me-2 flex-shrink-0">
                                                    <img src="/admin/assets/img/profiles/avatar-23.jpg" alt="Profile">
                                                </span>
                                                <div class="flex-grow-1">
                                                    <p class="mb-1"><span
                                                            class="text-dark fw-semibold">Sylvia</span> added
                                                        appointment on
                                                        02:00 PM</p>
                                                    <span>10 mins ago</span>
                                                    <div
                                                        class="d-flex justify-content-start align-items-center mt-1">
                                                        <span class="btn btn-light btn-sm me-2">Deny</span>
                                                        <span class="btn btn-primary btn-sm">Approve</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                    <div class="border-bottom mb-3 pb-3">
                                        <a href="activity.html">
                                            <div class="d-flex">
                                                <span class="avatar avatar-lg me-2 flex-shrink-0">
                                                    <img src="/admin/assets/img/profiles/avatar-25.jpg" alt="Profile">
                                                </span>
                                                <div class="flex-grow-1">
                                                    <p class="mb-1">New student record <span class="text-dark fw-semibold"> George</span> 
                                                        is created by <span class="text-dark fw-semibold">Teressa</span>
                                                    </p>
                                                    <span>2 hrs ago</span>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                    <div class="border-0 mb-3 pb-0">
                                        <a href="activity.html">
                                            <div class="d-flex">
                                                <span class="avatar avatar-lg me-2 flex-shrink-0">
                                                    <img src="/admin/assets/img/profiles/avatar-01.jpg" alt="Profile">
                                                </span>
                                                <div class="flex-grow-1">
                                                    <p class="mb-1">A new teacher record for <span
                                                            class="text-dark fw-semibold">Elisa</span>
                                                    </p>
                                                    <span>09:45 AM</span>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex p-0">
                                <a href="#" class="btn btn-light w-100 me-2">Cancel</a>
                                <a href="activity.html" class="btn btn-primary w-100">View All</a>
                            </div>
                        </div>
                    </div>
                    <div class="dropdown profile-dropdown">
                        <a href="javascript:void(0);" class="dropdown-toggle d-flex align-items-center"
                            data-bs-toggle="dropdown">
                            <span class="avatar avatar-sm online">
                                <img src="{{ Auth::user()->profile['avatar_url'] ?? '/assets/media/avatars/blank.png' }}" alt="Img" class="img-fluid rounded-circle">
                            </span>
                        </a>
                        <div class="dropdown-menu shadow-none">
                            <div class="card mb-0">
                                <div class="card-header">
                                    <div class="d-flex align-items-center">
                                        <span class="avatar avatar-lg me-2 avatar-rounded">
                                            <img src="{{ Auth::user()->profile['avatar_url'] ?? '/assets/media/avatars/blank.png' }}" alt="img">
                                        </span>
                                        <div>
                                            <h5 class="mb-0">{{ Auth::user()->name }}</h5>
                                            <p class="fs-12 fw-medium mb-0">{{ Auth::user()->email }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <a class="dropdown-item d-inline-flex align-items-center p-0 py-2" href="{{ route('applicants.profiles.index') }}">
                                        <i class="ti ti-user-circle me-1"></i>Kelola Profil
                                    </a>
                                    <a class="dropdown-item d-inline-flex align-items-center p-0 py-2" href="bussiness-settings.html">
                                        <i class="ti ti-settings me-1"></i>Pengaturan Akun
                                    </a>
                                </div>
                                <div class="card-footer py-1">
                                    <a class="dropdown-item d-inline-flex align-items-center p-0 py-2" href="{{ route('applicant-logout') }}"
                                    onclick="event.preventDefault();
                                                  document.getElementById('logout-form').submit();"><i
                                        class="ti ti-login me-2"></i>Keluar</a>
                                        <form id="logout-form" action="{{ route('applicant-logout') }}" method="POST" class="d-none">
                                            @csrf
                                        </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div class="dropdown mobile-user-menu">
            <a href="javascript:void(0);" class="nav-link dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fa fa-ellipsis-v"></i>
                <span class="notification-status-dot"></span>
            </a>
        
            <div class="dropdown-menu dropdown-menu-end">
                <a class="dropdown-item" href="profile.html">
                    Notification
                    <span class="badge badge-xs rounded-pill bg-danger">1</span>
                </a>
                <a class="dropdown-item" href="{{ route('applicants.profiles.index') }}">Kelola Profil</a>
                <a class="dropdown-item" href="{{ route('applicant-logout') }}"
                   onclick="event.preventDefault();
                            document.getElementById('logout-form').submit();">Keluar</a>
                <form id="logout-form" action="{{ route('applicant-logout') }}" method="POST" class="d-none">
                    @csrf
                </form>
            </div>
        </div>        
        <!-- /Mobile Menu -->

    </div>

</div>