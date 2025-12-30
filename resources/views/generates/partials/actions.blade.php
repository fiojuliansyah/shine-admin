<div class="dropdown">
    <button class="btn btn-primary btn-sm rounded-pill" type="button" id="dropdownMenuButton-{{ $row->id }}" data-bs-toggle="dropdown">
        Actions
    </button>
    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton-{{ $row->id }}">
        <li>
            <!-- Edit Button -->
            <a href="{{ route('generates.show', $row->id) }}" class="dropdown-item">Lihat</a>
        </li>
    </ul>
</div>