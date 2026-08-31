@extends('components.front.layout')

@section('page-content')

    <main class="login-form-page">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                </div>
                <div class="col-lg-6 ps-0">
                    <!-- <div class="left-image-wrapper">
                        <img loading="lazy" src="{{asset('assets/img-new/banner-login.jpg')}}" class="img-fluid" />
                    </div> -->
                    <div class="login_img_bg_2"></div> 
                </div>
                <div class="col-lg-6 ps-0 pe-0">
                    <div class="login-wrapper card">
                        <img src="{{asset('assets/img-new/msme-logo.png')}}" loading="lazy" class="img-fluid" style="max-width: 250px;">
                        <div class="inner-login-wrapper">
                        <div class="align-items-center border-0 card gap-2 shadow-none" style="background: transparent; min-height: auto;">
                            <!-- <div class="card-header text-center py-4 border-0">
                                <img src="{{asset('assets/img-new/logo-ffo-new.png')}}" loading="lazy" class="logo">
                            </div> -->
                            <div class="card-text text-center">
                                    @yield('auth-form')
                            </div>
                        </div>
                        </div>
                    </div>
                    <img src="{{asset('assets/img-new/login-decor-img.svg')}}" class="login-decor"> 
                </div>
            </div>
        </div>
    </main>

@endsection