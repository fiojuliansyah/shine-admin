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
	<title>Cipta Karir - Job Portal</title>

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

    @stack('css')

	<link rel="stylesheet" href="/admin/assets/css/style.css">

</head>

<body data-layout="detached">

	<div class="main-wrapper">

		{{-- Pesan sukses/error/validasi tampil lewat dialog seragam (partials.app-dialog) --}}

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

	<!-- Input tanggal DD-MM-YYYY -->
	@include('partials.date-inputs')

	<!-- Feather Icon JS -->
	<script src="/admin/assets/js/feather.min.js"></script>

	<!-- Slimscroll JS -->
	<script src="/admin/assets/js/jquery.slimscroll.min.js"></script>

	<!-- Custom JS -->
	<script src="/admin/assets/js/script.js"></script>

	@include('partials.app-dialog')
	@include('partials.document-preview')
    @stack('js')


</body>

</html>