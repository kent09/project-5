@extends('layouts.appaccount')
@section('title', 'Billing')
@section('content')
<link rel="stylesheet" href="{{ url('assets/css/pricing.css')}}" crossorigin="anonymous">
<link rel="stylesheet" href="{{ url('assets/css/stripeelement.css')}}" crossorigin="anonymous">
<div class="loading hide">Loading&#8230;</div>
<div class="">
	<!-- <form action="{{ url('/billing') }}" method="post"  id="frm-charge"> -->
		<div id="generic_price_table">   
			<div class="row">
		        
		    </div>
			<div class="row current-plan">
			    <div class="col-md-4 col-md-offset-4">
			        <div class="row">
			        	<div class="col-md-12">
				            <div class=" clearfix active">
				                                         
				                <div class="generic_feature_list">
				                	
				                </div>
				                <!--//BUTTON END-->
				            </div>
				            <!--//PRICE CONTENT END-->
				        </div>
					</div>
			    </div>
			        <!--//PRICE HEADING END-->
			</div>
		    <div class="row">
		        <div class="col-md-12">
		            <!--PRICE HEADING START-->
		            <div class="price-heading clearfix">
		                <h1>Premium Plans</h1>
		            </div>
					<!--//PRICE HEADING END-->
					<div class="subscription-type">
						<div class="btn-group btn-toggle"> 
							<button class="btn btn-default">Monthly</button>
							<button class="btn btn-primary active">Annual</button>
						</div>	
					</div>
		        </div>
		    </div>
			<div class="row">

				<div class="col-md-4 col-sm-12">
		        	<!--PRICE CONTENT START-->
		            <div class="generic_content active  clearfix">
		                <!--HEAD PRICE DETAIL START-->
		                <div class="generic_head_price clearfix">
		                    <!--HEAD CONTENT START-->
		                    <div class="generic_head_content clearfix">
		                    	<!--HEAD START-->
		                        <div class="head">
		                            <span>Basic</span>
		                        </div>
		                        <!--//HEAD END-->
		                    </div>
		                    <!--//HEAD CONTENT END-->
		                    <!--PRICE START-->
		                    <div class="generic_price_tag clearfix">	
		                        		                    </div>
		                    <!--//PRICE END-->
		                </div>                            
		                <!--//HEAD PRICE DETAIL END-->
		                <!--FEATURE LIST START-->
		                <div class="generic_feature_list">
		                	<ul> 
								<li>500 Tokens</li>
								<li><span>$500</span></li>
								<li>(1 Month Free)</li>
							</ul>
		                </div>
		                <!--//FEATURE LIST END-->
		                <!--BUTTON START-->
		                <div class="generic_price_btn clearfix">
		                			                </div>
		                <!--//BUTTON END-->
		            </div>
		            <!--//PRICE CONTENT END-->
				</div>

				<div class="col-md-4 col-sm-12">
		        	<!--PRICE CONTENT START-->
		            <div class="generic_content  clearfix">
		                <!--HEAD PRICE DETAIL START-->
		                <div class="generic_head_price clearfix">
		                    <!--HEAD CONTENT START-->
		                    <div class="generic_head_content clearfix">
		                    	<!--HEAD START-->
		                        <div class="head">
		                            <span>Power User</span>
		                        </div>
		                        <!--//HEAD END-->
		                    </div>
		                    <!--//HEAD CONTENT END-->
		                    <!--PRICE START-->
		                    <div class="generic_price_tag clearfix">	
		                        		                    </div>
		                    <!--//PRICE END-->
		                </div>                            
		                <!--//HEAD PRICE DETAIL END-->
		                <!--FEATURE LIST START-->
		                <div class="generic_feature_list">
		                	<ul> 
								<li>2000 Tokens</li>
								<li><span>$900</span></li>
								<li>(1 Month Free)</li>
							</ul>
		                </div>
		                <!--//FEATURE LIST END-->
		                <!--BUTTON START-->
		                <div class="generic_price_btn clearfix">
		                			                </div>
		                <!--//BUTTON END-->
		            </div>
		            <!--//PRICE CONTENT END-->
				</div>

				<div class="col-md-4 col-sm-12">
		        	<!--PRICE CONTENT START-->
		            <div class="generic_content  clearfix">
		                <!--HEAD PRICE DETAIL START-->
		                <div class="generic_head_price clearfix">
		                    <!--HEAD CONTENT START-->
		                    <div class="generic_head_content clearfix">
		                    	<!--HEAD START-->
		                        <div class="head">
		                            <span>Enterprise</span>
		                        </div>
		                        <!--//HEAD END-->
		                    </div>
		                    <!--//HEAD CONTENT END-->
		                    <!--PRICE START-->
		                    <div class="generic_price_tag clearfix">	
		                        		                    </div>
		                    <!--//PRICE END-->
		                </div>                            
		                <!--//HEAD PRICE DETAIL END-->
		                <!--FEATURE LIST START-->
		                <div class="generic_feature_list">
		                	<ul> 
								<li>6000 Tokens</li>
								<li><span>$1,600</span></li>
								<li>(1 Month Free)</li>
							</ul>
		                </div>
		                <!--//FEATURE LIST END-->
		                <!--BUTTON START-->
		                <div class="generic_price_btn clearfix">
		                			                </div>
		                <!--//BUTTON END-->
		            </div>
		            <!--//PRICE CONTENT END-->
				</div>
			</div>

			<div class="plan-info">
				<table class="table table-bordered application-token">
					<thead>
					<tr>
						<th><img src="/assets/images/logo/fusedtools.png"></th>
						<th>Tool Token Cost</th>
						<th>Monthly Tool Usage</th>
						<th>Monthly Token Usage</th>
					</tr>
					</thead>
					<tbody>
					<tr>
						<td>Script Tasks</td>
						<td>1 per script</td>
						<td>5 records</td>
						<td>5 token</td>
					</tr>
					<tr>
						<td>CSV Import Limit</td>
						<td>1 per 100</td>
						<td>250 records</td>
						<td>2 token</td>
					</tr>

					<tr>
						<td>Gep Tools</td>
						<td>1 per 100 records</td>
						<td>150 records</td>
						<td>5 token</td>
					</tr>
					</tbody>
				</table>

				<table class="table table-bordered application-token">
					<thead>
					<tr>
						<th><img src="/assets/images/logo/fuseddocs.png"></th>
						<th>Tool Token Cost</th>
						<th>Monthly Tool Usage</th>
						<th>Monthly Token Usage</th>
					</tr>
					</thead>
					<tbody>
					<tr>
						<td>Document Setting</td>
						<td>1 per script</td>
						<td>5 records</td>
						<td>5 token</td>
					</tbody>
				</table>

				<table class="table table-bordered application-token">
					<thead>
					<tr>
						<th><img src="/assets/images/logo/fusedinvoice.png"></th>
						<th>Tool Token Cost</th>
						<th>Monthly Tool Usage</th>
						<th>Monthly Token Usage</th>
					</tr>
					</thead>
					<tbody>
					<tr>
						<td>Xero Tools</td>
						<td>1 per script</td>
						<td>5 records</td>
						<td>5 token</td>
					</tr>
					</tbody>
				</table>
			</div>
			
		</div>
		<div class="">
			<div class="row">
				<div class="col-lg-6">
					<h2>Billing Details</h2>
					<div class="panel panel-default">
						<div class="panel-heading">Your Details</div>
						<div class="panel-body">
							<input type="hidden" name="_token" value="{{csrf_token()}}">
							<div class="">
								<div class="row form-group">
									<div class="col-md-6">
										<label>First Name</label>
										<input class="form-control" required name="first_name" value="{{ $user->userAddress->first_name or '' }}" type="text" placeholder="First Name" />
									</div>
									<div class="col-md-6">
										<label>Last Name</label>
										<input class="form-control" required name="last_name" value="{{ $user->userAddress->last_name or '' }}" type="text" placeholder="Last Name" />
									</div>
								</div>
								<div class="row form-group">
									<div class="col-md-12">

										<label>Company Name</label>
										<input class="form-control" required name="company_name" value="{{ $user->userAddress->company_name or '' }}" type="text" placeholder="Company Name" />
									</div>
								</div>
								<div class="row form-group">
									<div class="col-md-8">

										<label>Email(s) for invoice notification (separated by comma)</label>
										<input class="form-control" required name="email" type="text" value="{{ $user->userAddress->email_list or '' }}" placeholder="Email" />
									</div>
									<div class="col-md-4">
										<label>Phone</label>
										<input class="form-control" required name="phone" type="number" value="{{ $user->userAddress->phone or '' }}" placeholder="Phone" />
									</div>
								</div>
								<br/><br/>
								<div class="row form-group">
									<div class="col-md-6">
										<label>Address 1</label>
										<input class="form-control" required name="address1" type="text" value="{{ $user->userAddress->address1 or '' }}" placeholder="Address 1" />
									</div>
									<div class="col-md-6">
										<label>Address 2</label>
										<input class="form-control" name="address2" type="text" value="{{ $user->userAddress->address2 or '' }}" placeholder="Address 2" />
									</div>
								</div>
								<div class="row form-group">
									<div class="col-md-6">
										<label>City</label>
										<input class="form-control" required name="city" type="text" value="{{ $user->userAddress->city or '' }}" placeholder="City" />
									</div>
									<div class="col-md-6">
										<label>Country</label>
										<input class="form-control" required name="country" type="text" value="{{ $user->userAddress->country or '' }}" placeholder="Country" />
									</div>
								</div>
								<div class="row form-group">
									<div class="col-md-6">
										<label>State</label>
										<input class="form-control" required name="state" value="{{ $user->userAddress->state or '' }}" type="text" placeholder="State" />
									</div>
									<div class="col-md-6">
										<label>Postcode</label>
										<input class="form-control" required maxlength="5" name="post_code" value="{{ $user->userAddress->post_code or '' }}" type="text" placeholder="Postcode" />
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="col-lg-6">
					<div class="payment-header-card">
						<h2>Payment Details</h2>
						<button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#myModal">
							Add Credit Card
						</button>
					</div>
					<div class="clearfix"></div>
					<div class="panel panel-default">
						<div class="panel-heading">Enter your card details</div>
						
						<div class="panel-body">
							<div class="row">
								<div class="col-md-12">
									<div id="card-element"></div> 
									<br/>
									<div id="card-errors" role="alert"></div>
								</div>
							</div>
							<div class="row text-right">
								<div class="col-md-12">
									<img class="img" src="{{ asset('assets/images/powered_by_stripe.png') }}" />
								</div>
							</div>
							
							<hr/>
							<div class="row marbottom martop">
								<div class="col-lg-12">
									<p class="ordertotal">Order Total</p>
									<p class="ordertotalamt">$0.00</p>
								</div>
							</div>
							<div class="row">
								<div class="col-lg-12">	
									<button type="submit" class="btn btn-primary btn-lg btn-block"  ><span class="fa fa-check"> </span> Order Now</button>
								</div>
							</div>
							<div class="row">
								<div class="col-lg-12">	
									<p><small>By proceeding with this order you agree to the terms & privacy policy outlined <a href="{{ url('/page/privacy') }}"> here </a>.</small></p>
								</div>
							</div>
							
						</div>
					</div>   
				</div>
			</div>
		</div>

		<div class="">
			<h3>Invoice History</h3>
			<table class="table table-bordered">
				<thead>
					<tr>
						<th>Date</th>
						<th>Description</th>
						<th>Amount</th>
						<th></th>
					</tr>
					</thead>
					<tbody>
					<tr>
						<td>6 Jan 2020</td>
						<td>Fused Tools Basic</td>
						<td>$50.00</td>
						<td><button type="button" class="btn btn-primary btn-xs">View Invoice</button></td>
					</tr>
					<tr>
						<td>6 Jan 2020</td>
						<td>Fused Tools Basic</td>
						<td>$50.00</td>
						<td><button type="button" class="btn btn-primary btn-xs">View Invoice</button></td>
					</tr>
					<tr>
						<td>6 Jan 2020</td>
						<td>Fused Tools Basic</td>
						<td>$50.00</td>
						<td><button type="button" class="btn btn-primary btn-xs">View Invoice</button></td>
					</tr>
					<tr>
						<td>6 Jan 2020</td>
						<td>Fused Tools Basic</td>
						<td>$50.00</td>
						<td><button type="button" class="btn btn-primary btn-xs">View Invoice</button></td>
					</tr>
				</tbody>
			</table>
			<div class="clearfix"></div>
		</div>
	</form>
