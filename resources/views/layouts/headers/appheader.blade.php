
<div class="header">
@if( Auth::user() )
  @include('layouts.headers.appsubheader')
@endif
@if (config('fusedsoftware.subdomain') == env('FUSEDSUITE_TOOLS_SUBDOMAIN'))
  @include('layouts.headers.toolsnavigation')
@elseif (config('fusedsoftware.subdomain') == env('FUSEDSUITE_DOCS_SUBDOMAIN'))
  @include('layouts.headers.docsnavigation')
@elseif (config('fusedsoftware.subdomain') == env('FUSEDSUITE_INVOICE_SUBDOMAIN'))
  @include('layouts.headers.invoicenavigation')
@elseif (config('fusedsoftware.subdomain') == env('FUSEDSUITE_APP_SUBDOMAIN'))
  @include('layouts.headers.appnavigation')
@elseif (config('fusedsoftware.subdomain') == env('FUSEDSUITE_ACCOUNT_SUBDOMAIN'))
  @include('layouts.headers.accountnavigation')
@endif
</div>
