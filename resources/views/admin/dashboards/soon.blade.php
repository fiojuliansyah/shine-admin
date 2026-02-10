@extends('layouts.master')

@section('content')
<div class="main-content">

    <div class="page-content">
        <div class="my-5 pt-sm-5">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="text-center">
                            <a href="index.html">
                                <img src="/dashboard/assets/images/logo-dark.png" alt="logo" height="24" />
                            </a>
                            <div class="row justify-content-center mt-5">
                                <div class="col-sm-4">
                                    <div class="maintenance-img">
                                        <img src="/dashboard/assets/images/maintenance.png" alt="" class="img-fluid mx-auto d-block">
                                    </div>
                                </div>
                            </div>
                            <h4 class="mt-5">Let's get started with Treework</h4>
                            <p class="text-muted">It will be as simple as Occidental in fact it will be Occidental.</p>

                            <div class="row justify-content-center mt-5">
                                <div class="col-md-8">
                                    <div data-countdown="2025/12/31" class="counter-number"></div>
                                </div>
                                <!-- end col-->
                            </div>
                            <!-- end row-->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection