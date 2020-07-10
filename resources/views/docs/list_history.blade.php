@extends('layouts.appdocs')
@section('title', 'Document History')
@section('content')
	<h1 class="title">Document History</h1>
	<div class="panel">
		<div class="panel-body inner-content" style="">
			@include('docs/_list_history_ajax')
		</div>
	</div>
		 
	<style>
		.action-icons i {
			font-size:18px;
			margin-right:5px;
		}
	</style>
	
@endsection
