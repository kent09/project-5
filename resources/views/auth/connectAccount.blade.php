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

<div class="infusionsoft-connect">
  <div class="container">
    <div class="row connectaccount">
      <div class="col-lg-8 col-lg-offset-2">
        <div class="formholder marginneg nopadside">
          <div class="marleft">
            <a id="connect" href="{{route('/connect/infusionsoft')}}" class="{{$infusion_connect ? 'disabled' : 'true'}}">
              <h2 class="{{$infusion_connect ? 'synedAccount' : 'syncaccount'}}" >
                Connect Your
                <strong>
                InfusionSoft Account
                </strong>
                <br/>
                @if(!$infusion_connect)
                <div style="position: absolute;left: 50%;transform: translateX(-50%) translateY(-50%);">
                  <span style="font-size: 11px;" class="connect-account">
                  click here to connect
                  </span>
                </div>
                @endif
              </h2>
            </a>
          </div>
          <hr>
          @if($infusion_connect)
          <div class="" style="text-align: center;">
            <a href="{{url('/')}}" class="{{ $classButton }}">
            Proceed to you Account
            </a>
          </div>
          @else
          <div class="" style="text-align: center;">
            <a href="{{url('/logout')}}" class="{{ $classButton }}">
            Logout
            </a>
          </div>
          @endif
        </div>
      </div>
    </div>
  </div>
</div>
@endsection