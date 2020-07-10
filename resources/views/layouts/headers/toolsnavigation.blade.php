<nav class="navbar navbar-default">
  <div class="container-fluid">
    <div class="navbar-header logo">
      <a href="/">
        <img src="/assets/images/logo/fusedtools.png">
      </a>
    </div>
    <ul class="nav navbar-nav nav-item">
      <p class="text-center"></p>
      @if( Auth::user() )
      <li class="{{url('/dashboard') == url()->current() ? 'active' : ''}}">
        <a href="{{ url('/dashboard') }}"><i class="fa fa-dashboard"> </i> &nbsp; Dashboard</a>
      </li>

      <li class="dropdown {{ strpos(url()->current(), '/scripts/geo/') !== false ? 'active' : ''}}">
        <a class="dropdown-toggle" data-toggle="dropdown" href="#">
          <i class="fa fa-globe"></i>  &nbsp; 
          Geographic Tools 
          <span class="caret"></span>
        </a>
        <ul class="dropdown-menu">
          <li class="{{ url('/scripts/geo/postcodebasedowner') == url()->current() ? 'active' : ''}}">
            <a href="{{ url('/scripts/geo/postcodebasedowner') }}"><i class="fa fa-map-marker"></i> &nbsp; Postcode Based Owner</a>
          </li>
          <li class="{{url('/scripts/geo/countrybasedowner') == url()->current() ? 'active' : ''}}">
            <a href="{{ url('/scripts/geo/countrybasedowner') }}"><i class="fa fa-globe"></i> &nbsp; Country Based Owner</a>
          </li>
          <li class="{{url('/scripts/geo/postcodecontacttagging') == url()->current() ? 'active' : ''}}">
            <a href="{{ url('/scripts/geo/postcodecontacttagging') }}"><i class="fa fa-tag"></i> &nbsp; Postcode Contact Tagging</a>
          </li>
        </ul>
      </li>
      
      <li class="dropdown {{ strpos(url()->current(), '/scripts/') !== false && strpos(url()->current(), 'scripts/geo') === false ? 'active' : ''}}">
        <a class="dropdown-toggle" data-toggle="dropdown" href="#">
          <i class="fa fa-code"></i> &nbsp; 
          Useful Scripts 
          <span class="caret"></span>
        </a>
        <ul class="dropdown-menu">
          <li class="{{ url('/scripts/moveopportunities') == url()->current() ? 'active' : ''}}">
            <a href="{{ url('/scripts/moveopportunities') }}"><i class="fa fa-arrow-up"></i> Move Opportunities</a>
          </li>
          <li class="{{ url('/scripts/updatecreditcards') == url()->current() ? 'active' : ''}}">
            <a href="{{ url('/scripts/updatecreditcards') }}"><i class="fa fa-credit-card"></i> Update Credit Cards</a>
          </li>
          <li class="{{ url('/scripts/addtovalues') == url()->current() ? 'active' : ''}}">
            <a href="{{ url('/scripts/addtovalues') }}"><i class="fa fa-plus-circle"></i> Add To / Increment Fields</a>
          </li>
          <li class="{{ url('/scripts/namesfromorders') == url()->current() ? 'active' : ''}}">
            <a href="{{ url('/scripts/namesfromorders') }}"><i class="fa fa-envelope"></i> Order Products To Field</a>
          </li>
          <li class="{{ url('/scripts/copyvalues') == url()->current() ? 'active' : ''}}">
            <a href="{{ url('/scripts/copyvalues') }}"><i class="fa fa-copy"></i> Copy Values Between Fields</a>
          </li>
          <li class="{{ url('/scripts/calculatedates') == url()->current() ? 'active' : ''}}">
            <a href="{{ url('/scripts/calculatedates') }}"><i class="fa fa-calendar"></i> Calculate & Store Dates</a>
          </li>
        </ul>
      </li>

      <li class="dropdown {{ strpos(url()->current(), 'sync') !== false ? 'active' : ''}}">
        <a class="dropdown-toggle" data-toggle="dropdown" href="#">
          <i class="fa fa-refresh"></i> &nbsp; 
          Sync Tools 
          <span class="caret"></span>
        </a>
        <ul class="dropdown-menu">
          <li class="{{ strpos(url()->current(), 'sync') !== false ? 'active' : ''}}">
            <a href="{{ url('/sync/company/contact') }}"><i class="fa fa-vcard"></i> Company Contact Sync</a>
          </li>
        </ul>
      </li>

      <li class="dropdown {{ strpos(url()->current(), 'tag') !== false ? 'active' : ''}}">
        <a class="dropdown-toggle" data-toggle="dropdown" href="#">
          <i class="fa fa-book"></i> &nbsp; 
          Tag Tools 
          <span class="caret"></span>
        </a>
        <ul class="dropdown-menu">
          <li class="{{ strpos(url()->current(), 'tag') !== false ? 'active' : ''}}">
            <a href="{{ url('/tag/contact') }}"><i class="fa fa-pencil-square-o"></i> Bulk Contact Tagging</a>
          </li>
        </ul>
      </li>

      <li class="dropdown {{ strpos(url()->current(), 'csvimport') !== false ? 'active' : ''}}">
        <a class="dropdown-toggle" data-toggle="dropdown" href="#">
          <i class="fa fa-download"></i> &nbsp; 
          Import Tools
          <span class="caret"></span>
        </a>
        <ul class="dropdown-menu">
          <li class="{{ strpos(url()->current(), 'csvimport') !== false ? 'active' : ''}}">
            <a href="{{ url('/csvimport') }}"><i class="fa fa-table"></i> Import CSV</a>
          </li>
        </ul>
      </li>
      @endif
    </ul>
    @include('layouts.appquickswitch')
  </div>
</nav>