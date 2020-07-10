@extends('layouts.apptools')
@section('title', 'Country Based Owner')
@section('page-css')
	<link rel="stylesheet" href="{{ asset('assets/vendors/multiselect/jquery.multiselect.css') }}">
@endsection

@section('content')

<h1 class="title" style="position: relative;">
    Country Based Owner 
</h1>

<div class="inner-content panel-body" ng-init="loadInfsAccount()">

    <div class="row topboxgrey">
        <div class="col-lg-2">
            <img ng-src="/assets/images/radiusban_03.jpg" class="img-responsive">
        </div>
        <div class="col-lg-10">
            <h4> What does this script do?</h4>
            <p>This tool allows you to specificy a certain Infusionsoft user as the owner for Infusionsoft contacts that are located within specific Country. IE. Sarah Stevens gets all contacts and leads within Australia.</p>
            <p>This means you can assign certain country to certain users (or sales reps), and have all existing and new contacts that are in these countries be assigned to that user.</p>

        </div>
    </div>


    <div class="row martop">
        <div class="col-lg-12">
            <div class="form-inline">
                <select name="infusaccount" class="infusaccount form-control" id="infsBtn">
                  <option value="">Select Your Infusion Account</option>
                  @if( count(\Auth::user()->infsAccounts) > 0 )
                      @foreach( \Auth::user()->infsAccounts as $account )
                        <option value="{{ $account->id }}">{{ $account->account }}</option>
                      @endforeach
                  @endif
                </select> 
                <i class="fa loader"></i>
            </div>          
        </div>
    </div>

	<input type="hidden" id="csrf_token" value="{{ csrf_token() }}">
	<country-based-owner default-infs="{{ $default_infs_account['id'] }}"></country-based-owner>


	<div class="row">
    <div class="col-lg-12">
        <h3>Quick Start Guide</h3>
        <p>To trigger this script and assign an owner to NEW contacts you will need to setup a HTTP post inside your campaign like so:</p>
        <div class="qsgtable">
            <h4>POST URL</h4>
            <input name="URL" type="text" class="posturlin" value="https://app.fusedtools.com/scripts/" /><input name="Submit" type="button" value="Merge" class="posturlmerge" />
            
            <h4 class="spacertwnty">Name/Value Pairs</h4>
            <input name="mode" type="text" class="namein" value="mode" /> = <input name="mode_pair" type="text" class="pairin" value="country_owner" /><br/>
            <input name="FuseKey" type="text" class="namein" value="FuseKey" /> = <input name="fused_user_pair" type="text" class="pairin" value="{{ \Auth::user()->FuseKey }}" /><br/>
            <input name="app" type="text" class="namein" value="app" /> = <input name="app_pair" id="app_name" type="text" class="pairin" value="{{ isset(\Auth::user()->infsAccounts()->where('is_default', 1)->first()->name) ? \Auth::user()->infsAccounts()->where('is_default', 1)->first()->name : '' }}" /><br/>
            <input name="contactid" type="text" class="namein" value="contactID" /> = <input name="contactid_pair" type="text" class="pairin" value="~Contact.ID~" /><br/>
            <input name="stageid" type="text" class="namein" value="Country" /> = <input name="stageid_pair" type="text" class="pairin" value="~Contact.Country~" /><br/>
			<input name="include_closed" type="text" class="namein" value="include_closed" /> = <input name="contactid_pair" type="text" class="pairin" value="0" /><br/>
			<input name="skip_opps" type="text" class="namein" value="skip_opps" /> = <input name="stageid_pair" type="text" class="pairin" value="0" /><br/>
        </div>
        <p>
        <table border="0" cellspacing="0" cellpadding="10" class="infotable">
              <tr>
                <td width="126" align="left" valign="middle" bgcolor="#eeeeee"><strong>Field Name</strong></td>
                <td align="left" valign="middle" bgcolor="#eeeeee"><strong>Description</strong></td>
                <td width="288" align="left" valign="middle" bgcolor="#eeeeee"><strong>Value</strong></td>
              </tr>
              <tr style="border-bottom:solid 2px #eeeeee;">
                <td align="left" valign="top" bgcolor="#f8f8f8">POST URL</td>
                <td align="left" valign="top" bgcolor="#f8f8f8">This is the URL of our web service and is a fixed value. <strong>(REQUIRED)</strong></td>
                <td align="left" valign="top" bgcolor="#f8f8f8"><strong id="docs-internal-guid-35de7689-e1e4-af0e-4c38-f86f464e7383">https://app.fusedtools.com/scripts/</strong></td>
              </tr>
              <tr style="border-bottom:solid 2px #eeeeee;">
                <td align="left" valign="top" bgcolor="#f8f8f8">mode</td>
                <td align="left" valign="top" bgcolor="#f8f8f8">This tells us which script you are trying to use. In this case it is country_owner. <strong>(REQUIRED)</strong>
            </td>
                <td align="left" valign="top" bgcolor="#f8f8f8"><strong id="docs-internal-guid-35de7689-e1e4-c2be-51b1-8b9e3045a200">country_owner</strong></td>
              </tr>
             <tr style="border-bottom:solid 2px #eeeeee;">
               <td align="left" valign="top" bgcolor="#f8f8f8">FuseKey</td>
                <td align="left" valign="top" bgcolor="#f8f8f8">This is a fixed value and tells us what fusedtools account this post belongs to.<br />
                (REQUIRED) - Your unique user ID is shown in the value column.</td>
                <td align="left" valign="top" bgcolor="#f8f8f8"><strong></strong></td>
              </tr>
            <tr style="border-bottom:solid 2px #eeeeee;">
                <td align="left" valign="top" bgcolor="#f8f8f8">app</td>
                <td align="left" valign="top" bgcolor="#f8f8f8">This tells us which of your Infusionsoft accounts that you want this script to work on. <strong>(REQUIRED)</strong>
            </td>
                <td align="left" valign="top" bgcolor="#f8f8f8"><strong id="docs-internal-guid-35de7689-e1e4-d517-443f-fa6cc121202d">IE. a123</strong></td>
              </tr>
              <tr style="border-bottom:solid 2px #eeeeee;">
                <td align="left" valign="top" bgcolor="#f8f8f8">contactId</td>
                <td align="left" valign="top" bgcolor="#f8f8f8">This is the id of the contact you want to work with. Leave this as the merge field given. <strong>(REQUIRED)</strong>
            </td>
                <td align="left" valign="top" bgcolor="#f8f8f8"><strong id="docs-internal-guid-35de7689-e1e4-d517-443f-fa6cc121202d">~Contact.Id~</strong></td>
              </tr>
              <tr style="border-bottom:solid 2px #eeeeee;">
                <td align="left" valign="top" bgcolor="#f8f8f8">Country</td>
                <td align="left" valign="top" bgcolor="#f8f8f8">This is the contacts country.<strong>(REQUIRED)</strong>
            </td>
                <td align="left" valign="top" bgcolor="#f8f8f8"><strong id="docs-internal-guid-35de7689-e1e4-e9be-418f-9c9e4f6b0b7e">~Contact.Country~</strong></td>
              </tr>

              <tr style="border-bottom:solid 2px #eeeeee;">
                <td align="left" valign="top" bgcolor="#f8f8f8">include_closed</td>
                <td align="left" valign="top" bgcolor="#f8f8f8">An optional field, that tells us whether to update older deals that are already closed to have this new owner. Default is 0. If 1, the deals will be updated<strong>(OPTIONAL)</strong>
            </td>
                <td align="left" valign="top" bgcolor="#f8f8f8"><strong id="docs-internal-guid-35de7689-e1e4-e9be-418f-9c9e4f6b0b7e">0 or 1</strong></td>
              </tr>

              <tr style="border-bottom:solid 2px #eeeeee;">
                <td align="left" valign="top" bgcolor="#f8f8f8">skip_opps</td>
                <td align="left" valign="top" bgcolor="#f8f8f8">An optional field, that tells us whether to update the owner of opporunities as well as contacts. Default = 0, and opps are updated. If 1, no opps are updated.<strong>(OPTIONAL)</strong>
            </td>
                <td align="left" valign="top" bgcolor="#f8f8f8"><strong id="docs-internal-guid-35de7689-e1e4-e9be-418f-9c9e4f6b0b7e">0 or 1</strong></td>
              </tr>
            </table>
         </p>
    
    </div>
