<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
	<meta name="description" content="Karyax Integrated System">
	<meta name="keywords" content="admin, estimates, bootstrap, business, html5, responsive, Projects">
	<meta name="author" content="Dreams technologies - Bootstrap Admin Template">
	<meta name="robots" content="noindex, nofollow">
	<title>KARYAX - Job Portal</title>

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

	<link rel="stylesheet" href="/admin/assets/css/style.css">

</head>

<body data-layout="detached">

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

		@include('website.layouts.partials.header')

		@include('website.layouts.partials.sidebar')

		@yield('content')


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