</div>
@endsection
@section('script')
<script src="https://js.stripe.com/v3/"></script>

<script>
	var planSelected = false;
	var stripe = Stripe('{{env("STRIPE_KEY")}}');

	$('.btn-toggle').click(function() {
		$(this).find('.btn').toggleClass('active');  
		
		if ($(this).find('.btn-primary').size()>0) {
			$(this).find('.btn').toggleClass('btn-primary');
		}		
		$(this).find('.btn').toggleClass('btn-default');
		
	});

	// Create an instance of Elements
	var elements = stripe.elements();

	// Custom styling can be passed to options when creating an Element.
	// (Note that this demo uses a wider set of styles than the guide below.)
	var style = {
		base: {
			color: '#32325d',
			lineHeight: '18px',
			fontFamily: '"Helvetica Neue", Helvetica, sans-serif',
			fontSmoothing: 'antialiased',
			fontSize: '16px',
			'::placeholder': {
				color: '#aab7c4'
			}
		},
		invalid: {
			color: '#fa755a',
			iconColor: '#fa755a'
		}
	};

	// Create an instance of the card Element
	var card = elements.create('card', {style: style});

	// Add an instance of the card Element into the `card-element` <div>
	card.mount('#card-element');

	// Handle real-time validation errors from the card Element.
	card.addEventListener('change', function(event) {
		var displayError = document.getElementById('card-errors');
		if (event.error) {
			displayError.textContent = event.error.message;
		} else {
			displayError.textContent = '';
		}
	});

	// Handle form submission
	var form = document.getElementById('frm-charge');
	form.addEventListener('submit', function(event) 
	{
		event.preventDefault();
		if(!planSelected)
		{
			alert("Please select a plan.");
			return false;
		}

		$('.loading').removeClass("hide");

		stripe.createToken(card).then(function(result) 
		{
			if (result.error) 
			{
				// Inform the user if there was an error
				var errorElement = document.getElementById('card-errors');
				errorElement.textContent = result.error.message;
				$('.loading').addClass("hide");
			} 
			else 
			{
				// Send the token to your server
				console.log(result.token);
				var resultTokenStr = JSON.stringify(result.token);
				console.log(resultTokenStr);
				$('#frm-charge').append($('<input type="hidden" name="stripeToken" />').val(resultTokenStr));
				$('#frm-charge').submit();
			}
		});
	});

	$(document).on('click', '.btnSelect', function()
	{
		$('.generic_price_btn a').removeClass('active-btn');
		$(this).addClass('active-btn');
		var id = $(this).attr('data-id');
		$('#r'+id).prop("checked", true);
		$('#r'+id).click(); //simulate clicking radio button
		planSelected = true;
		
		$('.generic_content').removeClass("active");
		$(this).closest('.generic_content ').addClass("active");
		return false;
	});

	$('form').submit(function(){
		alert($(this["options"]).val());
		return false;
	});
</script>
@endsection