<div style="margin-top:-30px;">
	<span class="success_msg" style=" color:green; margin-left:38%;">
	</span>
</div>

<table class=" table table-striped" style="width:100%;">
	<tr>
		<td>Created At</td>
		<td>Infs App</td>
		<td>Document ID</td>
		<td>Document Status</td>
		<td>Infs. Contact Id</td>
		<td>Tag Applied (ID)</td>
	</tr>
	@if ( count($notifications) )
		@foreach ( $notifications as $notty)
			<tr>
				<td>{{ $notty->created_at }}</td>
				<td>{{ $notty->infsAccount->name or '' }}</td>				
				<td>{{ $notty->completed_id }}</td>
				<td>{{ $notty->document_status }}</td>
				<td>{{ $notty->contactId }}</td>
				<td>{{ $notty->tag_applied }}</td>
			</tr>
		@endforeach
	@else
		<tr>
			<td colspan="5"><center>No Record Found</center></td>
		</tr>
	@endif
</table>
