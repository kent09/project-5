@extends('layouts.app')

@section('content')


<div class="backgrey managebillpage">

<form method="post" action="{{ url('/manageaccount') }}">
				{{ csrf_field() }}
<div class="">
	<div class="row">
		<div class="col-lg-6">
			<h2>Billing Details</h2>
			<div class="panel">
				<div class="panel-body">
					<div class="row form-group">
						<div class="col-lg-6"><input class="form-control" name="first_name" type="text" value="{{$user->first_name}}" placeholder="First Name" /></div>
						<div class="col-lg-6"><input class="form-control" name="last_name" type="text" value="{{$user->last_name}}" placeholder="Last Name" /></div>
					</div>
					<div class="row form-group">
						<div class="col-lg-12"><input class="form-control" name="company_name" type="text" value="{{$user->company_name}}" placeholder="Company Name" /></div>
					</div>
					<div class="row form-group">
						<div class="col-lg-12"><input class="form-control" name="email" type="email" value="{{$user->email}}" placeholder="Email" /></div>
					</div>
					<div class="row form-group">
						<div class="col-lg-12"><input class="form-control" name="phone" type="number" value="{{$user->phone}}" placeholder="Phone" /></div>
					</div>
					<div class="row form-group">
						<div class="col-lg-12"><input class="form-control" name="address1" type="text" value="{{$user->address1}}" placeholder="Address 1" /></div>
					</div>
					<div class="row form-group">
						<div class="col-lg-12"><input class="form-control" name="address2" type="text" value="{{$user->address2}}" placeholder="Address 2" /></div>
					</div>
					<div class="row form-group">
						<div class="col-lg-6"><input class="form-control" name="city" type="text" value="{{ $user->city}}" placeholder="City" /></div>
						<div class="col-lg-6"><input class="form-control" name="state" type="text" value="{{ $user->state}}" placeholder="State" /></div>
					</div>
					<div class="row form-group">
						<div class="col-lg-6"><input class="form-control" name="country" type="text" value="{{ $user->country}}" placeholder="Country" /></div>
						<div class="col-lg-6"><input class="form-control" name="post_code" type="text" value="{{ $user->post_code}}" placeholder="Post Code" /></div>
					</div>
					<div class="row form-group">
						<div class="col-lg-12"><button type="submit" id="orderbill">Update Billing Details</button></div>
					</div>
				</div>
			</div>
		</div>
		<div class="col-lg-6">
			<h2>Current Card Details</h2>
			<div class="formholdernew">
				<div class="row crecardhold">   
					<div class="col-lg-11 col-lg-offset-1">                 	
						<table width="100%" border="0" cellspacing="0" cellpadding="0">
							<tr>
								<td><input name="card_type" id="visa" type="radio" value="Visa" /><label for="visa"><img src="{{ asset('assets/images/creditcard_visa.jpg') }}" class="img-responsive"></label></td>
								<td><input name="card_type" id="mastercard" type="radio" value="MasterCard" /><label for="mastercard"><img src="{{ asset('assets/images/creditcard_mastercard.jpg') }}" class="img-responsive"></label></td>
							</tr>
						</table>
					</div>
				</div>
				<div class="row">
					<div class="col-lg-12"><input name="card_number" type="text" placeholder="Credit Card Number" /></div>
				</div>
				<div class="row">
					<div class="col-lg-6">
						<select name="month" >
							<option value="" selected disabled>Month</option>
							<option value="01">01</option>
							<option value="02">02</option>
							<option value="03">03</option>
							<option value="04">04</option>
							<option value="05">05</option>
							<option value="06">06</option>
							<option value="07">07</option>
							<option value="08">08</option>
							<option value="09">09</option>
							<option value="10">10</option>
							<option value="11">11</option>
							<option value="12">12</option>
						</select>
					</div>
					<div class="col-lg-6">
						<select name="year">
							<option value="" selected disabled>Year</option>
							@foreach( range( Carbon\Carbon::now()->year,2035)  as $year )
								<option value="{{ $year }}">{{ $year }}</option>
							@endforeach
						</select>
					</div>
				</div>
				<div class="row">
					<div class="col-lg-12"><input name="cvv" type="text" placeholder="CVV" /></div>
				</div>
				<div class="row">
					<div class="col-lg-12"><button type="submit" id="orderbill">Update Credit Card</button></div>
				</div>
			</div>     
		</div>
	</div>
</div>




<div class="contentform spacerforty">
<h2>Your package</h2>
	<div class="row">
	
	@foreach($plans as $plan)
				@if($plan->name !== 'Free')
		
				<div class="col-lg-6">
			<div class="packageholderin">
			  <h3>{{$plan->name}}</h3>
				<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
  @foreach($plan->product as $product)
  
  
	<td align="center" valign="top" class="planholderpack">
	<h4>@if ($product->charge_freq=='month')
			Monthly
			@else
			Annual
		@endif</h4>
	<p><sup style="font-weight:normal;">$</sup><span class="price">{{$product->charge}}</span> /{{$product->charge_freq}}</p>
	<p><input name="Package" id="r{{$product->id}}" type="radio" value="{{$product->id}}" onclick="$('.ordertotalamt').text('${{$product->charge}}.00/{{$product->charge_freq}}')" class="planField"><label for="r{{$product->id}}" class="btnplanselnew">Select</label></p>    
	@if ($product->charge_freq=='year')
<p><span class="freetext">1 month free</span></p>
		@endif
	</td>
		
	
   @endforeach 
  </tr>
</table>


@if ($plan->name=='Agency')
<p>5 Infusionsoft Account</p>
<p><strong>1500</strong> Task Allowance / month</p>
<p><strong>2500</strong> Records / day</p>
<p class="smalltext">CSV Import Limit</p>
<p class="accesstext">Access to Premium Scripts</p>
@else
<p>1 Infusionsoft Account</p>
<p><strong>500</strong> Task Allowance / month</p>
<p><strong>1000</strong> Records / day</p>
<p class="smalltext">CSV Import Limit</p>
<p class="accesstext">Access to Premium Scripts</p>
@endif

</div>
		</div>
			@endif
		@endforeach
		
		
	</div>
</div>


<div class="contentform spacerforty freeplancheck">
<div class="row">
		<div class="col-lg-12" align="center";>
			<input name="Package" id="freeplanac" type="radio" onclick="$('.ordertotalamt').text('$0.00')" value="" style="display:inline-block; width:35px;" /><label for="freeplanac"><strong>Free Plan</strong> - 10 tasks a month, 100 csv records, 1 Infusionsoft account.</label>
		</div>
</div>
</div>

<div class="contentform spacerforty">
<div class="formholdernew">
	<div class="row">
		<div class="col-lg-4 col-lg-offset-1">
		<p class="ordertotal">Order Total</p>
		<p class="ordertotalamt">$0.00</p>
		</div>
		<div class="col-lg-6">	
	<button type="submit" id="orderbillamount"><img src="{{ asset('assets/images/envelop.jpg') }}"> Order Now</button>
		</div>
	</div>
	
</div>


<div class="row">
		<div class="col-lg-12" align="center">
	<p class="spacerforty"><img src="{{ asset('assets/images/eway_07.jpg') }}"></p>
	</div>
	</div>
</div>

</form>


<!-- jQuery -->
<script>
	var siteUrl = "{{ url('/') }}";
</script>


</div>
@endsection
