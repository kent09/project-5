@extends('layouts.apptools')
@section('title', 'Update Credit Card & Recharge Infusionsoft Subscriptions')
@section('content')
    <h1 class="title">Update Credit Card & Recharge Infusionsoft Subscriptions
</h1>
		
	<div class="inner-content panel-body">
	    <div class="row topboxgrey">
	        <div class="col-lg-2">
	            <img src="{{ asset('assets/images/1.png') }}" class="img-responsive">
	        </div>
	        <div class="col-lg-10">
	           <h4> What does this script do?</h4>
<p>This simple script allows you to automatically update all subscriptions with the latest credit card added by a customer and recharge any outstanding invoices on that subscription.</p>
<p>This solves the problem where a customer adds or changes their card, but it doesn't add that same credit card to their active subscriptions, or recharge outstanding orders.</p>

	        </div>
	    </div>
    	<div class="row">
    		<div class="col-lg-12">
    			<h3>Quick Start Guide</h3>
    			<ol style="margin-left:20px;">
    <li>If you already have a page or method for users to be able to update their cards, skip to step X, otherwise proceed to step 2.</li>
    <li>Setup a new product that is $0 that is called "Update Your Credit Card".
<p><img src="{{ asset('assets/images/manageproduct.png') }}" class="img-responsive"></p></li>
<li>Create an order form for this product called "Update Your Credit Card".
<p><img src="{{ asset('assets/images/updateyourcreditcard1.png') }}" class="img-responsive"></p>
<ol type="a">
    <li>
        Optional: Add the below javascript snippet to the form design if you want to hide the qty and price fields since it is a $0 product.
        <p><img src="{{ asset('assets/images/updateyourcreditcard2.png') }}" class="img-responsive"></p>
        <br/>
        <p>Code Snippet:</p>
<pre>&lt;script type=&quot;text/javascript&quot;&gt;
  jQuery(document).ready(function(){
&nbsp;jQuery(&quot;table.viewCart,table.orderSummary&quot;).css(&quot;display&quot;,&quot;none&quot;);
});<br/>&lt;/script&gt;</pre>
    </li>
</ol>
</li>
<li>Add this URL to your failed billing emails, or a link on your website for failed billings.
<p><img src="{{ asset('assets/images/updateyourcreditcard.png') }}" class="img-responsive"></p>
</li>
<li>Create a campaign (or use an existing on) for managing the update process.</li>
<li>Add a purchase goal for the “Update Your Credit Card” product - this means that whenever anyone “buys” this free product the Fused Tools Update Credit Card script will be triggered.
<p><img src="{{ asset('assets/images/fusedtoolsdemotesting7.png') }}" class="img-responsive"></p>
</li>
<li><p>Add a http post element to your sequence configured like so:</p>
<div class="qsgtable">
                <h4>POST URL</h4>
                <input name="URL" type="text" class="posturlin" value="https://app.fusedtools.com/scripts/" /><input name="Submit" type="button" value="Merge" class="posturlmerge" />
                
                <h4 class="spacertwnty">Name/Value Pairs</h4>
                <input name="mode" type="text" class="namein" value="mode" /> = <input name="mode_pair" type="text" class="pairin" value="update_card" /><br/>
               <input name="FuseKey" type="text" class="namein" value="FuseKey" /> = <input name="fused_user_pair" type="text" class="pairin" value="{{ \Auth::user()->FuseKey }}" /><br/>
                <input name="app" type="text" class="namein" value="app" /> = <input name="app_pair" id="app_name" type="text" class="pairin" value="a123" /><br/>
                <input name="contactid" type="text" class="namein" value="contactID" /> = <input name="contactid_pair" type="text" class="pairin" value="~Contact.ID~" /><br/>
                <input name="stageid" type="text" class="namein" value="update_subscriptions" /> = <input name="stageid_pair" type="text" class="pairin" value="1" /><br/>
                <input name="stageid" type="text" class="namein" value="rebill_subscriptions" /> = <input name="stageid_pair" type="text" class="pairin" value="0" /><br/>
                <input name="stageid" type="text" class="namein" value="only_active" /> = <input name="stageid_pair" type="text" class="pairin" value="1" /><br/>
                <input name="stageid" type="text" class="namein" value="rebill_orders" /> = <input name="stageid_pair" type="text" class="pairin" value="1" /><br/>
                <input name="stageid" type="text" class="namein" value="mechant_id" /> = <input name="stageid_pair" type="text" class="pairin" value="12" /><br/>
          </div>


