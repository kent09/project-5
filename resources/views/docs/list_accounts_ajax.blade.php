<div class="pull-left" style="font-weight:bold;">
    PandaDoc
</div>
<div class="pull-right">
		@if ( count($panda_accounts) == 0 )
		<a class="btn btn-sm btn-info" href="https://app.pandadoc.com/oauth2/authorize?client_id=5f761211014489008a92&redirect_uri=http://fusedtools.com/public/manage-panda-account/save&scope=read+write&response_type=code">Add Account</a>
		@else 
			<a class="btn btn-sm btn-info" href="https://app.pandadoc.com/oauth2/authorize?client_id=5f761211014489008a92&redirect_uri=http://fusedtools.com/public/manage-panda-account/save&scope=read+write&response_type=code">Change Account</a>
		@endif
		</div>
	<br/>
	<br/>
	<table class="table table-striped">
		<tr>
			<th>API Key</th>
			<th>Date Created</th>
			<th>Status</th>
			<th>Action</th>
		</tr>
		@if ( count($panda_accounts) > 0 )
		
			@foreach ($panda_accounts as $account )
				<tr>
					<td>{{ @$account->api_key }}</td>
					<td>{{ date('d/m/Y h:i a', strtotime($account->created_at) ) }}</td>
					@if( @$account->active == 1)
						<td><span style="color:green">Active</span</td>
					@else 
						<td><span style="color:red">Inactive</span></td>
					@endif
					
					<td>
						@if( @$account->active == 1)
							{{--*/ $class 	= 'fa-lock' /*--}}
							{{--*/ $status 	= 'active' /*--}}
							{{--*/ $title 	= 'Inactive' /*--}}
							
						@else
							{{--*/ $class 	= 'fa-unlock' /*--}}
							{{--*/ $status 	= 'inactive' /*--}}
							{{--*/ $title 	= 'active' /*--}}
							
						@endif

						<a  class=" action-icons change-status" href="javascript:void(0)" data-id="{{@$account->id}}" data-status="{{ $status }}" title="{{ $title }}"><i class="fa {{ $class }}" aria-hidden="true"></i></a>
						<a href="javascript:void(0)" class=" action-icons delete-account" data-id="{{@$account->id}}" ><i class="fa fa-trash-o" aria-hidden="true" title="Delete"></i></a>
					</td>
				</tr>
			@endforeach
		@else 
			<tr>
				<td colspan="4" class="text-center">No Record Found.</td>
			</tr>
		@endif
	</table>
</div>
