<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
	<meta content="" name="description">
	<meta content="" name="author">
    <title>@yield('title') FusedDocs Admin</title>

    <!-- Fonts -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.5.0/css/font-awesome.min.css" integrity="sha384-XdYbMnZ/QjLh6iI4ogqCTaIjrFk87ip+ekIjefZch0Y+PvJ8CDYtEs1ipDmPorQ+" crossorigin="anonymous">

    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Lato:100,300,400,700">


    <!-- Styles -->
     <link rel="stylesheet" href="{{ url('assets/css/style.css')}}" crossorigin="anonymous">
     <link rel="stylesheet" href="{{ url('assets/css/token-input.css')}}" crossorigin="anonymous">
     <link rel="stylesheet" href="{{ url('assets/css/bootstrap.min.css')}}" crossorigin="anonymous">
     <link rel="stylesheet"  href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css" crossorigin="anonymous">

    {{-- <link href="{{ elixir('css/app.css') }}" rel="stylesheet"> --}}

</head>
<body id="app-layout" style="background:#EEEEEE !important;">
	<div class="main">
	
		<div class="left-section">
			<center><a href="{{ url('/home') }}"><img src="http://app.fuseddocs.com/assets/images/fusedlogo.png"/></a></center>
			@if( Auth::user() && (Auth::user()->acc_limit_reached))
				<ul class="left-navigation">
					<li class="@if( Request::is('superadmin') || Request::is('/')  ) select @endif"><a href="{{ url('superadmin/') }}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
					<li class="@if( Request::is('superadmin/users') ) select @endif"><a href="{{ url('superadmin/users') }}"><i class="fa fa-users"></i> Users</a></li>
				</ul>
			@endif
		</div>

		<div class="right-section">
			<ul class="top-nav">
			    <li><a href="{{ url('/logout') }}">Logout</a></li>
			</ul>



			<script src="{{ URL::to('assets/js/jquery.js') }}"></script>
			<script src="{{ URL::to('assets/js/jquery.validate.min.js') }}"></script>
			<script src="{{ URL::to('assets/js/bootstrap.min.js') }}"></script>
			{{-- <script src="{{ elixir('js/app.js') }}"></script> --}}
			<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"  crossorigin="anonymous"></script>
			<script src="{{ URL::to('assets/js/jquery.tokeninput.js') }}"></script>
			<script>
                var siteUrl = "{{ url('/') }}";
			</script>
			@if (Auth::user())
				<script src="{{ URL::to('assets/js/notty_sync.js') }}"></script>
			@endif


			@if ($errors->any())
				<div class="alert alert-danger">
					<ul>
						@foreach ($errors->all() as $error)
							<li>{{ $error }}</li>
						@endforeach
					</ul>
				</div>
			@endif
			<div class="content">
				@yield('content')
			</div>
		</div>
	</div>
    <!-- jQuery -->
    @yield('script')
</body>
</html>


