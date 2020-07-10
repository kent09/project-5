@extends('layouts.appsuite')
@section('title', 'Premium Plans')
@section('content')

    <h1 class="title color-blue">Premium Plans</h1>
    <nav>
        <div class="nav nav-tabs" id="nav-tab" role="tablist">
            <a class="nav-item nav-link active" id="nav-annual-tab" data-toggle="tab" href="#nav-annual" role="tab" aria-controls="nav-annual" aria-selected="true">Annual</a>
            <a class="nav-item nav-link" id="nav-monthly-tab" data-toggle="tab" href="#nav-monthly" role="tab" aria-controls="nav-monthly" aria-selected="false">Monthly</a>
        </div>
    </nav>
    @if ($user->subscription('main') && $user->subscription('main')->hasIncompletePayment()) 
    <div class="text-center pt-5">
            You have incomplete payment. Please settle it first   
            <a href="{{ route('cashier.payment', $user->subscription('main')->latestPayment()->id) }}">
               here.
            </a>
    </div>
    @endif
    <div class="tab-content" id="nav-tabContent">
        <div class="tab-pane fade show active" id="nav-annual" role="tabpanel" aria-labelledby="nav-annual-tab">
            <div class="row">
                @foreach ($plans as $key => $plan)

                    @if($key % 2 != 1) 
                        @continue 
                    @endif
                   
                <div class="col-md-4">
                    <div class="holder text-center 
                    @if(isset($subscription->stripe_plan) && $subscription->stripe_status == 'active' && $plan->stripe_sub_id == $subscription->stripe_plan) active @endif">
                        <header>{{ $plan->label }}</header>
                        <p class="token">{{ $plan->monthly_token_amount }} tokens</p>
                        <div class="price"><strong>${{ number_format($plan->price) }}</strong></div>
                        <p class="month">(1 Month Free)</p>
                        
                        @if(isset($subscription->stripe_plan) && $subscription->stripe_status == 'active' && $plan->stripe_sub_id == $subscription->stripe_plan)
                            <div class="cplan">CURRENT PLAN</div>
                        @else
                            <button class="btn btn-upgrade bg-blue text-light" data-toggle="modal" data-id="{{ $plan->id }}" data-target="#upgrade-modal">Upgrade</button>
                        @endif

                    </div>
                </div>

                @endforeach

            </div>
        </div>
        <div class="tab-pane fade" id="nav-monthly" role="tabpanel" aria-labelledby="nav-monthly-tab">
            <div class="row">

                @foreach ($plans as $key => $plan)
                    @if($key % 2 == 1) 
                        @continue 
                    @endif
                <div class="col-md-4">
                    <div class="holder text-center
                    @if(isset($subscription->stripe_plan) && $subscription->stripe_status == 'active'  && $plan->stripe_sub_id == $subscription->stripe_plan) active @endif">
                        <header>{{ $plan->label }}</header>
                        <p class="token">{{ $plan->monthly_token_amount }} tokens</p>
                        <div class="price"><strong>${{ number_format($plan->price) }}</strong></div>
                        <p class="month"></p>

                        @if(isset($subscription->stripe_plan) && $subscription->stripe_status == 'active' && $plan->stripe_sub_id == $subscription->stripe_plan)
                            <div class="cplan">CURRENT PLAN</div>
                        @else
                            <button class="btn btn-upgrade bg-blue text-light" data-toggle="modal" data-id="{{ $plan->id }}" data-target="#upgrade-modal">Upgrade</button>
                        @endif

                        

                    </div>
                </div>

                @endforeach

            </div>
        </div>
    </div>

    <div class="tool-token">
        <ul>
            <li>Tool Token Cost</li>
            <li>Monthly Tool Usage</li>
            <li>Monthly Token Usage</li>
        </ul>
        <div class="table-holder">
            <header><img src="/assets/images/logo/fusedtools.png" alt="Fusedtools"></header>
            <table class="table">
                <tbody>
                    <tr>
                        <td>Script Tasks</td>
                        <td>1 per script</td>
                        <td>5 records</td>
                        <td>5 tokens</td>
                    </tr>
                    <tr>
                        <td>CSV Import Limit</td>
                        <td>1 per 100 records</td>
                        <td>250 records</td>
                        <td>2 tokens</td>
                    </tr>
                    <tr>
                        <td>Gep Tools</td>
                        <td>1 per 100 records</td>
                        <td>150 records</td>
                        <td>2 tokens</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="table-holder">
            <header><img src="/assets/images/logo/fuseddocs.png" alt="Fused Docs"></header>
            <table class="table">
                <tbody>
                    <tr>
                        <td>Document Setting</td>
                        <td>5 tokens</td>
                        <td>5 records</td>
                        <td>5 tokens</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="table-holder">
            <header><img src="/assets/images/logo/fusedinvoice.png" alt="Fused Invoice"></header>
            <table class="table">
                <tbody>
                    <tr>
                        <td>Xero Tools</td>
                        <td>1 token per invoice</td>
                        <td>5 invoices</td>
                        <td>5 tokens</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="total-usage">
            <div>
                <span><strong>Total Usage:</strong></span>
                <span><strong>19 tokens</strong></span>
            </div>
        </div>
    </div>

    <div class="billing-form">
        
        <div class="row">
            <div class="col-md-6">
                <div class="title">Billing Details</div>
                <form method="post" action="{{ url('/billing') }}"> 
                    {{ csrf_field() }}
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group @if ($errors->has('first_name')) has-error @endif">
                                <label>First Name*</label>
                                <input type="text" name="first_name" class="form-control" value="{{ !empty($user_address) ? $user_address->first_name : '' }}">
                                @if ($errors->has('first_name')) <p class="help-block">This field is required.</p> @endif
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group @if ($errors->has('last_name')) has-error @endif">
                                <label>Last Name*</label>
                                <input type="text" name="last_name" class="form-control" value="{{ !empty($user_address) ? $user_address->last_name : '' }}">
                                @if ($errors->has('last_name')) <p class="help-block">This field is required.</p> @endif
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Company Name</label>
                        <input type="text" name="company_name" class="form-control" value="{{ !empty($user_address) ? $user_address->company_name : '' }}">
                    </div>
                    <div class="form-group @if ($errors->has('address1')) has-error @endif">
                        <label>Address 1*</label>
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
                                <label>City*</label>
                                <input type="text" name="city" class="form-control" value="{{ !empty($user_address) ? $user_address->city : '' }}">
                                @if ($errors->has('city')) <p class="help-block">This field is required.</p> @endif
                            </div>
                            <div class="form-group @if($errors->has('state')) has-error @endif">
                                <label>State</label>
                                <input type="text" name="state" class="form-control" value="{{ !empty($user_address) ? $user_address->state : '' }}">
                                @if ($errors->has('state')) <p class="help-block">This field is required.</p> @endif
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group @if ($errors->has('country')) has-error @endif">
                                <label>Country*</label>
                                <select class="form-control" name="country">

                                    @if(!$user_address)
                                        <option value="" selected="selected">Default</option>
                                    @endif

                                    @foreach( $countries as $country)
                                    <option value="{{ $country->country_name }}" 
                                    {{( !empty($user_address) && $country->country_name == $user_address->country ) ? 'selected' : ''}} >{{ $country->country_name }}</option>
                                    @endforeach

                                </select>
                                @if ($errors->has('country')) <p class="help-block">This field is required.</p> @endif
                            </div>
                            <div class="form-group @if ($errors->has('postcode')) has-error @endif">
                                <label>Postcode*</label>
                                <input type="text" name="postcode" value="{{ !empty($user_address) ? $user_address->post_code : '' }}" class="form-control">
                                @if ($errors->has('postcode')) <p class="help-block">This field is required.</p> @endif
                            </div>
                        </div>
                    </div>
                    <div class="btn-holder">
                        <button type="submit" class="btn btn-submit bg-blue text-light">Updating Billing Details</button>
                    </div>
                </form>
            </div>
            <div class="col-md-6 card-info">
                <div class="title">Credit Card Info</div>
                <div class="row align-items-center">
                    <div class="col-md-7">
                        <label>Credit Card ending in</label>
                        <div class="card-number">
                            {{ $stripe_data ? 'XXXX XXXX XXXX '.$stripe_data->card->last4 : 'XXXX XXXX XXXX 1234'}}
                        </div>
                        <div class="btn-holder">
                            <button class="btn btn-submit bg-blue text-light" data-toggle="modal" data-target="#update-card-modal">Add / Update Credit Card</button>
                            @if($subscription)
                            <a href="{{ url('cancelsubscription') }}" class="btn mt-5 btn-cancel btn-danger">Cancel Subscription</a>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-5">
                        <img src="/assets/images/creditcard_visa_2.png" alt="VISA">
                    </div>
                </div>
            </div>
        </div>
    </div>


    <div class="invoice-history">
        <div class="title">Invoice History</div>
        <div class="table-responsive-sm">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th scope="col">Date</th>
                        <th scope="col">Description</th>
                        <th scope="col">Amount</th>
                        <th scope="col"></th>
                    </tr>
                </thead>
                <tbody>
                @if($invoices)
                    @foreach ($invoices as $invoice)
                        <tr>
                            <td>{{ $invoice->date()->format('d M Y') }}</td>
                            <td>Fused Tools Basic</td>
                            <td>{{$invoice->total()}}</td>
                            <td>
                                <a href="/invoices/{{ $invoice->id }}" class="btn btn-invoice bg-blue text-light">
                                    View Invoice
                                </a>
                            </td>
                        </tr>
                    @endforeach
                @endif
                <tbody>    
            </table>
        </div>
    </div>  
    @include('v2.manageBilling.modal.update-card')
    @include('v2.manageBilling.modal.order-checkout')
    

