<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
	<meta name="description" content="Cipta Karir">
	<meta name="keywords" content="admin, estimates, bootstrap, business, html5, responsive, Projects">
	<meta name="author" content="Dreams technologies - Bootstrap Admin Template">
	<meta name="robots" content="noindex, nofollow">
	<meta name="csrf-token" content="{{ csrf_token() }}">
	<title>Cipta Karir</title>

	<!-- Favicon -->
	<link rel="shortcut icon" type="image/x-icon" href="/admin/assets/img/favicon-ciptakarir.png">

	<!-- Apple Touch Icon -->
	<link rel="apple-touch-icon" sizes="180x180" href="/admin/assets/img/apple-touch-icon-ciptakarir.png">

	<!-- Bootstrap CSS -->
	<link rel="stylesheet" href="/admin/assets/css/bootstrap.min.css">

	<!-- Feather CSS -->
	<link rel="stylesheet" href="/admin/assets/plugins/icons/feather/feather.css">

	<!-- Tabler Icon CSS -->
	<link rel="stylesheet" href="/admin/assets/plugins/tabler-icons/tabler-icons.css">

	<link rel="stylesheet" href="/admin/assets/plugins/select2/css/select2.min.css">

	<!-- Fontawesome CSS -->
	<link rel="stylesheet" href="/admin/assets/plugins/fontawesome/css/fontawesome.min.css">
	<link rel="stylesheet" href="/admin/assets/plugins/fontawesome/css/all.min.css">

	{{-- lightbox img --}}
	<link href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/css/lightbox.min.css" rel="stylesheet" />

    @stack('css')
	<style>
		.notification-container {
			position: fixed; 
			top: 20px; 
			right: 20px; 
			z-index: 9999; 
			width: 350px;
			max-width: 90%;
		}

		@media (max-width: 576px) {
			.notification-container {
				top: 10px;
				right: 5%;
				left: 5%;
				width: 90%;
			}
		}
	</style>
	<!-- Main CSS -->
	<link rel="stylesheet" href="/admin/assets/css/style.css">

</head>

