<!--
<span class="help-block text-center success_msg" style=" color:green; margin-top:-49px;">
</span>
-->
@if ( Session::has('success') )
	<span class="help-block text-center success_msg" style=" color:green;">
		<strong>{{ Session::get('success') }}</strong>
		{{ Session::forget('success') }}
	</span>
@endif
@if ( Session::has('error') )
	<span class="help-block text-center" style=" color:#C24842;">
		<strong>{{ Session::get('error') }}</strong>
		{{ Session::forget('error') }}
	</span>
@endif

<div style="margin-top:-30px;">
	<span class="success_msg" style=" color:green; margin-left:38%;">
	</span>
</div>

<div class="" style="overflow-x: auto;">
	<table class="table table-striped" style="width:100%;">
		<tr>
			<td></td>
			<td>Error Type</td>
			<td>Message</td>
			<td>Roles</td>
			<td>Infs. Contact Id</td>
		</tr>
		@if ( count($notifications) )
			@foreach ( $notifications as $notty)
				<tr>
					<td><input type="checkbox" name="notty_check" class="notty-check" data-id="{{ $notty->id }}"></td>
					<td>{{ $notty->error_type }}</td>
					<td>{{ $notty->message }}</td>
					<td>{{ strlen($notty->role_data) > 50 ? substr($notty->role_data,0,50)."..." : $notty->role_data  }}</td>
					<td>{{ $notty->message }}</td>
				</tr>
			@endforeach
		@else
			<tr>
				<td colspan="5"><center>No Record Found</center></td>
			</tr>
		@endif
	</table>
</div>