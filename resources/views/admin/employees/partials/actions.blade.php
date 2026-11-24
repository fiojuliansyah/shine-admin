<div class="dropdown">
    <button class="btn btn-primary btn-sm rounded-pill" type="button" id="employeeActions-{{ $row->id }}" data-bs-toggle="dropdown">
        Actions
    </button>
    <ul class="dropdown-menu" aria-labelledby="employeeActions-{{ $row->id }}">
        @if ($applicant)
            <li>
                <a href="{{ route('applicants.resume', $applicant->id) }}" target="_blank" class="dropdown-item">
                    <i class="ti ti-file-description me-1"></i> Lihat Resume
                </a>
            </li>
        @endif
        <li>
            <a href="{{ route('users.account', $row->id) }}" target="_blank" class="dropdown-item">
                <i class="ti ti-edit me-1"></i> Edit Profil
            </a>
        </li>
    </ul>
</div>
