<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
	<meta name="description" content="Karyax Integrated System">
	<meta name="keywords" content="admin, estimates, bootstrap, business, html5, responsive, Projects">
	<meta name="author" content="Dreams technologies - Bootstrap Admin Template">
	<meta name="robots" content="noindex, nofollow">
	<title>SHINE - Job Portal</title>

	<!-- Favicon -->
	<link rel="shortcut icon" type="image/x-icon" href="/admin/assets/img/favicon.png">

	<!-- Apple Touch Icon -->
	<link rel="apple-touch-icon" sizes="180x180" href="/admin/assets/img/apple-touch-icon.png">

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

    @stack('css')
	<!-- Main CSS -->
	<link rel="stylesheet" href="/admin/assets/css/style.css">

</head>

<body data-layout="detached">

	<!-- Main Wrapper -->
	<div class="main-wrapper">

		@if(session('success'))
			<div id="successAlert" class="col-xl-3" style="position: fixed; top: 20px; left: 88%; transform: translateX(-50%); z-index: 9999; max-width: 400px; width: 100%; display: block;">
				<div class="card border-0">
					<div class="alert alert-primary border border-primary mb-0 p-3">
						<div class="d-flex align-items-start">
							<div class="me-2">
								<i class="feather-info flex-shrink-0"></i>
							</div>
							<div class="text-primary w-100">
								<div class="fw-semibold d-flex justify-content-between">
									Success<button type="button" class="btn-close p-0"
										data-bs-dismiss="alert" aria-label="Close"><i
											class="fas fa-xmark"></i></button></div>
								<div class="fs-12 op-8 mb-1">{{ session('success') }}</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<script>
				setTimeout(function() {
					document.getElementById('successAlert').style.display = 'none';
				}, 3000);
			</script>
		@endif


		<!-- Header -->
		@include('website.layouts.partials.header')
		<!-- /Header -->
		<!-- Sidebar -->
		@include('website.layouts.partials.sidebar')
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

	<!-- Feather Icon JS -->
	<script src="/admin/assets/js/feather.min.js"></script>

	<!-- Slimscroll JS -->
	<script src="/admin/assets/js/jquery.slimscroll.min.js"></script>

	<!-- Custom JS -->
	<script src="/admin/assets/js/script.js"></script>

    @stack('js')

	<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
	<script>
		$(document).on('submit', 'form', function(e) {
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

			setTimeout(() => {
				this.submit();
			}, 1000); 
			e.preventDefault();
		});
	</script>

</body>

</html>