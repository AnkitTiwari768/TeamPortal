@extends('components.admin.layout')

@section('styles')

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

<style>

.dashboard-default-hero{
    position:relative;
    min-height:620px;
    display:flex;
    align-items:center;
    overflow:hidden;
    background:url('https://web.utlhq.com/team_uat/sites/default/files/2026-02/banner-3.png') center center/cover no-repeat;
    box-shadow:0 6px 24px rgba(5,20,45,.18);
}
.dashboard-default-hero::before{
    content:'';
    position:absolute;
    inset:0;
    background:linear-gradient(
        100deg,
        rgba(4,16,38,.94) 0%,
        rgba(4,16,38,.82) 38%,
        rgba(4,16,38,.35) 75%,
        rgba(4,16,38,.15) 100%
    );
}

.dashboard-default-hero .container{
    width:90%;
    max-width:1200px;
    margin:auto;
    position:relative;
    z-index:2;
}

.dashboard-default-hero .brand-logo{
    position:absolute;
    top:0;
    left:0;
    display:inline-flex;
    align-items:center;
    background:rgba(255,255,255,.12);
    backdrop-filter:blur(10px);
    -webkit-backdrop-filter:blur(10px);
    border:1px solid rgba(255,255,255,.18);
    padding:10px 20px;
    border-radius:12px;
}

.dashboard-default-hero .brand-logo img{
    height:52px;
    width:auto;
    display:block;
}

.dashboard-default-hero .content{
    max-width:700px;
    padding-top:110px;
    color:#fff;
}

.dashboard-default-hero .content h1{
    font-family:'Poppins', sans-serif;
    font-size:58px;
    font-weight:800;
    line-height:1.15;
    letter-spacing:.3px;
    margin-bottom:22px;
}

.dashboard-default-hero .content h1 span{
    color:#1dd1c1;
}

.dashboard-default-hero .content p{
    font-family:'Poppins', sans-serif;
    font-size:15px;
    line-height:1.85;
    color:#f1f1f1;
    margin-bottom:0;
    max-width:650px;
}

@media(max-width:768px){

.dashboard-default-hero{
    min-height:auto;
    padding:70px 0 40px;
}

.dashboard-default-hero .brand-logo{
    position:static;
    margin-bottom:30px;
}

.dashboard-default-hero .brand-logo img{
    height:42px;
}

.dashboard-default-hero .content{
    padding-top:0;
}

.dashboard-default-hero .content h1{
    font-size:36px;
}

.dashboard-default-hero .content p{
    font-size:16px;
}

}

</style>

@endsection

@section('page-content')

<section class="dashboard-default-hero">

<div class="container">

<div class="brand-logo" style="   margin-top: -11%;
    margin-left: -55px;">
    <img src="https://web.utlhq.com/team_uat/themes/unee_msme/img/logo.svg" alt="MSME Logo">
</div>

<div class="content">

<h1>
MSME <span>TEAM</span><br>
INITIATIVE
</h1>

<p>
MSME TEAM is a transformative initiative by the Ministry of MSME under the RAMP Programme to empower Indian Micro, Small and Medium Enterprises through digital innovation, capacity building, and technology-driven growth.
</p>

</div>

</div>

</section>

@endsection
