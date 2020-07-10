<nav class="">
  <ul class="nav navbar-nav navbar-right nav-item quick-switch">
    <li class="dropdown selected-module">
      <!-- <a class="dropdown-toggle" data-toggle="dropdown" href="#">
        @if (config('fusedsoftware.subdomain') == env('FUSEDSUITE_INVOICE_SUBDOMAIN'))
          <img src="/assets/images/logo/fusedinvoice.png" />
        @elseif (config('fusedsoftware.subdomain') == env('FUSEDSUITE_DOCS_SUBDOMAIN'))
          <img src="/assets/images/logo/fuseddocs.png" />
        @else
          <img src="/assets/images/logo/fusedtools.png" />
        @endif
        <span class="caret"></span>
      </a> -->
      <a class="dropdown-toggle" data-toggle="dropdown" href="#">
        <img src="/assets/images/logo/fusedsuite.png" />
        <span class="caret"></span>
      </a>
      <ul class="dropdown-menu dropdownUser">
        <li><a href="//{{ env('FUSEDSUITE_TOOLS_SUBDOMAIN') }}.{{ env('APP_URL') }}"><img src="/assets/images/logo/fusedtools.png" /></a></li>
        <li><a href="//{{ env('FUSEDSUITE_DOCS_SUBDOMAIN') }}.{{ env('APP_URL') }}"><img src="/assets/images/logo/fuseddocs.png" /></a></li>
        <li><a href="//{{ env('FUSEDSUITE_INVOICE_SUBDOMAIN') }}.{{ env('APP_URL') }}"><img src="/assets/images/logo/fusedinvoice.png" /></a></li>
      </ul>
    </li>
  </ul>
</nav>