@if($errors->any() || session('error') || session('success'))
<div class="modal fade" id="alertModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header {{ session('success') && !$errors->any() && !session('error') ? 'bg-success' : 'bg-danger' }}">
                <h5 class="modal-title text-white">{{ session('success') && !$errors->any() && !session('error') ? 'Berhasil' : 'Terjadi Kesalahan' }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li class="text-danger">{{ $error }}</li>
                    @endforeach
                    @if(session('error'))
                        <li class="text-danger">{{ session('error') }}</li>
                    @endif
                    @if(session('success') && !$errors->any() && !session('error'))
                        <li class="text-success">{{ session('success') }}</li>
                    @endif
                </ul>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
@push('js')
<script>
    $(document).ready(function() {
        new bootstrap.Modal(document.getElementById('alertModal')).show();
    });
</script>
@endpush
@endif
