@extends('admin.layout')

@section('title') Users - @endsection

@section('content')
    <h1 class="title">Users List</h1>
	<div class="panel-body inner-content" style="">
        <table class=" table table-striped" style="width:100%;">
        	<tr>
        		<td>Sr No.</td>
        		<td>User FirstName</td>
        		<td>User LastName</td>
        		<td>User Email</td>
        		<td>Membership</td>
        		<td>Actions</td>
        	</tr>
        	@if ( count($users) )
        	    @php $i = 1; @endphp
        		@foreach ( $users as $user)
        			<tr>
        				<td>{{ $i }}</td>
        				<td>{{ $user->first_name}}</td>
        				<td>{{ $user->last_name}}</td>
        				<td>{{ $user->email}}</td>
        				<td>{{ $user->userSubscription->plan->name or '' }}</td>
        				<td>
        				    <a href="#" class="btn btn-default"><i class="fa fa-eye"></i></a>
        				    <a href="#" class="btn btn-primary"><i class="fa fa-edit"></i></a>
        				    <a href="#" class="btn btn-danger"><i class="fa fa-trash"></i></a>
        				</td>
        			</tr>
        			@php $i++; @endphp
        		@endforeach
        	@else
        		<tr>
        			<td colspan="5"><center>No Record Found</center></td>
        		</tr>
        	@endif
        </table>
    </div>
@endsection