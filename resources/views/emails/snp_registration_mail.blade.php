<div id="layoutSidenav_content">
            <main>
                <div class="container-fluid px-4 py-4">
                    <div class="row">
                        <div class="col-lg-12 col-md-12">
                            <div class="card container-main-card">
                                <div class="card-header d-flex">
   
                                </div>
                                <div class="card-body pt-1 position-relative">    
                                   <div class="no-content">
                                        @php
                                        if($status == 'new')
                                            $text = 'for completing the registration';
                                        else
                                            $text = 'for updating your registered information';
                                        @endphp
                                        
                                        <p>Dear <strong>{{$name}}</strong>,<br/><br/> Thank you {{$text}} with MSME Team Portal.<br/><br/>Your profile is under review. You will be notified soon.</p>
										 
                                    </div>


                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </main>

        </div>