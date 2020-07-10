<nav class="navbar navbar-default">
  <div class="container-fluid">
    <div class="navbar-header logo">
      <a href="/">
        <img src="/assets/images/logo/fuseddocs.png">
      </a>
    </div>
    <ul class="nav navbar-nav nav-item">
      <p class="text-center"></p>
      @if( Auth::user() )
      <li class="{{ url('/dashboard') == url()->current() ? 'active' : ''}}">
        <a href="{{ url('/dashboard') }}"><i class="fa fa-dashboard"> </i> &nbsp; Dashboard</a>
      </li>
      <li class="{{ url('/pandadocs') == url()->current() ? 'active' : ''}}">
        <a href="{{ url('/pandadocs') }}"><i class="fa fa-folder"> </i> &nbsp; Pandadocs Manager</a>
      </li>
      <li class="{{ url('/docusign') == url()->current() ? 'active' : ''}}">
        <a href="{{ url('/docusign') }}"><i class="fa fa-folder"> </i> &nbsp; Docusign Manager</a>
      </li>
      <li class="{{ url('/pandadocs/setupguide') == url()->current() ? 'active' : ''}}">
        <a href="{{ url('/pandadocs/setupguide') }}"><i class="fa fa-info-circle"> </i> &nbsp; Pandadocs Guide</a>
      </li>
      <li class="{{ url('/history') == url()->current() ? 'active' : ''}}">
        <a href="{{ url('/history') }}"><i class="fa fa-history"> </i> &nbsp; Document History</a>
      </li>
      <li class="{{ url('/notifications') == url()->current() ? 'active' : ''}}">
        <a href="{{ url('/notifications') }}"><i class="fa fa-warning"> </i> &nbsp; Document Error log</a>
      </li>
      @endif
    </ul>
    @include('layouts.appquickswitch')
  </div>
</nav>