<div class="modal fade" id="upgrade-modal" tabindex="-1" role="dialog" aria-labelledby="Order Checkout" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered container" role="document">
        <div class="modal-content">
            <div class="modal-body">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">X</span>
                </button>
                <div class="order-checkout">
                    <div class="title">Order Checkout</div>
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th scope="col">Date</th>
                                <th scope="col">Description</th>
                                <th scope="col">Amount</th>
                            </tr>
                        </thead>
                        @if($subscriptions_plan)
                        <tbody>
                            @foreach($subscriptions_plan as $subscribe_plan)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($subscribe_plan->start_date)->format('d M Y') }}</td>
                                
                                @foreach($plans as $plan)
                                    @if($subscribe_plan->plan->id == $plan->stripe_sub_id)
                                        <td>{{ $plan->label }} {{ $plan->billing_period }}</td>
                                    @endif
                                @endforeach

                                <td>$ {{ number_format($subscribe_plan->plan->amount/100, 2, '.', ',') }} / {{ $subscribe_plan->plan->interval }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        @endif
                    </table>
                </div>
                <form method="post" id="order-card" action="{{ url('/billing/confirm') }}">
                    {{ csrf_field() }}
                    <input type="hidden" name="planid">
                    <input type="hidden" name="paymentid" id="order-paymentid">
                    <div class="row">
                        <div class="col-md-6 billing-details">
                            <div class="title">Billing Details</div>
                                
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group @if ($errors->has('first_name')) has-error @endif">
                                        <label>First Name</label>
                                        <input type="text" name="first_name" value="{{ !empty($user_address) ? $user_address->first_name : '' }}" class="form-control">
                                        @if ($errors->has('first_name')) <p class="help-block">This field is required.</p> @endif
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group @if ($errors->has('last_name')) has-error @endif">
                                        <label>Last Name</label>
                                        <input type="text" value="{{ !empty($user_address) ? $user_address->last_name : '' }}" name="last_name" class="form-control">
                                        @if ($errors->has('last_name')) <p class="help-block">This field is required.</p> @endif
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Company Name</label>
                                <input type="text" name="company_name" value="{{ !empty($user_address) ? $user_address->company_name : '' }}" class="form-control">
                            </div>
                            <div class="form-group @if ($errors->has('address1')) has-error @endif">
                                <label>Address 1</label>
                                <input type="text" name="address1" class="form-control" value="{{ !empty($user_address) ? $user_address->address1 : '' }}">
                                @if ($errors->has('address1')) <p class="help-block"> This field is required.</p> @endif
                            </div>
                            <div class="form-group">
                                <label>Address 2</label>
                                <input type="text" name="address2" class="form-control" value="{{ !empty($user_address) ? $user_address->address2 : '' }}">
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group @if ($errors->has('city')) has-error @endif">
                                        <label>City</label>
                                        <input type="text" name="city" value="{{ !empty($user_address) ? $user_address->city : '' }}" class="form-control">
                                    </div>
                                    <div class="form-group @if($errors->has('state')) has-error @endif">
                                        <label>State</label>
                                        <input type="text" name="state" class="form-control" value="{{ !empty($user_address) ? $user_address->state : '' }}">
                                        @if ($errors->has('state')) <p class="help-block">This field is required.</p> @endif
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group @if ($errors->has('country')) has-error @endif">
                                        <label>Country</label>
                                        <select class="form-control" name="country">

                                            @if(!$user_address)
                                                <option value="" selected="selected">Default</option>
                                            @endif

                                            @foreach( $countries as $country)
                                                <option value="{{ $country->country_name }}" {{ !empty($user_address) && ( $country->country_name == $user_address->country ) ? 'selected' : ''}} >{{ $country->country_name }}</option>
                                            @endforeach

                                        </select>
                                        @if ($errors->has('country')) <p class="help-block">This field is required.</p> @endif
                                    </div>
                                    <div class="form-group">
                                        <label>Postcode</label>
                                        <input type="text" name="postcode" class="form-control" value="{{ !empty($user_address) ? $user_address->post_code : '' }}">
                                        @if ($errors->has('postcode')) <p class="help-block">This field is required.</p> @endif
                                    </div>
                                </div>
                            </div> 
                        </div>
                            
                        <div class="col-md-6 card-info">
                            <div class="title">Credit Card Info</div>
                            <div class="row align-items-end">
                                <div class="col-md-9">
                                    <div class="form-group">
                                        <label>Card number</label>
                                        <div id="order-card-number" class="form-control"></div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <img src="/assets/images/creditcard_visa_2.png" alt="VISA">
                                </div>
                            </div>
                            <div class="form-group @if($errors->has('card_name')) has-error @endif">
                                <label>Cardholder Name</label>
                                <input type="text" name="card_name" id="order-card-name" @if ($stripe_data && $stripe_data->billing_details->name) value="{{ $stripe_data->billing_details->name }}" @endif class="form-control">
                            </div>
                            <div class="form-group">
                                <label>Expire Date</label>
                                <div id="order-card-expiry" class="form-control"></div>
                            </div>
                            <div class="form-group">
                                <label>CVV</label>
                                <div id="order-card-cvc" class="form-control"></div>
                            </div>
                        </div>
                        <div class="btn-holder">
                            <button type="submit" id="order-card-button" @if(isset($subscription->stripe_status) && $subscription->stripe_status == "incomplete") disabled @endif class="btn btn-submit bg-blue text-light">Place Order</button>
                        </div>
                    </div>
                <form>
            </div>
        </div>
    </div>
</div>