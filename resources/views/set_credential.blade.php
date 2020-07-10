<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>FusedTools</title>

    <!-- Fonts -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.5.0/css/font-awesome.min.css" integrity="sha384-XdYbMnZ/QjLh6iI4ogqCTaIjrFk87ip+ekIjefZch0Y+PvJ8CDYtEs1ipDmPorQ+" crossorigin="anonymous">

    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Lato:100,300,400,700">


    <!-- Styles -->
     <link rel="stylesheet" href="{{ url('assets/css/style.css')}}" crossorigin="anonymous">
     <link rel="stylesheet" href="{{ url('assets/css/token-input.css')}}" crossorigin="anonymous">
     <link rel="stylesheet" href="{{ url('assets/css/bootstrap.min.css')}}" crossorigin="anonymous">
    {{-- <link href="{{ elixir('css/app.css') }}" rel="stylesheet"> --}}

</head>
<body id="app-layout">
    <nav class="navbar navbar-default navbar-static-top">
        <div class="container">
            <div class="navbar-header">

                <!-- Collapsed Hamburger -->
                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#app-navbar-collapse">
                    <span class="sr-only">Toggle Navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>

                <!-- Branding Image -->
                <a class="navbar-brand" href="{{ url('/home') }}">
                    FusedTools
                </a>
            </div>

            <div class="collapse navbar-collapse" id="app-navbar-collapse">
              
        </div>
    </nav>
	<div class="container-fluid">
		<div class="row">
			<div class="col-md-8 col-md-offset-2">
				<div class="panel panel-default">
					<div class="panel-heading">Create Account</div>
					<div class="panel-body">
						<form class="form-horizontal" role="form" method="POST" action="{{ url('/create_account') }}">
							{{ csrf_field() }}

							<div class="form-group">
								<label for="password" class="col-md-4 control-label">Login Email</label>

								<div class="col-md-6" style="margin-top:5px;">
									<input type="hidden" class="form-control" name="email" value="{{ $email }}">
									{{ $email }}
								</div>
							</div>
							<div class="form-group{{ $errors->has('password') ? ' has-error' : '' }}">
								<label for="password" class="col-md-4 control-label">Password</label>

								<div class="col-md-6">
									<input id="password" type="password" class="form-control" name="password">

									@if ($errors->has('password'))
										<span class="help-block">
											<strong>{{ $errors->first('password') }}</strong>
										</span>
									@endif
								</div>
							</div>
							<div class="form-group{{ $errors->has('password_confirmation') ? ' has-error' : '' }}">
								<label for="password-confirm" class="col-md-4 control-label">Confirm Password</label>

								<div class="col-md-6">
									<input id="password-confirm" type="password" class="form-control" name="password_confirmation">

									@if ($errors->has('password_confirmation'))
										<span class="help-block">
											<strong>{{ $errors->first('password_confirmation') }}</strong>
										</span>
									@endif
								</div>
							</div>
							<div class="form-group">
								<div class="col-md-6 col-md-offset-4">
									<button type="submit" class="btn btn-primary">
										<i class="fa fa-btn fa-sign-in"></i> Create
									</button>
								</div>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
    
    <!-- jQuery -->
    <script src="{{ URL::to('assets/js/jquery.js') }}"></script>
    <script src="{{ URL::to('assets/js/jquery.validate.min.js') }}"></script>
    <script src="{{ URL::to('assets/js/bootstrap.min.js') }}"></script>
    {{-- <script src="{{ elixir('js/app.js') }}"></script> --}}
    <script src="{{ URL::to('assets/js/jquery.tokeninput.js') }}"></script>
</body>
</html>


