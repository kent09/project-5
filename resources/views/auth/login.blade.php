@php
	$layout = 'layouts.appaccount';

	switch(config('fusedsoftware.subdomain')) {
		case env('FUSEDSUITE_TOOLS_SUBDOMAIN'):
			$layout = 'layouts.apptools';
			break;
		case env('FUSEDSUITE_DOCS_SUBDOMAIN'):
			$layout = 'layouts.appdocs';
			break;
		case env('FUSEDSUITE_INVOICE_SUBDOMAIN'):
			$layout = 'layouts.appinvoices';
			break;
	}
@endphp

@extends($layout)
@section('title', 'Login')
@section('content')

@if ( Session::has('success') )
	<span class="help-block text-center" style=" color:green;">
		<strong>{{ Session::get('success') }}</strong>
		{{ Session::forget('success') }}
	</span>
@endif

@if ( Session::has('error') )
	<span class="help-block text-center" style=" color:#C24842;">
		<strong>{{ Session::get('error') }}</strong>
		{{ Session::forget('error') }}
	</span>
@endif

<div class="row" style="margin-top: 20px;">
	<div class="col-md-10 col-md-offset-1">
	<form class="" role="form" method="POST" action="{{ url('/login') }}">
			<input type="hidden" name="_token" value="{{ csrf_token() }}">

			<div class="row text-center" style="margin-bottom: 25px;">
				<div class="col-md-12">
					<h3 style="margin-bottom: 10px;">Login</h3>
					<p>Please enter your email address and password</p>
				</div>
			</div>
			<div class="form-group{{ $errors->has('email') ? ' has-error' : '' }} row">
				<div class="col-md-12">
					<input id="email" type="email" class="form-control" name="email" value="{{ old('email') }}"  placeholder="Email">

					@if ($errors->has('email'))
						<span class="help-block">
							<strong>{{ $errors->first('email') }}</strong>
						</span>
					@endif
				</div>
			</div>

			<div class="form-group{{ $errors->has('password') ? ' has-error' : '' }} row">
				<div class="col-md-12">
					<input id="password" type="password" class="form-control" name="password" placeholder="Password">
					@if ($errors->has('password'))
						<span class="help-block">
							<strong>{{ $errors->first('password') }}</strong>
						</span>
					@endif
				</div>
			</div>

			<div class="form-group row">
				<div class="col-md-12">
					<div class="checkbox">
						<label>
							<input type="checkbox" name="remember"> Remember Me
						</label>
					</div>
				</div>
			</div>

			<div class="form-group row">
				<div class="col-md-12">
					<button type="submit" class="btn-block btn btn-default btn_cls">
						<i class="fa fa-btn fa-sign-in"></i> Login
					</button>

					<a class="btn btn-link" href="{{ url('/password/reset') }}" style="margin: 0px; padding: 0px;">Forgot your password?</a>

				</div>
			</div>
		</form>
	</div>
</div>
<style>
	.content {
		width: 500px !important;
	}
	.inner-content {
		min-height:400px;
	}
</style>	

    
@endsection
