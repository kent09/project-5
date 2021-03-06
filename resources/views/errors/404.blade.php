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
          <h1>404</h1>
          <h2>Page Not Found</h2>
          <div class="error-details">
            Sorry, an error has occured, Requested page not found!
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

