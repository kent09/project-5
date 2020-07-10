@extends('layouts.applayout')
@section('title', 'App Dashboard')
@section('content')
  <div class="module">
    <div class="row">

      <h3>Choose your application </h3>

      <div class="col-lg-4 col-md-3 col-sm-4 col-xs-6">
        <div class="panel panel-default my_panel">
          <div class="panel-body">
            <a href="//{{ env('FUSEDSUITE_TOOLS_SUBDOMAIN').'.'.env('APP_URL') }}">
              <img src="/assets/images/logo/fusedtools.png" alt="" class="img-responsive center-block" />
            </a>
          </div>
        </div>
      </div>

      <div class="col-lg-4 col-md-3 col-sm-4 col-xs-6">
        <div class="panel panel-default my_panel">
          <div class="panel-body">
            <a href="//{{ env('FUSEDSUITE_DOCS_SUBDOMAIN').'.'.env('APP_URL') }}">
              <img src="/assets/images/logo/fuseddocs.png" alt="" class="img-responsive center-block" />
            </a>
          </div>
        </div>
      </div>

      <div class="col-lg-4 col-md-3 col-sm-4 col-xs-6">
        <div class="panel panel-default my_panel">
          <div class="panel-body">
            <a href="//{{ env('FUSEDSUITE_INVOICE_SUBDOMAIN').'.'.env('APP_URL') }}">
              <img src="/assets/images/logo/fusedinvoice.png" alt="" class="img-responsive center-block" />
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection
