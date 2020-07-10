@extends('layouts.apptools')
@section('title', 'Order Confirmation')
@section('content')
	<link rel="stylesheet" href="{{ url('assets/css/stripeelement.css')}}" crossorigin="anonymous">
	<style>
		span.help-block{
			color: red;
		}

	</style>
	<div class="loading hide">Loading&#8230;</div>
	<div class="backgrey managebillpage pt30">
	    <div class="">
	        @if ($errors->any() )
				<div class="alert alert-danger">
					<ul>
						@foreach ($errors->all() as $error)
							<li>{{ $error }}</li>
						@endforeach
					</ul>
				</div>
			@endif
			<form action="{{ $changePlan ? url('/changeplan/confirm') : url('/billing/confirm') }}" method="post" id="frm-confirm">
				{{ csrf_field() }}
				<input type="hidden" name="Package" value="{{ $planProduct->id }}">
				<div class="row order-confirm-header">
					<div class="col-md-6">
						<h1>Order confirmation</h1>
					</div>
				</div>

				<div class="panel panel-order-confirm">
					<div class="panel-body">
						<div class="row">
							<div class="col-md-6">
								<div class="row">
									<div class="col-md-12">
										<div class="confirm-info">
											<h4>Your Information</h4>
											<hr/>
											<p><strong>{{ $user->userAddress->first_name . " " . $user->userAddress->last_name }}</strong></p>
											<p>{{ $user->userAddress->company_name }}</p>
											<p>{{ $user->email }} 
												@if(!empty($user->userAddress->email_list))
													<a data-html="true"  tabindex="0" role="button" data-toggle="popover" data-trigger="focus" title="Additional emails that will be notified" data-content="{{ str_replace(',', '<br/>', $user->userAddress->email_list) }}">(See other emails)</a> 
												@endif
												
											</p>
											<p>{{ $user->userAddress->phone }}</p>
										</div>
									</div>
								</div>
								<div class="row">
									<div class="col-md-12">
										<div class="confirm-payment">
											<h4>Payment</h4>
											<hr/>
											<p><span class="fa fa-cc-{{ strtolower($changePlan ? $user->card_brand : $stripeToken->card->brand ) }}" style="font-size: 26px; "> </span></p>
											<span><strong>{{ $changePlan ? $user->card_brand : $stripeToken->card->brand  }}</strong> ending in: {{ strtolower($changePlan ? $user->card_last_four : $stripeToken->card->last4) }} </span>
										</div>
									</div>
								</div>
							</div>
							<div class="col-md-6">
								<div class="row">
									<div class="col-md-12">
										<div class="confirm-info">
											<h4>Billing Address</h4>
											<hr/>
											<p>{{ $user->userAddress->address1 }}</p>
											<p>{{ $user->userAddress->address2 }}</p>
											<p>{{ $user->userAddress->city . ", " .  $user->userAddress->state . " " . $user->userAddress->post_code }}</p>
											<p>{{ $user->userAddress->country }}</p>
											<p>&nbsp;</p>
										</div>
									</div>
								</div>

								<div class="row text-right">
									<div class="col-md-12">
										<div class="confirm-action-btn">
											<h4 class="text-left">Order Summary</h4>
											<hr/>
											<h4>Plan: <strong>{{ $planProduct->plan->name }} - {{ $planProduct->charge_freq }}ly</strong></h4>
											<h4>Price: <strong>${{ number_format($planProduct->charge, 2) }}</strong></h4>
											<h4>Less: <strong>{{ $proratedLessAmount == 0 ? '$0' : '($' . number_format($proratedLessAmount, 2) . ')' }}</strong></h4>
											@if( $proratedLessAmount != 0 )
												<p>*  <small>These credits come from pro-rating during plan changes.</small></p>
											@endif
											<hr/>
											<h4>Order Total:&nbsp;&nbsp; <span class="order-total">${{ number_format($planProduct->charge - $proratedLessAmount, 2) }} </span></h4>
											<br/>
											<button type="submit" class="btn btn-primary btn-lg"  > &nbsp;&nbsp;&nbsp;&nbsp; Confirm Order &nbsp;&nbsp;&nbsp;&nbsp; </button>
											<a href="{{ url('billing') }}" class="btn btn-default btn-lg"  > &nbsp;&nbsp;&nbsp;&nbsp; Edit &nbsp;&nbsp;&nbsp;&nbsp; </a>
											
										</div>
									</div>
								</div>
							</div>
						</div>

						<div class="row text-right" style="margin-top: 10px;">
							<div class="col-md-12">
								<p><small>By proceeding with this order you agree to the terms & privacy policy outlined <a href="{{ url('/page/privacy') }}"> here </a>.</small></p>
							</div>
						</div>
					</div>
				</div>
			</form>
		</div>
	</div>

	<script>
		$(function () {
			$('[data-toggle="popover"]').popover()
		});

		$(document).on('submit', '#frm-confirm', function()
		{
			$('.loading').removeClass("hide");
		})
	</script>
@endsection