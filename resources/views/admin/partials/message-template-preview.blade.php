{{-- Helper JS bersama untuk preview template pesan (isi variabel + format WhatsApp ke HTML). --}}
@once
    @push('js')
    <script>
        window.MessageTemplatePreview = {
            SAMPLE: {
                '{nama}': 'Budi Santoso',
                '{posisi}': 'Security Officer',
                '{status}': 'Interview',
                '{email}': 'budi@email.com',
                '{no_hp}': '081234567890',
                '{nik_karyawan}': '2026110001',
                '{site}': 'Gedung Utama',
                '{perusahaan}': 'PT Contoh',
                '{tanggal}': new Date().toLocaleDateString('id-ID').replace(/\//g, '-')
            },
            fill: function (text, values) {
                return Object.keys(values).reduce(function (acc, key) {
                    return acc.split(key).join(values[key]);
                }, text || '');
            },
            escape: function (text) {
                return String(text)
                    .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;').replace(/'/g, '&#039;');
            },
            // Sama dengan ApplicantStatusNotification::whatsappToHtml()
            toHtml: function (text) {
                return this.escape(text)
                    .replace(/\*(\S(?:[^*\n]*\S)?)\*/g, '<strong>$1</strong>')
                    .replace(/(^|[^\w])_(\S(?:[^_\n]*\S)?)_(?![\w])/g, '$1<em>$2</em>')
                    .replace(/~(\S(?:[^~\n]*\S)?)~/g, '<s>$1</s>')
                    .replace(/\n/g, '<br>');
            }
        };
    </script>
    @endpush
@endonce
