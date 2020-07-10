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
@extends('layouts.appaccount')
@section('title', 'Support')
@section('content')
    <h1 class="title">Support</h1>
	@if( Session::has('error') )
    	<span class="help-block text-center" style=" color:#C24842;">
    		<strong>{{ Session::get('error') }}</strong>
    	</span>
	@endif
    @if ( Session::has('success') )
    	<span class="help-block text-center" style=" color:green;">
    		<strong>{{ Session::get('success') }}</strong>
    		{{ Session::forget('success') }}
    	</span>
    @endif
	
	<div class="inner-content panel-body">
		<div class="row">
			<div class="col-md-6 col-md-offset-3 mb30">
				<form action="{{ url('/support') }}" method="post">
				    {{ csrf_field() }}
                	<div class="form-group{{ $errors->has('name') ? ' has-error' : '' }}">
                		<label for="name">Name*</label>
                		<input type="text" name="name" value="{{ $user['fullname'] }}" class="form-control" id="name">
                	</div>
                	<div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
                		<label for="email">Contact Email*</label>
                		<input type="email" name="email" value="{{ $user['email'] }}" class="form-control" id="email">
                	</div>
                	<div class="form-group{{ $errors->has('type') ? ' has-error' : '' }}">
                		<label>Inquiry Type*</label>
                		<select name="type" class="form-control">
                			<option value="Sales">Sales</option>
                			<option value="Setup">Setup</option>
                			<option value="Feature Request">Feature Request</option>
                			<option value="Bug Report">Bug Report</option>
                			<option value="Other">Other</option>
                		</select>
                	</div>
                	<div class="form-group">
                		<label for="phone">Contact Number (Optional)</label>
                		<input type="text" name="phone" value="" class="form-control" id="phone">
                	</div>
                	<div class="form-group{{ $errors->has('message') ? ' has-error' : '' }}">
                		<label for="message">Message*</label>
                		<textarea name="message" class="form-control" id="message" style="height:100px;"></textarea>
                	</div>
                	<button type="submit" class="{{ $classButton }}">Send Request</button>
                </form>

			</div>
		</div>
	</div>
	
@endsection
