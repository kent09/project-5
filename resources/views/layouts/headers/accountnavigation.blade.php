<nav class="navbar navbar-default">
  <div class="container-fluid">
    <div class="navbar-header logo">
      <a href="/">
        <img src="/assets/images/logo/fusedsuite.png">
      </a>
    </div>
    <ul class="nav navbar-nav nav-item">
      <p class="text-center"></p>
      @if( Auth::user() )
      <li class="{{ url('/billing') == url()->current() ? 'active' : ''}}">
        <a href="{{ url('/billing') }}"><i class="fa fa-file-invoice"> </i> &nbsp; Billing</a>
      </li>
      <li class="{{ url('/support') == url()->current() ? 'active' : '' }}">
        <a href="{{ url('/support') }}"><i class="fa fa-copy"> </i>  &nbsp; Support</a>
      </li>
      @endif
    </ul>
    @include('layouts.appquickswitch')
  </div>
</nav>