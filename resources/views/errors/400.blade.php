@extends('mobiles.layouts.module')


@section('content')
<div class="page-content">

    <div class="page-title page-title-small">
        <h2 style="color: black"><a href="{{ route('mobile.home') }}"><i class="fa fa-arrow-left" style="color: black"></i></a></h2>
        <div class="divider"></div>
    </div>
        <div class="content py-5 text-center">
            <h1><i class="fa fa-exclamation-triangle color-red-dark fa-4x"></i></h1>
            <h1 class="fa-5x pt-5 pb-2">404</h1>
            <h4 class="text-uppercase pb-3">Page not Found</h4>
            <p class="boxed-text-l">
                The page you're looking for cannot be found.
                How about trying the homepage?
            </p>
            <a href="{{ route('mobile.home') }}" class="back-button btn btn-m btn-center-s bg-highlight rounded-sm font-700 text-uppercase">Back Home</a>
        </div>
</div>
@endsection