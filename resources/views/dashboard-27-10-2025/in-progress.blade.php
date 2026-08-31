@extends('components.admin.layout')

@section('styles')

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@400..800&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">


<style>
  body {
	padding: 0;
	margin: 0;
	font-family: "Poppins", sans-serif;
  }

  .under-construction {
	display: grid;
	grid-template-columns: repeat(2, 1fr);
	grid-template-rows: 1fr;
	grid-column-gap: 0px;
	grid-row-gap: 0px;
	position: relative;
  }

  .under-construction>div {
	display: flex;
	flex-direction: column;
	height: 100dvh;
	position: relative;
  }

  .content {
	position: absolute;
	left: 10%;
	top: 25%;
	transform: translate(0, 0);
  }

  .content h1 {
	color: #231c61;
	font-size: 4rem;
	margin: 0;
  }

  .content h3 {
	color: #5571e9;
	font-size: 2rem;
	margin: 0;
  }

  .img-sec {
	display: flex;
	align-items: center;
	height: 100vh;
  }

  .img-sec img {
	width: 100%;
	max-width: 100%;
  }

  .back-btn {
	display: flex;
	background-color: #121b54;
	max-width: 170px;
	color: #fff;
	text-decoration: none;
	text-align: center;
	box-sizing: border-box;
	padding: 10px;
	border-radius: 50px;
	margin-top: 60px;
	justify-content: center;
	gap: 0.5rem;
	transition-duration: 0.4s;
  }
  .back-btn:hover{
	background-color: #5571e9;
  }
  .back-btn:hover > img{
	margin-left: -5px;
	transition-duration: 0.4s;
  }
  .back-btn >img{
	width: 20px;
	transition-duration: 0.4s;
  }
</style>

@endsection

@section('page-content')

<div class="under-construction mt-3">
    
      <div class="content">
        <h1>Coming Soon!</h1>
        <h3>Page Under Construction </h3>
        <p>We are curently working on this page<br/> and wiil launch soon!</p>
        <!-- <span>admin@demoemail.com</span> -->
        <a href="#" class="back-btn" id="backToHome"><img src="{{ asset('assets/img/bck-btn.svg') }}"> Back to Home</a>
      </div>
    
    
      <div class="img-sec">
        <img src="{{ asset('assets/img/under_construction.svg') }}" />
      </div>
    
  </div>

@endsection


@section('js') 

<script type="text/javascript"> 
	

	
</script>

@endsection

