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
  <div class="row connectaccount error-page">
    <div class="col-lg-8 col-lg-offset-2">
      <div class="login-form">
        <div class="error-template text-center">
          <h1>500</h1>
          <h2>Internal Server Error</h2>
          <div class="error-details">
            Please try again or feel free to contact us if the problem persists.
          </div>
          <br>
          <div class="error-actions">
            <a href="{{url('/')}}" class="{{ $classButton }}"><span class="glyphicon glyphicon-home"></span>
            Take Me Home </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

