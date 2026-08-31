<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - MSME TEAM Initiative</title>
    <link rel="icon" type="image/x-icon" href="http://192.168.2.47/projects/team_portal/assets/favicon.ico">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&amp;display=swap" rel="stylesheet">
    <!-- CSS Link -->
    <link rel="stylesheet" href="{{ asset('assets/css/common-style-form.css') }}">
</head>

<style>
    .new_login_page {
        background: url("{{ asset('assets/image/login-background.png') }}") no-repeat center;
        background-size: cover;
    }

    .new-registration-option .info-glass {
        position: absolute;
        top: 10% !important;
        padding: 20px 25px !important;
        text-align: center;
        background: rgb(10 41 81 / 37%);
        backdrop-filter: blur(6px);
        border-radius: 10px;
        border: 1px solid rgba(255, 255, 255, 0.2);
        margin: 0 27px !important;
    }

    .register-card {
        background: linear-gradient(180deg, #ffffff, #f1f7ff);
        border-radius: 12px;
        padding: 30px;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
    }

    .option-box {
        background: #e7f1ff;
        border-radius: 10px;
        padding: 8px 18px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        cursor: pointer;
        transition: 0.3s;
        border: 1px solid #41668938;
    }

    .option-box:hover {
        background: #d9e9ff;
    }

    .arrow-btn {
        width: 42px;
        height: 42px;
        background: #597de1;
        color: #fff;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        transition: all 0.3s ease;
    }

    .arrow-btn:hover {
        background: #16317e;
        transform: translateY(-3px);
        box-shadow: 0 10px 20px rgba(30, 79, 216, 0.25);
    }

    .option-box span {
        font-size: 13px;
    }
</style>

<body class="new_login_page new-registration-option login-page">


    <div class="container-fluid">
        <div class="row justify-content-center">

            <div class="card login-card login-form-page">
                <div class="row g-0 left-side-img">

                    <div class="col-md-6 left-side d-none d-md-flex ">
                        <div class="login_img_bg_2"></div>
                        <div class="info-glass">
                            <h4>Welcome to MSME TEAM Portal</h4>
                            <p>An initiative of the Ministry of MSME, MSME TEAM
                                (Trade Enablement and Marketing) is launched
                                under the "Raising and Accelerating MSME Productivity
                                (RAMP)"Programme.</p>

                        </div>
                    </div>

                    <div class="col-md-6 right-side pt-0 ">
                        <div class="registration-screen right-side-content-container">


                            <div class="d-flex align-items-center mb-4">
                                <img src="{{ asset('assets/image/msme-logo.png') }}" loading="lazy" class="img-fluid"
                                    style="max-width: 250px;">

                            </div>

                            <h3 class="fw-bold fs-5  mb-3 page-head">MSMEs,<br> <span style="font-weight: 500;">Let’s
                                    Get You Register on TEAM!</span></h3>



                            <div class="tab-content" id="loginTabContent">

                                <form class="pt-5">

                                    <!-- Option 1 -->
                                    <div class="option-box mb-3">
                                        <span>If you have Udyam Registration Number</span>
                                        <div class="arrow-btn">
                                            <a href="{{ url('signup') }}"><img
                                                    src="{{ asset('assets/image/redirect-icon.svg') }}"/></a>
                                        </div>
                                    </div>

                                    <!-- Option 2 -->
                                    <div class="option-box">
                                        <span>If you do not have Udyam Number</span>
                                        <div class="arrow-btn  text-primary">
                                            <a href="{{ url('registration') }}"><img
                                                    src="{{ asset('assets/image/redirect-icon.svg') }}">
                                            </a>
                                        </div>

                                    </div>

                                    <div class="option-box my-3">
                                        <span>If you are a PM Vishwakarma Beneficiary</span>
                                        <div class="arrow-btn  text-primary">
                                            <a href="{{ url('pm-registration') }}"><img
                                                    src="{{ asset('assets/image/redirect-icon.svg') }}">
                                            </a>
                                        </div>

                                    </div>


                                </form>


                                <div class="tab-pane fade" id="others-content" role="tabpanel"
                                    aria-labelledby="others-tab">
                                    <div class="alert alert-info">
                                        <h4 class="fw-bold">Hello Deepak!</h4>
                                        <p>Welcome to the Administrator and Partner login section. Please enter your
                                            credentials to continue.</p>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label  ">Username</label>
                                        <input type="text" class="form-control" placeholder="Enter your username">
                                    </div>
                                    <button type="submit" class="btn btn-primary-custom w-100 text-white">Login as
                                        Admin
                                    </button>
                                </div>

                            </div>
                        </div>

                    </div>
                </div>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>





</body>

</html>
