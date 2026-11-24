@extends('admin.layouts.main')

@section('content')
<div class="page-wrapper">
    <div class="content">

        <!-- Breadcrumb -->
        <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
            <div class="my-auto mb-2">
                <h2 class="mb-1">Template Pesan</h2>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item">
                            <a href="{{ route('dashboard') }}"><i class="ti ti-smart-home"></i></a>
                        </li>
                        <li class="breadcrumb-item">E-Recruitment</li>
                        <li class="breadcrumb-item active" aria-current="page">Template Pesan</li>
                    </ol>
                </nav>
            </div>
            <div class="d-flex my-xl-auto right-content align-items-center flex-wrap">
                <div class="mb-2">
                    <button type="button" class="btn btn-primary d-flex align-items-center btn-template-create">
                        <i class="ti ti-circle-plus me-2"></i>Tambah Template
                    </button>
                </div>
            </div>
        </div>
        <!-- /Breadcrumb -->

        <div class="alert alert-info small">
            Template dipakai saat memindahkan status pelamar. Setelah klik simpan status, akan muncul konfirmasi untuk
            memilih kirim notifikasi lewat <strong>WhatsApp</strong> dan/atau <strong>Email</strong> beserta template-nya.
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">List Template Pesan</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th style="width:50px;">No</th>
                                <th>Nama Template</th>
                                <th>Subjek Email</th>
                                <th>Isi Pesan</th>
                                <th class="text-center">Status</th>
                                <th class="text-center" style="width:120px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($templates as $template)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td class="fw-medium">{{ $template->name }}</td>
                                    <td>{{ $template->subject ?: '-' }}</td>
                                    <td class="text-muted small" style="max-width:380px; white-space:normal;">
                                        {{ \Illuminate\Support\Str::limit($template->body, 120) }}
                                    </td>
                                    <td class="text-center">
                                        @if ($template->is_active)
                                            <span class="badge bg-success">Aktif</span>
                                        @else
                                            <span class="badge bg-secondary">Nonaktif</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="d-inline-flex gap-1">
                                            <button type="button" class="btn btn-sm btn-white btn-template-edit"
                                                data-template="{{ json_encode($template->only(['id', 'name', 'subject', 'body', 'is_active'])) }}"
                                                data-action="{{ route('message-templates.update', $template) }}">
                                                <i class="ti ti-edit"></i>
                                            </button>
                                            <form action="{{ route('message-templates.destroy', $template) }}" method="POST"
                                                onsubmit="return confirm('Hapus template {{ addslashes($template->name) }}?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-white text-danger">
                                                    <i class="ti ti-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">Belum ada template pesan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah / Edit Template -->
<div class="modal fade" id="templateFormModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <form id="templateForm" action="{{ route('message-templates.store') }}" method="POST">
                @csrf
                <input type="hidden" name="_method" id="templateFormMethod" value="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="templateFormTitle">Tambah Template</h5>
                    <button type="button" class="btn-close custom-btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="ti ti-x"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-lg-7">
                            <div class="mb-3">
                                <label class="form-label">Nama Template</label>
                                <input type="text" class="form-control" name="name" id="tplName" required
                                    placeholder="Contoh: Undangan Interview">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Subjek Email <span class="text-muted small">(khusus Email)</span></label>
                                <input type="text" class="form-control" name="subject" id="tplSubject"
                                    placeholder="Contoh: Update Status Lamaran - {status}">
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Isi Pesan</label>
                                <textarea class="form-control font-monospace" name="body" id="tplBody" rows="12" required
                                    style="font-size:13px;"></textarea>
                                <div class="form-text">Format WhatsApp: <code>*tebal*</code>, <code>_miring_</code>, <code>~coret~</code>. Format ini juga diterapkan di Email.</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small text-muted mb-1">Klik variabel untuk menyisipkan:</label>
                                <div class="d-flex flex-wrap gap-1">
                                    @foreach ($placeholders as $key => $label)
                                        <button type="button" class="btn btn-sm btn-outline-primary tpl-placeholder"
                                            data-value="{{ $key }}" title="{{ $label }}">{{ $key }}</button>
                                    @endforeach
                                </div>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="tplActive" checked>
                                <label class="form-check-label" for="tplActive">Aktif (muncul di pilihan template)</label>
                            </div>
                        </div>
                        <div class="col-lg-5">
                            <label class="form-label">Preview WhatsApp</label>
                            <div class="rounded p-3" style="background:#E5DDD5; min-height:300px;">
                                <div id="tplPreview" class="bg-white rounded p-2 shadow-sm small"
                                    style="white-space:normal; word-break:break-word; max-width:95%;"></div>
                            </div>
                            <div class="form-text">Variabel diganti contoh data pada preview.</div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light me-2" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

@include('admin.partials.message-template-preview')
@endsection

@push('js')
<script>
    $(function () {
        var modalEl = document.getElementById('templateFormModal');
        var modal = new bootstrap.Modal(modalEl);
        var $form = $('#templateForm');
        var $body = $('#tplBody');

        function renderPreview() {
            $('#tplPreview').html(window.MessageTemplatePreview.toHtml(
                window.MessageTemplatePreview.fill($body.val(), window.MessageTemplatePreview.SAMPLE)
            ));
        }
        $body.on('input', renderPreview);

        $('.btn-template-create').on('click', function () {
            $form.attr('action', @json(route('message-templates.store')));
            $('#templateFormMethod').val('POST');
            $('#templateFormTitle').text('Tambah Template');
            $('#tplName, #tplSubject, #tplBody').val('');
            $('#tplActive').prop('checked', true);
            renderPreview();
            modal.show();
        });

        $('.btn-template-edit').on('click', function () {
            var t = $(this).data('template');
            $form.attr('action', $(this).data('action'));
            $('#templateFormMethod').val('PUT');
            $('#templateFormTitle').text('Edit Template');
            $('#tplName').val(t.name);
            $('#tplSubject').val(t.subject || '');
            $body.val(t.body);
            $('#tplActive').prop('checked', !!t.is_active);
            renderPreview();
            modal.show();
        });

        // Sisipkan variabel di posisi kursor
        $('.tpl-placeholder').on('click', function () {
            var el = $body[0];
            var value = $(this).data('value');
            var start = el.selectionStart, end = el.selectionEnd;
            el.value = el.value.slice(0, start) + value + el.value.slice(end);
            el.focus();
            el.selectionStart = el.selectionEnd = start + value.length;
            renderPreview();
        });

        @if ($errors->any())
            alert({!! json_encode(implode("\n", $errors->all()), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!});
        @endif
    });
</script>
@endpush