@endsection

@section('script')
    <script src="https://js.stripe.com/v3/"></script>
    <script>
        const stripe = Stripe('{{ env('STRIPE_KEY') }}');
        const elements = stripe.elements({
            fonts: [
                {
                    cssSrc: 'https://fonts.googleapis.com/css?family=Poppins&display=swap'
                }
            ]
        });

        const orderElements = stripe.elements({
            fonts: [
                {
                    cssSrc: 'https://fonts.googleapis.com/css?family=Poppins&display=swap'
                }
            ]
        });

        const style = { 
            base: {
                iconColor: '#c4f0ff',
                color: '#555555',
                fontWeight: 400,
                fontFamily: 'Poppins, sans-serif',
                lineHeight: '40px',
                fontSize: '16px',

                '::placeholder': {
                    color: '#555555',
                },
            },
        };

        // Update card 
        <?php $card_placeholder = empty($stripe_data) ? 'XXXX XXXX XXXX 1234' : 'XXXX XXXX XXXX '.$stripe_data->card->last4; ?>
        
        
        const updateCardNumber = elements.create('cardNumber', {
            style: style,
            placeholder: '<?php echo $card_placeholder; ?>',
        });
        updateCardNumber.mount('#update-card-number');

        const updateCardExpiry = elements.create('cardExpiry', {
            style: style,
            placeholder: '01 / 20',
        });
        updateCardExpiry.mount('#update-card-expiry');

        const updateCardCvc = elements.create('cardCvc', {
            style: style,
            placeholder: '',
        });
        updateCardCvc.mount('#update-card-cvc');

        const cardHolderName = document.getElementById('update-card-name');
        const cardButton = document.getElementById('update-card-button');
        const clientSecret = cardButton.dataset.secret;

        cardButton.addEventListener('click', async (e) => {
            e.preventDefault();
            cardButton.disabled = true;
            const { setupIntent, error } = await stripe.confirmCardSetup(
                clientSecret, {
                    payment_method: {
                        card: updateCardNumber,
                        billing_details: { name: cardHolderName.value }
                    }
                }
            );

            if (error) {
                toastr.error(error.message);
                cardButton.disabled = false;
            } else {
                document.getElementById('paymentid').value = setupIntent.payment_method;
                document.getElementById('update-card').submit();
                
            }
        });
        // end update card

        // place order
        const orderCardNumber = orderElements.create('cardNumber', {
            style: style,
            placeholder: 'XXXX XXXX XXXX 1234',
        });
        orderCardNumber.mount('#order-card-number');
        
        const orderCardExpiry = orderElements.create('cardExpiry', {
            style: style,
            placeholder: '01 / 20',
        });
        orderCardExpiry.mount('#order-card-expiry');

        const orderCardCvc = orderElements.create('cardCvc', {
            style: style,
            placeholder: '',
        });
        orderCardCvc.mount('#order-card-cvc');

        const orderCardHolderName = document.getElementById('order-card-name');
        const orderCardButton = document.getElementById('order-card-button');

        orderCardButton.addEventListener('click', async (e) => {
            e.preventDefault();
            orderCardButton.disabled = true;
            const { paymentMethod, error } = await stripe.createPaymentMethod(
                'card', orderCardNumber, {
                    billing_details: { name: orderCardHolderName.value }
                }
            );
            if (error) {
                toastr.error(error.message);
                orderCardButton.disabled = false;
            } else {
                document.getElementById('order-paymentid').value = paymentMethod.id;
                
                @if(empty($subscription) || $subscription->stripe_status != "incomplete")
                    document.getElementById('order-card').submit();
                @endif

                @if(!empty($subscription) && $subscription->stripe_status == "incomplete")
                    swal("Sorry", "You still have incomplete payment.", "warning")
                @endif

            }
        });



        // end place order
        $(document).ready(function() {
  
            $('#upgrade-modal').on('show.bs.modal', function(e) {
                var planid = $(e.relatedTarget).data('id');
                $(e.currentTarget).find('input[name="planid"]').val(planid);
                
            });


            $('.btn-cancel').click(function(e) {
                e.preventDefault();
                let href = $(this).attr("href"); 
          
                swal({
                    title: "Are you sure?",
                    type: "warning",
                    buttons: ["Cancel", "Yes"],
                    dangerMode: true,
                }).then((result) => {
                    if (result) {
                        window.location = href;
                    }
                });

            });
  
            @if(Session::has('message'))
                var type = "{{ Session::get('alert-type', 'info') }}";
                switch(type){
                    case 'info':
                        toastr.info("{{ Session::get('message') }}");
                        break;

                    case 'warning':
                        toastr.warning("{{ Session::get('message') }}");
                        break;

                    case 'success':
                        toastr.success("{{ Session::get('message') }}");
                        break;

                    case 'error':
                        toastr.error("{{ Session::get('message') }}");
                        break;
                }
            @endif
            
        });
    </script>

@endsection