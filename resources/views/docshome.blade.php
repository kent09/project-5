@php
	$layout = 'layouts.appaccount';

	switch(config('fusedsoftware.subdomain')) {
		case env('FUSEDSUITE_TOOLS_SUBDOMAIN'):
			$layout = 'layouts.apptools';
			break;
		case env('FUSEDSUITE_DOCS_SUBDOMAIN'):
			$layout = 'layouts.appdocs';
			break;
		case env('FUSEDSUITE_INVOICE_SUBDOMAIN'):
			$layout = 'layouts.appinvoices';
			break;
	}
@endphp
@extends($layout)
@section('title', 'Getting Started Checklist')
@section('content')
<?php if (Session::has('error')): ?>
<span class="help-block text-center" style=" color:#C24842;">
<strong><?php echo(Session::get('error')); ?></strong>
<?php echo(Session::forget('error')); ?>
</span>
<?php endif; ?>
<div class="panel">
  <div class="panel-body">
    <div class="row">
      <div class="col-md-12">
        <div class="">
          <h2>Getting Started Checklist</h2>
        </div>
        <div class="bodytotfileshold leftalign">
          <ul>
            <li id="isAccount" class="comp">Linked Pandadocs Account</li>
            <li id="isAccount" class="comp">Linked Docusign Account</li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
@section('script')
<script>  
  $( document ).ready(function() {
      if("{{\Auth::user()->usersIsAccounts}}"=== ""){
		$('#isAccount').addClass('incomp');
      }else {
		$('#isAccount').addClass('comp');
  	  } 
  });
</script>
@endsection