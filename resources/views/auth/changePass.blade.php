@php
$classButton = 'btn btn-primary';
$layout = 'layouts.appaccount';

switch(config('fusedsoftware.subdomain')) {
    case env('FUSEDSUITE_TOOLS_SUBDOMAIN'):
      $layout = 'layouts.apptools';
      $classButton = 'btn btn-warning';
      break;
    case env('FUSEDSUITE_DOCS_SUBDOMAIN'):
	   $layout = 'layouts.appdocs';
      $classButton = 'btn btn-success';
      break;
    case env('FUSEDSUITE_INVOICE_SUBDOMAIN'):
	   $layout = 'layouts.appinvoices';
      $classButton = 'btn btn-primary';
      break;
}
@endphp
@extends($layout)

@section('content')
<style>
   body {
      background: red;
   }
   .content, .panel {
      box-shadow: none !important;
      background: transparent;
   }

   .panel {
      margin: 10px 0 150px;
   }

   .login-form h1 {
      text-align: center;
   }
</style>
<div class="panel">
   <div class="panel-body">
      <div class="row">
         <div class="col-lg-4 col-lg-offset-4">

            <div class="login-form">
               <h1 class="title primary-title">Change Password</h1>
               @if(Session::get('success'))
               <div class="alert alert-success alert-dismissable">
                  <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                  {{ Session::get('success') }}
               </div>
               @endif
               <form action="{{ url('/changepassword') }}" method="POST">
                  {{ csrf_field() }}
                  <div class="form-group{{ $errors->has('password') ? ' has-error' : '' }}">
                     <label for="email">New Password: </label>
                     <input type="password" class="form-control" id="password" name="password">
                     @if ($errors->has('password'))
                     <span class="help-block">
                     <strong>{{ $errors->first('password') }}</strong>
                     </span>
                     @endif
                  </div>
                  <div class="form-group{{ $errors->has('password_confirmation') ? ' has-error' : '' }}">
                     <label for="cnf_pwd">Confirm Password:</label>
                     <input type="password" class="form-control" id="cnf_pwd" name="password_confirmation">
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
   </div>
</div>
@endsection