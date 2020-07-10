@php
$classButton = 'btn btn-primary';

switch(config('fusedsoftware.subdomain')) {
    case env('FUSEDSUITE_TOOLS_SUBDOMAIN'):
        $classButton = 'btn btn-warning';
        break;
    case env('FUSEDSUITE_DOCS_SUBDOMAIN'):
        $classButton = 'btn btn-success';
        break;
    case env('FUSEDSUITE_INVOICE_SUBDOMAIN'):
        $classButton = 'btn btn-primary';
        break;
}
@endphp
@extends('layouts.headers.initialheader')
@section('content')

<div class="container fusesuite-register">
<div class="row connectaccount">
   <div class="col-lg-6 col-lg-offset-3">
      <div class="login-form ">
		<div class="primary-title">
			<h3>Start Your <strong>Free</strong></h3>
			<h1>FusedTools Trial Now</h1>
			<h3><img src="{{ url('assets/images/icon_speacker.png') }}"> No Credit Card Required</h3>
         <p>Already have an account? <a href="/login">Login</a></p>
		</div>
         @if(Session::get('success'))
         <div class="alert alert-success alert-dismissable">
            <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
            {{ Session::get('success') }}
         </div>
         @endif
         <form action="{{ url('/register') }}" method="POST">
            {{ csrf_field() }}
            <div class="form-group{{ $errors->has('first_name') ? ' has-error' : '' }}">
               <label for="first_name">First Name: </label>
               <input type="text" class="form-control" id="first_name" name="first_name">
               @if ($errors->has('password'))
               <span class="help-block">
               <strong>{{ $errors->first('first_name') }}</strong>
               </span>
			   @endif
			</div>

            <div class="form-group{{ $errors->has('last_name') ? ' has-error' : '' }}">
               <label for="last_name">Last Name: </label>
               <input type="text" class="form-control" id="last_name" name="last_name">
               @if ($errors->has('last_name'))
               <span class="help-block">
               <strong>{{ $errors->first('last_name') }}</strong>
               </span>
			   @endif
			</div>
			
			<div class="form-group{{ $errors->has('company_name') ? ' has-error' : '' }}">
               <label for="company_name">Company Name: </label>
               <input type="text" class="form-control" id="company_name" name="company_name">
               @if ($errors->has('company_name'))
               <span class="help-block">
               <strong>{{ $errors->first('company_name') }}</strong>
               </span>
			   @endif
			</div>

			<div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
               <label for="email">Email: </label>
               <input type="text" class="form-control" id="email" name="email">
               @if ($errors->has('email'))
               <span class="help-block">
               <strong>{{ $errors->first('email') }}</strong>
               </span>
			   @endif
			</div>


			<div class="form-group{{ $errors->has('phone') ? ' has-error' : '' }}">
               <label for="phone">Phone: </label>
               <input type="text" class="form-control" id="phone" name="phone">
               @if ($errors->has('phone'))
               <span class="help-block">
               <strong>{{ $errors->first('phone') }}</strong>
               </span>
			   @endif
			</div>

			<div class="form-group{{ $errors->has('timezonelist') ? ' has-error' : '' }}">
               <label for="timezonelist">Timezone List: </label>
               <select name="timezone" class="form-control">
					@foreach(config('timezonelist') as $value => $label)
						<option value="{{ $label }}">{{ $label }} {{ $value }}</option>
					@endforeach
				</select>
               @if ($errors->has('timezonelist'))
				<span class="help-block">
				<strong>{{ $errors->first('timezonelist') }}</strong>
				</span>
			   @endif
			</div>

			<div class="form-group{{ $errors->has('country') ? ' has-error' : '' }}">
               <label for="country">Country: </label>
               <input type="text" class="form-control" id="country" name="country">
               @if ($errors->has('country'))
               <span class="help-block">
               <strong>{{ $errors->first('country') }}</strong>
               </span>
			   @endif
			</div>

			<div class="form-group{{ $errors->has('password') ? ' has-error' : '' }}">
               <label for="password">Password: </label>
               <input type="password" class="form-control" id="password" name="password">
               @if ($errors->has('password'))
               <span class="help-block">
               <strong>{{ $errors->first('password') }}</strong>
               </span>
			   @endif
			</div>

			<div class="form-group{{ $errors->has('password_confirmation') ? ' has-error' : '' }}">
               <label for="password_confirmation">Password Confirmation: </label>
               <input type="password" class="form-control" id="password_confirmation" name="password_confirmation">
               @if ($errors->has('password_confirmation'))
               <span class="help-block">
               <strong>{{ $errors->first('password_confirmation') }}</strong>
               </span>
			   @endif
			</div>


            <button type="submit" class="{{ $classButton }} primary-button">Create</button>
         </form>
      </div>
   </div>
</div>

@endsection