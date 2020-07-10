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
	
<div class="container">
<div class="row connectaccount">
   <div class="col-lg-4 col-lg-offset-4">
      <div class="login-form">
         <h1 class="title primary-title">Reset Password</h1>
         @if (session('status'))
         <div class="alert alert-success alert-dismissable">
            <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
         </div>
         @endif
        
         <form action="{{ url('/password/reset') }}" method="POST">
            {{ csrf_field() }}
            
            <input type="hidden" name="token" value="{{ $token }}">
            <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
               <label for="email">Email: </label>
               <input type="text" class="form-control" id="email" name="email" value="{{ $email or old('email') }}">
               @if ($errors->has('email'))
               <span class="help-block">
               <strong>{{ $errors->first('email') }}</strong>
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
               <label for="password_confirmation">Password Confirm: </label>
               <input type="password" class="form-control" id="password_confirmation" name="password_confirmation">
               @if ($errors->has('password_confirmation'))
               <span class="help-block">
               <strong>{{ $errors->first('password_confirmation') }}</strong>
               </span>
               @endif
            </div>
           
            <button type="submit" class="{{ $classButton }} primary-button">Change</button>
         </form>
      </div>
   </div>
</div>



@endsection
