
@if( $merchants == 0 )
  <p>You do not currently have a merchant account setup to collect payments.</p>
@endif

@if( $merchants >= 1 )
  <p>Your merchant ID is {{$merchants}}.</p>
@endif