<table border="0" cellspacing="0" cellpadding="10" class="infotable">
  <tr>
    <td width="126" align="left" valign="middle" bgcolor="#eeeeee"><strong>Field Name</strong></td>
    <td align="left" valign="middle" bgcolor="#eeeeee"><strong>Description</strong></td>
    <td width="288" align="left" valign="middle" bgcolor="#eeeeee"><strong>Value</strong></td>
  </tr>
  <tr style="border-bottom:solid 2px #eeeeee;">
    <td align="left" valign="top" bgcolor="#f8f8f8">POST URL</td>
    <td align="left" valign="top" bgcolor="#f8f8f8">This is the URL of our web service and is a fixed value. <strong>(REQUIRED)</strong></td>
    <td align="left" valign="top" bgcolor="#f8f8f8"><strong>https://app.fusedtools.com/scripts/</strong></td>
  </tr>
  <tr style="border-bottom:solid 2px #eeeeee;">
    <td align="left" valign="top" bgcolor="#f8f8f8">mode</td>
    <td align="left" valign="top" bgcolor="#f8f8f8">This tells us which script you are trying to use. In this case it is update_card. <strong>(REQUIRED)</strong>
</td>
    <td align="left" valign="top" bgcolor="#f8f8f8"><strong>update_card</strong></td>
  </tr>
  <tr style="border-bottom:solid 2px #eeeeee;">
    <td align="left" valign="top" bgcolor="#f8f8f8">FuseKey</td>
    <td align="left" valign="top" bgcolor="#f8f8f8">This is a fixed value and tells us what fusedtools account this post belongs to.<br />
    (REQUIRED) - Your unique user ID is shown in the value column.</td>
    <td align="left" valign="top" bgcolor="#f8f8f8"><strong>{{ \Auth::user()->FuseKey }}</strong></td>
  </tr>
    <tr style="border-bottom:solid 2px #eeeeee;">
    <td align="left" valign="top" bgcolor="#f8f8f8">app</td>
    <td align="left" valign="top" bgcolor="#f8f8f8">This tells us which of your Infusionsoft accounts that you want this script to work on. <strong>(REQUIRED)</strong>
</td>
    <td align="left" valign="top" bgcolor="#f8f8f8"><strong id="docs-internal-guid-35de7689-e1e4-d517-443f-fa6cc121202d">IE. a123</strong></td>
  </tr>
  <tr style="border-bottom:solid 2px #eeeeee;">
    <td align="left" valign="top" bgcolor="#f8f8f8">contactId</td>
    <td align="left" valign="top" bgcolor="#f8f8f8">
This is the id of the contact that we will be updating. Leave this as the merge field given. <strong>(REQUIRED)</strong>
</td>
    <td align="left" valign="top" bgcolor="#f8f8f8"><strong>~Contact.Id~</strong></td>
  </tr>

  <tr style="border-bottom:solid 2px #eeeeee;">
    <td align="left" valign="top" bgcolor="#f8f8f8">update_susbcriptions</td>
    <td align="left" valign="top" bgcolor="#f8f8f8">Do you want us to update the given users subscriptions with the new card? <strong>(REQUIRED) </strong></td>
    <td align="left" valign="top" bgcolor="#f8f8f8"><strong>0 = no<br/>1 = yes</strong></td>
  </tr>
  <tr style="border-bottom:solid 2px #eeeeee;">
    <td align="left" valign="top" bgcolor="#f8f8f8">rebill_susbcriptions</td>
    <td align="left" valign="top" bgcolor="#f8f8f8">Do you want us to rebill any outstanding unpaid invoices on the subscription after updating the card? 
</td>
    <td align="left" valign="top" bgcolor="#f8f8f8"><strong>0 = no<br/>1 = yes</strong></td>
  </tr>
  <tr style="border-bottom:solid 2px #eeeeee;">
    <td align="left" valign="top" bgcolor="#f8f8f8">only_active</td>
    <td align="left" valign="top" bgcolor="#f8f8f8">Do you only want us to rebill invoices on active subscriptions? If you select 0, we will rebill unpaid invoices on Inactive subscriptions as well.
