@extends('layouts.app')

@section('content')
	<h1 class="title">Account Settings</h1>
	<div class="inner-content panel-body">
	    
		<div class="col-md-6 col-md-offset-3">
		    @if(Session::get('success'))
                <div class="alert alert-success alert-dismissable">
                    <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                    {{ Session::get('success') }}
                </div>
            @endif
            @if(Session::get('error'))
                <div class="alert alert-danger alert-dismissable">
                    <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                    {{ Session::get('error') }}
                </div>
            @endif
        	<form action="{{ url('/account-settings') }}" method="POST">
        	    {{ csrf_field() }}
        		<div class="form-group">
        			<label >Additional Notification Emails: <small><i>(Comma separated)</i></small></label>
        			<input type="text" class="form-control" name="email_list" value="{{ \CommanHelper::emailList() }}">
        		</div>
        		<button type="submit" class="btn btn-primary">Save</button>
        	</form>
        </div>
	</div>
@endsection