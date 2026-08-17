<div class="dropdown">
    <button class="btn btn-primary btn-sm rounded-pill" type="button" id="dropdownMenuButton-{{ $row->id }}" data-bs-toggle="dropdown">
        Actions
    </button>
    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton-{{ $row->id }}">
        <li>
            <a href="{{ route('generates.show', $row->id) }}" class="dropdown-item">
                <i class="ti ti-file-text me-1"></i> Lihat Surat
            </a>
        </li>
        @if ($row->user)
            @php($applicant = $row->user->applicants()->latest('id')->first())
            @if ($applicant)
                <li>
                    <a href="{{ route('applicants.resume', $applicant->id) }}" target="_blank" class="dropdown-item">
                        <i class="ti ti-file-description me-1"></i> Lihat Resume
                    </a>
                </li>
            @endif
            <li>
                <a href="{{ route('users.account', $row->user_id) }}" target="_blank" class="dropdown-item">
                    <i class="ti ti-edit me-1"></i> Edit Profil
                </a>
            </li>
        @endif
    </ul>
</div>
