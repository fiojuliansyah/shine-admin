{{--
    Format tanggal standar aplikasi: DD-MM-YYYY.
    Semua <input type="date"> ditampilkan sebagai DD-MM-YYYY (flatpickr altInput),
    sedangkan nilai yang dikirim ke server tetap YYYY-MM-DD sehingga backend tidak berubah.
    Tambahkan atribut data-native-date untuk memakai date picker bawaan browser.
--}}
<link rel="stylesheet" href="/admin/assets/plugins/flatpickr/flatpickr.min.css">
<script src="/admin/assets/plugins/flatpickr/flatpickr.min.js"></script>
<script src="/admin/assets/plugins/flatpickr/l10n/id.js"></script>
<script>
    (function () {
        function initDateInputs(root) {
            if (!window.flatpickr) return;

            (root || document).querySelectorAll('input[type="date"]:not([data-native-date])').forEach(function (el) {
                if (el._flatpickr) return;

                flatpickr(el, {
                    dateFormat: 'Y-m-d',
                    altInput: true,
                    altFormat: 'd-m-Y',
                    altInputClass: el.className,
                    allowInput: true,
                    disableMobile: true,
                    minDate: el.min || null,
                    maxDate: el.max || null,
                    locale: (flatpickr.l10ns && flatpickr.l10ns.id) || 'default',
                    parseDate: function (str) {
                        // Terima ketikan DD-MM-YYYY / DD/MM/YYYY maupun YYYY-MM-DD
                        var m = String(str).trim().match(/^(\d{1,2})[-\/.](\d{1,2})[-\/.](\d{4})$/);
                        if (m) return new Date(+m[3], +m[2] - 1, +m[1]);
                        m = String(str).trim().match(/^(\d{4})-(\d{1,2})-(\d{1,2})/);
                        if (m) return new Date(+m[1], +m[2] - 1, +m[3]);
                        return undefined;
                    },
                    onReady: function (selectedDates, dateStr, instance) {
                        if (instance.altInput) instance.altInput.placeholder = 'DD-MM-YYYY';
                    }
                });
            });
        }

        // $(input).val('2026-01-31') / .val('') pada input yang sudah memakai flatpickr ikut memperbarui tampilan
        if (window.jQuery) {
            var original = jQuery.valHooks.hidden || {};
            // jQuery memanggil hooks.get bila key "get" ada, jadi jangan buat key get kosong.
            var hook = jQuery.extend({}, original);
            hook.set = function (elem, value) {
                if (elem._flatpickr) {
                    value ? elem._flatpickr.setDate(value, false, 'Y-m-d') : elem._flatpickr.clear(false);
                    return true;
                }
                return original.set ? original.set(elem, value) : undefined;
            };
            jQuery.valHooks.hidden = hook;
        }

        window.initDateInputs = initDateInputs;
        document.readyState === 'loading'
            ? document.addEventListener('DOMContentLoaded', function () { initDateInputs(); })
            : initDateInputs();
    })();
</script>
