{{--
    Modal konfirmasi notifikasi saat pindah status pelamar.

    Parameter:
      $notifyFormSelector : selector form pindah status (wajib), mis. '#form-bulk-update-status'
      $notifyContext      : [placeholder => nilai] untuk preview, mis. ['{nama}' => 'Budi'] (opsional)

    Saat form di-submit, modal ini muncul untuk memilih kanal (WhatsApp / Email) dan template.
    Pilihan dikirim sebagai notify_channels[] dan message_template_id.
--}}
@php
    $notifyTemplates = \App\Models\MessageTemplate::active()->orderBy('name')->get(['id', 'name', 'subject', 'body']);
@endphp

<div class="modal fade" id="notifyConfirmModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Konfirmasi Notifikasi ke Pelamar</h4>
                <button type="button" class="btn-close custom-btn-close" data-bs-dismiss="modal" aria-label="Close">
                    <i class="ti ti-x"></i>
                </button>
            </div>
            <div class="modal-body">
                <p class="mb-3">Status akan dipindahkan ke <strong id="notifyStatusName">-</strong>
                    <span id="notifyCount"></span>. Kirim notifikasi ke pelamar?</p>

                <label class="form-label fw-semibold">Kirim lewat</label>
                <div class="d-flex flex-wrap gap-3 mb-3">
                    <div class="form-check">
                        <input class="form-check-input notify-channel" type="checkbox" value="whatsapp" id="notifyViaWa" checked>
                        <label class="form-check-label" for="notifyViaWa"><i class="ti ti-brand-whatsapp text-success me-1"></i>WhatsApp</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input notify-channel" type="checkbox" value="email" id="notifyViaEmail">
                        <label class="form-check-label" for="notifyViaEmail"><i class="ti ti-mail text-primary me-1"></i>Email</label>
                    </div>
                </div>

                <div id="notifyTemplateWrap">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Template Pesan</label>
                        @if ($notifyTemplates->isEmpty())
                            <div class="alert alert-warning py-2 small mb-0">
                                Belum ada template aktif. Pesan default akan dipakai.
                                <a href="{{ route('message-templates.index') }}" target="_blank">Buat template</a>
                            </div>
                        @else
                            <select class="form-select" id="notifyTemplateSelect">
                                @foreach ($notifyTemplates as $tpl)
                                    <option value="{{ $tpl->id }}">{{ $tpl->name }}</option>
                                @endforeach
                            </select>
                            <div class="form-text">
                                Kelola template di menu <a href="{{ route('message-templates.index') }}" target="_blank">Template Pesan</a>.
                            </div>
                        @endif
                    </div>

                    @if ($notifyTemplates->isNotEmpty())
                        <label class="form-label fw-semibold mb-1">Preview</label>
                        <div class="small text-muted mb-1" id="notifySubjectPreview"></div>
                        <div class="rounded p-3" style="background:#E5DDD5;">
                            <div id="notifyBodyPreview" class="bg-white rounded p-2 shadow-sm small"
                                style="word-break:break-word; max-height:260px; overflow:auto;"></div>
                        </div>
                    @endif
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light me-auto" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-outline-secondary" id="notifySkipBtn">Simpan tanpa Notifikasi</button>
                <button type="button" class="btn btn-primary" id="notifySendBtn">
                    <i class="ti ti-send me-1"></i>Simpan &amp; Kirim
                </button>
            </div>
        </div>
    </div>
</div>

@include('admin.partials.message-template-preview')

@push('js')
<script>
    $(function () {
        var formSelector = @json($notifyFormSelector);
        var context = @json($notifyContext ?? []);
        var templates = @json($notifyTemplates->keyBy('id'));
        var modalEl = document.getElementById('notifyConfirmModal');
        var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        var pendingForm = null;

        function selectedStatusName(form) {
            var opt = $(form).find('select[name="status_id"] option:selected');
            return opt.length ? $.trim(opt.text().replace('(Aktif)', '')) : '';
        }

        function previewValues() {
            var values = $.extend({}, window.MessageTemplatePreview.SAMPLE, context);
            if (pendingForm) values['{status}'] = selectedStatusName(pendingForm) || values['{status}'];
            return values;
        }

        function renderPreview() {
            var tpl = templates[$('#notifyTemplateSelect').val()];
            if (!tpl) return;
            var P = window.MessageTemplatePreview, values = previewValues();
            $('#notifySubjectPreview').text('Subjek email: ' + P.fill(tpl.subject || 'Update Status Lamaran', values));
            $('#notifyBodyPreview').html(P.toHtml(P.fill(tpl.body, values)));
        }

        function syncChannels() {
            var any = $('.notify-channel:checked').length > 0;
            $('#notifyTemplateWrap').toggleClass('opacity-50', !any);
            $('#notifyTemplateSelect').prop('disabled', !any);
            $('#notifySendBtn').prop('disabled', !any);
        }

        $('#notifyTemplateSelect').on('change', renderPreview);
        $('.notify-channel').on('change', syncChannels);

        // Delegasi di document: berjalan SETELAH handler form milik halaman (mis. validasi
        // kandidat terpilih) dan SEBELUM popup loading global di layout.
        $(document).on('submit', formSelector, function (e) {
            if (e.isDefaultPrevented()) return;          // dibatalkan handler halaman
            if (this.dataset.notifyConfirmed === '1') {  // sudah dikonfirmasi -> lanjut submit
                this.dataset.notifyConfirmed = '';
                return;
            }

            e.preventDefault();
            e.stopImmediatePropagation();
            pendingForm = this;

            var count = $(this).find('input[name="applicant_ids[]"]').length;
            $('#notifyStatusName').text(selectedStatusName(this) || '-');
            $('#notifyCount').text(count ? 'untuk ' + count + ' kandidat' : '');

            // Tutup modal asal (mis. Bulk Update) agar tidak bertumpuk
            $('.modal.show').not(modalEl).modal('hide');
            renderPreview();
            syncChannels();
            modal.show();
        });

        function submitWith(channels) {
            if (!pendingForm) return;
            var $form = $(pendingForm);
            $form.find('.notify-hidden').remove();
            channels.forEach(function (ch) {
                $('<input>', { type: 'hidden', name: 'notify_channels[]', value: ch, 'class': 'notify-hidden' }).appendTo($form);
            });
            if (channels.length && $('#notifyTemplateSelect').val()) {
                $('<input>', { type: 'hidden', name: 'message_template_id', value: $('#notifyTemplateSelect').val(), 'class': 'notify-hidden' }).appendTo($form);
            }

            pendingForm.dataset.notifyConfirmed = '1';
            modal.hide();
            // requestSubmit memicu event submit lagi (handler halaman + popup loading global)
            if (pendingForm.requestSubmit) pendingForm.requestSubmit();
            else $form.trigger('submit');
        }

        $('#notifySendBtn').on('click', function () {
            submitWith($('.notify-channel:checked').map(function () { return this.value; }).get());
        });
        $('#notifySkipBtn').on('click', function () {
            submitWith([]);
        });
    });
</script>
@endpush