</div>

<hr>
</span>

</div>

@endsection


@section('script')
	
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>

	<script src="{{ URL::to('assets/js/select2.min.js') }}"></script>
	
	<script src="https://cdn.jsdelivr.net/npm/sweetalert2@8"></script>

	<script>
		$( window ).load(function() {
	        if ( $('.infusaccount option').length == 2 ) {
	            $('.infusaccount option:last-child').attr('selected', 'selected');
	        }  
	    });
	</script>

	

	<script src="{{ asset('assets/vendors/framework/angular/angular.min.js') }}"></script>
	<script src="{{ asset('assets/vendors/framework/angular/angular-sanitize.min.js') }}"></script>
	<script src="{{ asset('assets/vendors/framework/angular/angular-animate.min.js') }}"></script>
	<script src="{{ asset('assets/vendors/framework/angular/angular-touch.min.js') }}"></script>
	<script src="{{ asset('assets/vendors/framework/angular/ui-bootstrap-custom-2.5.0.js') }}"></script>
	

	<script src="/app/env/{{ env('NG_APP') }}.js"></script>
	<script src="{{ asset('app/components/country_based_owner/CountryBasedOwner.js?version=1.0') }}"></script>
	<script src="{{ asset('app/app.js?version=1.0') }}"></script>


	<script>

	$(document).ready(function(){
		/* Get all owner */
		// $(document).on('change','#infsBtn',function(e){
		// 	e.preventDefault();
		// 	var thisObj = $(this);
		// 	var accountID = thisObj.val();

		// 	if( accountID == '' ){
		// 		$(".ownersection, .allowner").hide();
		// 		toastr.options = {
		// 			positionClass: 'toast-top-center'
		// 		};
		// 		toastr.warning("", 'Please select your account from the dropdown.');
		// 		return false;
		// 	}

		// 	thisObj.prop('disabled',true);
		// 	$('.loader').addClass('fa-spinner fa-spin');

		// 	if( accountID) {
		// 		$.ajax({
		// 			'type': 'post',
		// 			'url' : '{{ url("/api/v1/country-owner-groups/get/by-user-infusionsoft-account-id") }}',
		// 			'data': { 'accountID':accountID,'_token':"{{ csrf_token() }}" },
		// 			'dataType':'html',
		// 			success: function(response){
		// 				var data = $.parseJSON(response);
		// 				if( data.status == 'failed' ) {
		// 					$('.ownerTable .tags-row').remove();
		// 					toastr.options = {
		// 						positionClass: 'toast-top-center'
		// 					};
		// 					toastr.warning("", data.message);
		// 				}
		// 				else {
		// 					$(".allowner").show();
		// 					$('.ownerTable').html('');
		// 					$('.ownerTable').html(data.response);
		// 					$('#app_name').val(data.app_name);
		// 				}
		// 				$('.loader').removeClass('fa-spinner fa-spin');
		// 				thisObj.prop('disabled',false);
		// 			}
		// 		});
		// 	} else {
		// 		$('.loader').removeClass('fa-spinner fa-spin');
		// 		thisObj.prop('disabled',true);
		// 		return false;
		// 	}
		// });
		

        
	});
</script>

@endsection
