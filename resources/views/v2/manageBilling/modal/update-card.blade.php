<div class="modal fade" id="update-card-modal" tabindex="-1" role="dialog" aria-labelledby="Order Checkout" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-body">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">X</span>
                </button>
                <form method="POST" id="update-card" action="{{ url('/updatecard') }}"> 
                    {{ csrf_field() }}
                    <input id="paymentid" name="paymentid" type="hidden">
                    <div class="title font-34">Credit Card Info</div>
                    <div class="error-card"></div>
                    <div class="card-info"> 
                        <div class="title">Credit Card Info</div>
                        <div class="row align-items-end">
                            <div class="col-md-9">
                                <div class="form-group @if($errors->has('card_number')) has-error @endif">
                                    <label>Card number</label>
                                    <div id="update-card-number" class="form-control"></div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <img src="/assets/images/creditcard_visa_2.png" alt="VISA">
                            </div>
                        </div>
                        <div class="form-group @if ($errors->has('card_name')) has-error @endif">
                            <label>Cardholder Name</label>
                            <input type="text" id="update-card-name" name="card_name" @if ($stripe_data && $stripe_data->billing_details->name) value="{{ $stripe_data->billing_details->name }}" @endif class="form-control holder-name">
                            @if ($errors->has('card_name')) <p class="help-block">This field is required.</p> @endif
                        </div>
                        <div class="form-group @if($errors->has('expire_date')) has-error @endif">
                            <label>Expire Date</label>
                            <div id="update-card-expiry" class="form-control"></div>
                     
                        </div>
                        <div class="form-group @if($errors->has('cvv')) has-error @endif">
                            <label>CVV</label>
                            <div id="update-card-cvc" class="form-control"></div>
                        </div>
                    </div>
                    <div class="btn-holder">
                        <button type="submit" id="update-card-button" class="btn btn-submit bg-blue text-light" data-secret="{{ $intent->client_secret }}">Add/Update Credit Card</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>