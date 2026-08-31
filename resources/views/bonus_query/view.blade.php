@extends('components.admin.layout')
@section('page-content')
 <main>
                <div class="container-fluid px-4 py-4">
                    <div class="row">
                        <div class="col-lg-12 col-md-12">
                            <div class="card container-main-card">
                                <div class="card-header d-flex">
                                    <div class="heading">
                                        <h1>View Application query</h1>
                                        <nav aria-label="breadcrumb">
                                            <ol class="breadcrumb">
                                                <li class="breadcrumb-item"><a href="#">Home</a></li>
                                                <li class="breadcrumb-item"><a href="#">Filming permissions for live
                                                        action shoot</a></li>
                                                <li class="breadcrumb-item">View Application query</li>
                                            </ol>
                                        </nav>
                                    </div>
                                    <div class="action-header ms-auto">
                                        <!-- split button -->
                                        <div class="btn-group drop-btn">

                                            <button type="button" class="btn btn-danger"> <img src="./img/add.svg">
                                                Add a query</button>
                                            <button type="button"
                                                class="btn btn-danger dropdown-toggle dropdown-toggle-split"
                                                data-bs-toggle="dropdown" aria-expanded="false">
                                                <span class="visually-hidden">Toggle Dropdown</span>
                                            </button>

                                            <ul class="dropdown-menu">
                                                <li><a class="dropdown-item" href="./application-form.html">Filming
                                                        permissions for live
                                                        action shoot</a></li>
                                                <li><a class="dropdown-item" href="#">Official coproduction live
                                                        action</a></li>
                                                <li><a class="dropdown-item" href="#">Official coproduction live
                                                        animation only</a></li>
                                            </ul>
                                        </div>
                                    </div>

                                </div>
                                <div class="card-body pt-1 position-relative">
                                    <a href="#" class="btn back-btn mb-4"> <img src="./img/arrow-back-w.svg"> Back</a>
                                    <div class="row">
                                        <div class="col-lg-8">
                                            <form action="">

                                                <h4 class="form-sub-heading mt-2 mb-3">View query form for <span class="fw-bold">Application Ref. Id :
                                                    FFO/NFDC/22-23/636</span>
                                                </h4>
        
                                                <div class="row">
                                                    <div class="col-lg-12 mb-3">
                                                        <div class="input-box">
                                                            <label class="form-label">Subject</label>
                                                            <input type="text" class="form-control" placeholder="">
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-12 mb-3">
                                                        <label class="form-label col-12">Document Download</label>
                                                        <button class="btn btn-download">Document 1 <img src="./img/download-ico.svg" class="ms-1"> </button>
                                                        <button class="btn btn-download me-1">Document 1 <img src="./img/download-ico.svg" class="ms-1"> </button>
                                                        <button class="btn btn-download me-1">Document 1 <img src="./img/download-ico.svg" class="ms-1"> </button>
                                                        <button class="btn btn-download me-1">Document 1 <img src="./img/download-ico.svg" class="ms-1"> </button>
                                                    </div>
        
                                                    <div class="col-lg-12 mb-3">
                                                        <div class="input-box">
                                                            <label class="form-label">Add your comments/remarks</label>
                                                            <textarea class="form-control" rows="5" placeholder=""></textarea>
                                                        </div>
                                                    </div>
        
        
        
                                                    <div class="form-action mt-3 mb-3 d-flex align-items-center">
                                                        <a href="#" class="btn btn-danger btn-cancel py-2 me-2">Cancel</a>
                                                        <a href="#" class="next-button py-2">Send Query</a>
                                                    </div>
        
        
                                                </div>
                                            </form>
        
                                        </div>
                                        <div class="col-lg-4">
                                            <div class="card query-card">
                                                <div class="card-header">
                                                    <h5>Remarks</h5>
                                                </div>
                                                <div class="card-body">
                                                    <div class="query-box">
                                                        <div class="query-item">
                                                            <div class="query-user d-flex">
                                                                <span><img src="./img/query-user.svg"></span>
                                                                <div class="content ms-2">
                                                                    <h6 class="m-0">Applicant remarks</h6>
                                                                    <p>12-Jan-2024 &nbsp; 10:00am</p>
                                                                </div>
                                                            </div>
                                                            <p class="query">Filming at Airports / Aerial Filming permission from Directorate General of Civil Aviation (DGCA) </p>
                                                        </div>

                                                        <div class="query-item">
                                                            <div class="query-user d-flex">
                                                                <span><img src="./img/query-user.svg"></span>
                                                                <div class="content ms-2">
                                                                    <h6 class="m-0">Applicant remarks</h6>
                                                                    <p>12-Jan-2024 &nbsp; 10:00am</p>
                                                                </div>
                                                            </div>
                                                            <p class="query">Filming at Airports / Aerial Filming permission from Directorate General of Civil Aviation (DGCA) </p>
                                                        </div>

                                                        <div class="query-item">
                                                            <div class="query-user d-flex">
                                                                <span><img src="./img/query-user.svg"></span>
                                                                <div class="content ms-2">
                                                                    <h6 class="m-0">Applicant remarks</h6>
                                                                    <p>12-Jan-2024 &nbsp; 10:00am</p>
                                                                </div>
                                                            </div>
                                                            <p class="query">Filming at Airports / Aerial Filming permission from Directorate General of Civil Aviation (DGCA) </p>
                                                        </div>

                                                        <div class="query-item">
                                                            <div class="query-user d-flex">
                                                                <span><img src="./img/query-user.svg"></span>
                                                                <div class="content ms-2">
                                                                    <h6 class="m-0">Applicant remarks</h6>
                                                                    <p>12-Jan-2024 &nbsp; 10:00am</p>
                                                                </div>
                                                            </div>
                                                            <p class="query">Filming at Airports / Aerial Filming permission from Directorate General of Civil Aviation (DGCA) </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                   
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </main>

@endsection