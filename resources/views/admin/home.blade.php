@extends('admin.layout')

@section('title') Dashboard - @endsection

@section('content')
	<h1 class="title titlebolder">Dashboard</h1>
	<div class="inner-content panel-body">
		<div class="row">
			<div class="col-md-6">
				<div class="totalfileshold">
					<div class="headtotfileshold"><h2>Total Users Registered</h2></div>
					<div class="bodytotfileshold">
						<div class="imgholder"><img src="http://app.fuseddocs.com/assets/images/totalicon_03.png"></div>
						<div class="textholdinfo">{{ $userCount }} <br/><span>Users</span></div>
					</div>
				</div>
			</div>
			<div class="col-md-6">
				<div class="totalfileshold">
					<div class="headtotfileshold"><h2>Total Users in Level</h2></div>
					<div class="bodytotfileshold">
						<h3>Free Users : {{ $data['free'] }} </h3>
						<h3>Professional Users : {{ $data['professional'] }} </h3>
						<h3>Enterprise Users : {{ $data['enterprise'] }} </h3>
					</div>
				</div>
			</div>
		</div>
	</div>
@endsection