<body data-layout="detached">

	<div id="global-loader" style="display: none;">
		<div class="page-loader"></div>
	</div>

	<!-- Main Wrapper -->
	<div class="main-wrapper">

		<div class="notification-container">
			@if(session('success'))
				<div id="successAlert" class="card border-0 shadow-lg mb-2">
					<div class="alert alert-primary border border-primary mb-0 p-3">
						<div class="d-flex align-items-start">
							<div class="me-2"><i class="feather-info flex-shrink-0"></i></div>
							<div class="text-primary w-100">
								<div class="fw-semibold d-flex justify-content-between">
									Success
									<button type="button" class="btn-close p-0" data-bs-dismiss="alert" aria-label="Close"><i class="fas fa-xmark"></i></button>
								</div>
								<div class="fs-12 op-8">{{ session('success') }}</div>
							</div>
						</div>
					</div>
				</div>
				<script>
					setTimeout(() => { $('#successAlert').fadeOut(); }, 3000);
				</script>
			@endif

			@if(session('error'))
				<div id="errorAlert" class="card border-0 shadow-lg mb-2">
					<div class="alert alert-danger border border-danger mb-0 p-3">
						<div class="d-flex align-items-start">
							<div class="me-2"><i class="feather-alert-circle flex-shrink-0"></i></div>
							<div class="text-danger w-100">
								<div class="fw-semibold d-flex justify-content-between">
									Error
									<button type="button" class="btn-close p-0" data-bs-dismiss="alert" aria-label="Close"><i class="fas fa-xmark"></i></button>
								</div>
								<div class="fs-12 op-8">{{ session('error') }}</div>
							</div>
						</div>
					</div>
				</div>
				<script>
					setTimeout(() => { $('#errorAlert').fadeOut(); }, 5000);
				</script>
			@endif

			@if ($errors->any())
				<div id="validationAlert" class="card border-0 shadow-lg mb-2">
					<div class="alert alert-danger border border-danger mb-0 p-3">
						<div class="d-flex align-items-start">
							<div class="me-2"><i class="feather-x-circle flex-shrink-0"></i></div>
							<div class="text-danger w-100">
								<div class="fw-semibold d-flex justify-content-between">
									Validation Error
									<button type="button" class="btn-close p-0" data-bs-dismiss="alert" aria-label="Close"><i class="fas fa-xmark"></i></button>
								</div>
								<ul class="fs-12 op-8 mb-0 ps-3 mt-1">
									@foreach ($errors->all() as $error)
										<li>{{ $error }}</li>
									@endforeach
								</ul>
							</div>
						</div>
					</div>
				</div>
				<script>
					setTimeout(() => { $('#validationAlert').fadeOut(); }, 6000);
				</script>
			@endif
		</div>

		<!-- Header -->
		@include('admin.layouts.partials.header')
		<!-- /Header -->
		<!-- Sidebar -->
		@include('admin.layouts.partials.sidebar')
		<!-- /Sidebar -->
		<!-- Page Wrapper -->
		@yield('content')
		<!-- /Page Wrapper -->

	</div>
	<!-- /Main Wrapper -->

	<!-- jQuery -->
	<script src="/admin/assets/js/jquery-3.7.1.min.js"></script>
	
	<script src="/admin/assets/plugins/select2/js/select2.min.js"></script>

	<!-- Bootstrap Core JS -->
	<script src="/admin/assets/js/bootstrap.bundle.min.js"></script>

	<!-- Input tanggal DD-MM-YYYY -->
	@include('partials.date-inputs')

	<!-- Feather Icon JS -->
	<script src="/admin/assets/js/feather.min.js"></script>

	<!-- Slimscroll JS -->
	<script src="/admin/assets/js/jquery.slimscroll.min.js"></script>

	<!-- memanggil theiaStickySidebar -->
	<script src="/admin/assets/plugins/theia-sticky-sidebar/theia-sticky-sidebar.min.js"></script>

	<!-- Custom JS -->
	<script src="/admin/assets/js/script.js"></script>

	{{-- lightbox img --}}
	<script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/js/lightbox.min.js"></script>
    @stack('js')

	{{-- Error "Ajax error" DataTables: tampilkan kode HTTP & kirim detailnya ke laravel.log --}}
	<script>
		(function () {
			if (!window.jQuery || !$.fn.dataTable) return;

			var hints = {
				0: 'Koneksi ke server terputus / request dibatalkan.',
				401: 'Sesi login habis. Muat ulang halaman lalu login kembali.',
				403: 'Akun Anda tidak punya izin untuk data ini.',
				404: 'Alamat data tidak ditemukan.',
				419: 'Sesi halaman kedaluwarsa. Muat ulang halaman.',
				429: 'Terlalu banyak permintaan. Tunggu sebentar lalu coba lagi.',
				500: 'Terjadi error di server.',
				502: 'Server sedang bermasalah (bad gateway).',
				503: 'Server sedang tidak tersedia.',
				504: 'Server terlalu lama memproses (timeout). Coba kurangi jumlah baris per halaman atau perketat filter.'
			};

			$.fn.dataTable.ext.errMode = function (settings, techNote, message) {
				var xhr = settings.jqXHR || {};
				var ajax = settings.ajax;
				var lastParams = settings.oAjaxData || null;

				$.ajax({
					url: @json(route('client-log')),
					method: 'POST',
					headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
					data: {
						message: String(message || '').substring(0, 1000),
						table: settings.sTableId,
						page_url: window.location.href,
						ajax_url: String((ajax && (ajax.url || (typeof ajax === 'string' ? ajax : ''))) || settings.sAjaxSource || ''),
						status: xhr.status || 0,
						status_text: xhr.statusText || '',
						response: String(xhr.responseText || '').substring(0, 4000),
						params: lastParams ? JSON.stringify({
							start: lastParams.start, length: lastParams.length,
							search: lastParams.search && lastParams.search.value,
							site_id: lastParams.site_id, type_id: lastParams.type_id,
							start_date: lastParams.start_date, end_date: lastParams.end_date
						}).substring(0, 2000) : ''
					}
				});

				var status = xhr.status;
				var detail = status !== undefined
					? '\n\nHTTP ' + status + (xhr.statusText ? ' ' + xhr.statusText : '') + (hints[status] ? ' - ' + hints[status] : '')
					: '';
				alert(message + detail + '\n\nDetail error sudah dicatat di laravel.log.');
			};
		})();
	</script>

	<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
	<script>
		function readDownloadCookie() {
			const match = document.cookie.match(/(?:^|;\s*)download_token=([^;]*)/);
			return match ? decodeURIComponent(match[1]) : null;
		}

		function clearDownloadCookie() {
			document.cookie = 'download_token=; Max-Age=0; path=/';
		}

		$(document).on('submit', 'form', function(e) {
			const form = this;

			// Token unik per submit; server membalas cookie dengan token yang sama
			// bila responsnya berupa file download (lihat MarkDownloadComplete).
			const token = Date.now().toString(36) + Math.random().toString(36).slice(2, 10);
			$(form).find('input[name="download_token"]').remove();
			$('<input>', { type: 'hidden', name: 'download_token', value: token }).appendTo(form);
			clearDownloadCookie();

			$('.modal').modal('hide');
			Swal.fire({
				title: 'Sedang memproses...',
				html: 'Tunggu sebentar, data sedang diproses...',
				showConfirmButton: false,
				allowOutsideClick: false,
				didOpen: () => {
					Swal.showLoading();
				}
			});

			// Download tidak berpindah halaman: tutup popup begitu file mulai diunduh.
			const poll = setInterval(() => {
				if (readDownloadCookie() === token) {
					clearInterval(poll);
					clearDownloadCookie();
					Swal.close();
				}
			}, 500);

			setTimeout(() => {
				form.submit();
			}, 1000);
			e.preventDefault();
		});
	</script>




</body>

</html>