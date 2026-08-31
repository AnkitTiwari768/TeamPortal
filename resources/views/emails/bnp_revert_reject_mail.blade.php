<div id="layoutSidenav_content">
            <main>
                <div class="container-fluid px-4 py-4">
                    <div class="row">
                        <div class="col-lg-12 col-md-12">
                            <div class="card container-main-card">
                                <div class="card-header d-flex">
   
                                </div>
                                <div class="card-body pt-1 position-relative">    
                                   <div class="no-content" style="font-family: calibri;">
                                       
                                        <p>Dear <strong>{{$name}}</strong>,<br/><br/> Your account for BNP has been {{$status}}.<br/><br/>  
                                        @if($status == 'Reverted')
                                            Kindly update your details below: <a href="{{ $revertlink }}">Here</a>
                                        @endif
                                        </p>
										
										 
                                    </div>


                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </main>

        </div>