</td>
    <td align="left" valign="top" bgcolor="#f8f8f8"><strong>0 = no<br/>1 = yes</strong></td>
  </tr>
  <tr style="border-bottom:solid 2px #eeeeee;">
    <td align="left" valign="top" bgcolor="#f8f8f8">rebill_orders</td>
    <td align="left" valign="top" bgcolor="#f8f8f8">Should we rebill any unpaid orders with the new card?
</td>
    <td align="left" valign="top" bgcolor="#f8f8f8"><strong>0 = no<br/>1 = yes</strong></td>
  </tr>
  <tr style="border-bottom:solid 2px #eeeeee;">
    <td align="left" valign="top" bgcolor="#f8f8f8">merchant_id</td>
    <td align="left" valign="top" bgcolor="#f8f8f8">Which of your merchant facilities/payment methods should we use to rebill the client (see tool below to get this value).
</td>
    <td align="left" valign="top" bgcolor="#f8f8f8"></td>
  </tr>
</table>
</li>
</ol>
        <p>Get Your Merchant ID</p>
    			<div class="form-inline">
                    <select name="infusaccount" class="infusaccount form-control" id="infsBtn">
                      <option value="">Select Your Infusion Account</option>
                      @if( count(\Auth::user()->infsAccounts) > 0 )
                          @foreach( \Auth::user()->infsAccounts as $account )
                            <option value="{{ $account->id }}">{{ $account->name }}</option>
                          @endforeach
                      @endif
                    </select> 
                    <!-- <a class="btn btn-primary" href="{{ url('/manageaccounts/add') }}"> Add Account</a> -->
                    <i class="fa loader"></i>
                </div>
                <div>
                    <table border="0" cellpadding="10" cellspacing="0" class="infotable spacertwnty" style="width:374px!important;" id="allStages">
                      <tr>
                        <td></td>
                      </tr>
                    </table>
                </div>

    			</div>
    			</div>
    			
    			<hr>
    			<div class="row">
    			    <div class="col-lg-12">
    			<h4>Detailed Setup Guide</h4>
    			<p align="center"><img src="{{ asset('assets/images/video_03.jpg') }}" class="img-responsive"></p>
    			
    			<br/>
    			
    			<p>Example campaign setup:</p>
    			<p><img src="{{ asset('assets/images/fusedtoolsdemotesting7.png') }}" class="img-responsive"></p>
    			
    			<br/>
    			<p>Example sequence setup:</p>
    			<p><img src="{{ asset('assets/images/fusedtoolsdemotesting8.png') }}" class="img-responsive"></p>
    			
    			
    			
    			
    			<br/>
    			<p>HTTP Post Configuration - See the top of the page</p>
    			
    		</div>
    	</div>
    </div>

@endsection


@section('script')
<script>
    $( window ).load(function() {
        if ( $('.infusaccount option').length == 2 ) {
            $('.infusaccount option:last-child').attr('selected', 'selected');
            $( ".infusaccount" ).trigger( "change" );
        }  
    });
    $(document).ready(function(){

        /* Get all stages */
        $(document).on('change','#infsBtn',function(e){
            e.preventDefault();
            var thisObj = $(this);
            var accountID = thisObj.val();
            
            if( accountID == '' ){
                toastr.options = {
                    positionClass: 'toast-top-center'
                };
                toastr.warning("", 'Please select your account from the dropdown.');
                return false;
            }
            thisObj.prop('disabled',true);
            $('.loader').addClass('fa-spinner fa-spin');
            if( accountID) {
                $.ajax({
                    'type': 'post',
                    'url' : '{{ url("/get-merchants") }}',
                    'data': { 'accountID':accountID,'_token':"{{ csrf_token() }}" },
                    'dataType':'html',
                    success: function(response){
                        var data = $.parseJSON(response);
                        if( data.status == 'failed' ) {
                            $('#allStages .stage-row').remove();
                            toastr.options = {
                                positionClass: 'toast-top-center'
                            };
                            toastr.warning("", data.message);
                        }
                        else {
                            $('#allStages').html('');
                            $('#allStages').html(data.message);
                            $('#app_name').val(data.app_name);
                        }
                        $('.loader').removeClass('fa-spinner fa-spin');
                        thisObj.prop('disabled',false);
                    }
                });
            } else {
                $('.loader').removeClass('fa-spinner fa-spin');
                thisObj.prop('disabled',true);
                return false;
            }
        });
       
    });
</script>
@endsection