<div class="inner-content panel-body">
@if($accounts)
	    <input type="hidden" name="_token" value="{{ csrf_token() }}">
        @foreach($accounts as $account)
    		<div class="row">
    			<div class="col-md-2" align="center"><br/>
    			    @if( $account->expire_date < Carbon\Carbon::now() )
                    <div title="Reauth" class="reauth not reauthBtn" data-id="{{ $account->id }}">
                        <i class="fa fa-refresh"></i></div>
                    @else
                    <div class="reauth done">
                        <i class="fa fa-refresh"></i></div>
                    @endif
                </div>
                <div class="col-md-8">
                    <h3>{{ $account->account }}</h3>
                    @if( $account->expire_date < Carbon\Carbon::now() )
                        <p>Woops! Their is an issue, please refresh token by clicking red Circle</p>
                    @else
                        <p>Success! It's working!</p>
                    @endif
                </div>
                <div class="col-md-2">
                    <button class="btn btn-danger removeAccount" data-id="{{ $account->id }}"><i class="fa"></i> Remove</button>
                </div>
    		</div><hr>
        @endforeach
    @endif
</div>