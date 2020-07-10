<nav class="navbar navbar-default">
  <div class="container-fluid">
    <div class="navbar-header logo">
      <a href="/">
        <img src="/assets/images/logo/fusedinvoice.png">
      </a>
    </div>
    <ul class="nav navbar-nav nav-item">
      <p class="text-center"></p>
      @if( Auth::user() )
      <li class="{{ url('/dashboard') == url()->current() ? 'active' : ''}}">
        <a href="{{ url('/dashboard') }}"><i class="fa fa-dashboard"> </i> &nbsp; Dashboard</a>
      </li>
      <li class="{{ url('/scripts/xero-invoice-cron') == url()->current() ? 'active' : '' }}">
        <a href="{{ url('/scripts/xero-invoice-cron') }}"><i class="fa fa-copy"> </i>  &nbsp; Copy Order To Xero</a>
      </li>
      <li class="{{ url('/scripts/xero-invoice-copy') == url()->current() ? 'active' : '' }}">
        <a href="{{ url('/scripts/xero-invoice-copy') }}"><i class="fa fa-list"> </i>  &nbsp;  HTTP Xero Creator</a>
      </li>
      <li class="{{ url('/scripts/xero-invoice-copy') == url()->current() ? 'active' : '' }}">
        <!-- <a href="{{ url('/scripts/xero-invoice-copy') }}"><i class="fa fa-refresh"> </i>  &nbsp;  Xero Invoice Sync</a> -->
        <a href="#"><i class="fa fa-refresh"> </i>  &nbsp;  Xero Invoice Sync</a>
      </li>
      @endif
    </ul>
    @include('layouts.appquickswitch')
  </div>
</nav>