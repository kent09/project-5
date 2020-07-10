@extends('layouts.apptools')
@section('title', 'Postcode & Radius based Contact Owner')
@section('content')
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
<script src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAP_JS_API') }}"></script>
<script src="{{ URL::to('assets/js/infobox.js') }}"></script> 
<h1 class="title">Postcode & Radius based Contact Owner</h1>

<div class="inner-content panel-body">

	<div class="row topboxgrey">
		<div class="col-lg-2">
			<img src="{{ asset('assets/images/radiusban_03.jpg') }}" class="img-responsive">
		</div>
		<div class="col-lg-10">
			<h4> What does this script do?</h4>
			<p>This tool allows you to specificy a certain Infusionsoft user as the owner for Infusionsoft contacts that are located within a certain radius of a postcode. IE. Sarah Stevens gets all contacts and leads within 20 miles of LA.</p>
            <p>This means you can assign certain cities to certain users (or sales reps), and have all existing and new contacts that are in these areas be assigned to that user.</p>

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
				<!-- <a href="{{ url('/manageaccounts/add') }}" class="btn btn-primary aninfuaccount"> Add New</a> -->
				<i class="fa loader"></i>
			</div>    		
		</div>
	</div>
	<div class="row allowner" style="display:none;">
		<div class="col-lg-7">
			<table border="0" cellspacing="0" cellpadding="10" class="ownerTable infotable spacertwnty" ></table>   		
		</div>
	</div>
	<div class="ownersection"></div>
	<div class="radiusmap" style="display:none;">
                
                <div class="col-md-12">
                    <h3>Your Radius</h3>
                    <div id="map" style="width: 560px; height: 350px;"></div>    
                </div>
                <div class="radiuslist col-md-12">
                    <h3>Your Postcode List</h3>
                    <div></div>
                </div>
            </div>
    <div class="row">
    	<div class="col-lg-12">
    		<h3>Quick Start Guide</h3>
    		<p>To trigger this script and assign an owner to NEW contacts you will need to setup a HTTP post inside your campaign like so:</p>
    		<div class="qsgtable">
                <h4>POST URL</h4>
                <input name="URL" type="text" class="posturlin" value="https://app.fusedtools.com/scripts/" /><input name="Submit" type="button" value="Merge" class="posturlmerge" />
                
                <h4 class="spacertwnty">Name/Value Pairs</h4>
                <input name="mode" type="text" class="namein" value="mode" /> = <input name="mode_pair" type="text" class="pairin" value="postcode_owner" /><br/>
                <input name="FuseKey" type="text" class="namein" value="FuseKey" /> = <input name="fused_user_pair" type="text" class="pairin" value="{{ \Auth::user()->FuseKey }}" /><br/>
                <input name="app" type="text" class="namein" value="app" /> = <input name="app_pair" id="app_name" type="text" class="pairin" value="a123" /><br/>
                <input name="contactid" type="text" class="namein" value="contactID" /> = <input name="contactid_pair" type="text" class="pairin" value="~Contact.ID~" /><br/>
                <input name="stageid" type="text" class="namein" value="Country" /> = <input name="stageid_pair" type="text" class="pairin" value="~Contact.Country~" /><br/>
                <input name="stageid" type="text" class="namein" value="PostalCode" /> = <input name="stageid_pair" type="text" class="pairin" value="~Contact.PostalCode~" /><br/>
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
                    <td align="left" valign="top" bgcolor="#f8f8f8">This tells us which script you are trying to use. In this case it is postcode_owner. <strong>(REQUIRED)</strong>
                </td>
                    <td align="left" valign="top" bgcolor="#f8f8f8"><strong id="docs-internal-guid-35de7689-e1e4-c2be-51b1-8b9e3045a200">postcode_owner</strong></td>
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
                    <td align="left" valign="top" bgcolor="#f8f8f8">PostalCode</td>
                    <td align="left" valign="top" bgcolor="#f8f8f8">This is the contacts Postcode.<strong>(REQUIRED)</strong></td>
                    <td align="left" valign="top" bgcolor="#f8f8f8"><strong id="docs-internal-guid-35de7689-e1e4-e9be-418f-9c9e4f6b0b7e2">~Contact.PostalCode~</strong></td>
                  </tr>
                </table>
    		 </p>
    	
    	</div>
    </div>
    <div class="row topboxgrey">
        
        <div class="col-lg-12">
           <h4>Important Notes:</h4>
            <ol style="margin-left:20px;">
                <li><strong>If you don't have country set, but all your contacts are from the same country ~</strong> - Add in a "set field value" element before the http post and set the Country to your country.</li>
            </ol>
    
        </div>
    </div>
    <hr>
    <div class="row">
    	<div class="col-lg-12">
    		<h4>Detailed Setup Guide</h4>
    		<p align="center"><img src="{{ asset('assets/images/video_03.jpg') }}" class="img-responsive"></p>
    		
    		<br/>
    		
    		<p>Example campaign setup:</p>
    		<p><img src="{{ asset('assets/images/fusedtoolsdemotesting11.png') }}" class="img-responsive"></p>
    		
    		<br/>
    		<p>Example sequence setup:</p>
    		<p><img src="{{ asset('assets/images/fusedtoolsdemotesting8.png') }}" class="img-responsive"></p>
    		
    		
    		
    		<br/>
    		<p>Example HTTP Post - see specific setup at the top of this page</p>
    		
    	</div>
    </div>
</div>
<script>
    $( window ).load(function() {
        if ( $('.infusaccount option').length == 2 ) {
            $('.infusaccount option:last-child').attr('selected', 'selected');
            $( ".infusaccount" ).trigger( "change" );
        }  
    });
	$(document).ready(function(){

		/* Get all owner */
		$(document).on('change','#infsBtn',function(e){
			e.preventDefault();
			var thisObj = $(this);
			var accountID = thisObj.val();

			if( accountID == '' ){
				$(".ownersection, .allowner").hide();
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
					'url' : '{{ url("/post-owner") }}',
					'data': { 'accountID':accountID,'_token':"{{ csrf_token() }}" },
					'dataType':'html',
					success: function(response){
						var data = $.parseJSON(response);
						if( data.status == 'failed' ) {
							$('.ownerTable .tags-row').remove();
							toastr.options = {
								positionClass: 'toast-top-center'
							};
							toastr.warning("", data.message);
						}
						else {
							$(".allowner").show();
							$('.ownerTable').html('');
							$('.ownerTable').html(data.response);
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
		
		
		$(document).on('click','.reassignpostcode',function(e){
			e.preventDefault();
			var thisObj = $(this);
			var infs_account_id = thisObj.data("reassign_acc_id");

		    $.ajax({
				'type': 'post',
				'url' : '{{ url("/reAssignContactOwner") }}',
				'data': { 'infs_account_id':infs_account_id,'_token':"{{ csrf_token() }}" },
				'dataType':'html',
				success: function(response){
					var data = $.parseJSON(response);
					if( data.status == 'failed' ) {
						toastr.options = {
							positionClass: 'toast-top-center'
						};
						toastr.warning("", data.message);
					}
					else {
						toastr.options = {
							positionClass: 'toast-top-center'
						};
						toastr.warning("", data.message);
					}
					thisObj.find('i').removeClass('fa-spinner fa-spin');
					thisObj.prop('disabled',false);
					onloadhtml();
				}
			});
		});
		
		$(document).on('click','.addnewpostinfo',function(e){
			e.preventDefault();
			var thisObj = $(this);
			var accountID = $(".infusaccount").val();
			$(".radiusmap").hide();

			if( accountID == '' ){
				toastr.options = {
					positionClass: 'toast-top-center'
				};
				toastr.warning("", 'Please select your account from the dropdown.');
				return false;
			}

			thisObj.prop('disabled',true);
			thisObj.find('i').addClass('fa-spinner fa-spin');

			if( accountID) {
				$.ajax({
					'type': 'post',
					'url' : '{{ url("/add-owner") }}',
					'data': { 'accountID':accountID,'_token':"{{ csrf_token() }}" },
					'dataType':'html',
					success: function(response){
						var data = $.parseJSON(response);
						if( data.status == 'failed' ) {
							$('.ownerTable .tags-row').remove();
							toastr.options = {
								positionClass: 'toast-top-center'
							};
							toastr.warning("", data.message);
						}
						else {
							$(".ownersection").show();
							$('.ownersection').html('');
							$('.ownersection').html(data.response);
						}
						thisObj.find('i').removeClass('fa-spinner fa-spin');
						thisObj.prop('disabled',false);
						onloadhtml();
					}
				});
			} else {
				thisObj.find('i').removeClass('fa-spinner fa-spin');
				thisObj.prop('disabled',true);
				return false;
			}
		});
        
        $(document).on('click','#radiusMap',function(e){
            e.preventDefault();
            var thisObj = $(this);
            var postcode = $('input[name=postcode]').val();
            var country =  $('.country').val();
            var radius = $('input[name=kmvalue]').val();
            var unit = $('.unitvaoue').val();
            
            thisObj.find('i').addClass('fa-spinner fa-spin');
            thisObj.prop('disabled',true);
            $.ajax({
                'type': 'post',
                'url' : '{{ url("/radiusMap") }}',
                'data': { 'postcode':postcode,'country':country,'radius':radius,'unit':unit,'_token':"{{ csrf_token() }}" },
                'dataType':'html',
                success: function(response){
                    var data = $.parseJSON(response);
                    if( data.status == 'failed' ) {
                        toastr.options = {
                            positionClass: 'toast-top-center'
                        };
                        toastr.warning("", data.message);
                    }
                    else {
                        $(".radiusmap").show();
                        $(".radiuslist div").html(data.response.list);
                        
                        if( unit == 'MI'){
                            radius = radius*1.609344;
                        }
                        init(data.response.lat,data.response.long,radius);
                    }
                    thisObj.find('i').removeClass('fa-spinner fa-spin');
                    thisObj.prop('disabled',false);
                }
            });
            
        });
        
        $(document).on('click','.edit-owner',function(e){
			e.preventDefault();
			var thisObj = $(this);
			var accountID = $(".infusaccount").val();
            var id = thisObj.data('id');
            $(".radiusmap").hide();
            
			if( accountID == '' ){
				toastr.options = {
					positionClass: 'toast-top-center'
				};
				toastr.warning("", 'Please select your account from the dropdown.');
				return false;
			}

			if( accountID) {
				$.ajax({
					'type': 'post',
					'url' : '{{ url("/edit-owner") }}',
					'data': { 'accountID':accountID,'id':id,'_token':"{{ csrf_token() }}" },
					'dataType':'html',
					success: function(response){
						var data = $.parseJSON(response);
						if( data.status == 'failed' ) {
							$('.ownerTable .tags-row').remove();
							toastr.options = {
								positionClass: 'toast-top-center'
							};
							toastr.warning("", data.message);
						}
						else {
							$(".ownersection").show();
							$('.ownersection').html('');
							$('.ownersection').html(data.response);
						}
						onloadhtml();
					}
				});
			} else {
				return false;
			}
		});
		
		$(document).on('click','.edit-owner-group',function(e){
			e.preventDefault();
			var thisObj = $(this);
			var accountID = $(".infusaccount").val();
			var postc_owner_id = $("input[name='onwer_id']").val();
            var id = thisObj.data('id');
            
			if( accountID == '' ){
				toastr.options = {
					positionClass: 'toast-top-center'
				};
				toastr.warning("", 'Please select your account from the dropdown.');
				return false;
			}

			if( accountID) {
				$.ajax({
					'type': 'post',
					'url' : '{{ url("/edit-owner-group") }}',
					'data': { 'postc_owner_id':postc_owner_id,'id':id,'_token':"{{ csrf_token() }}" },
					'dataType':'html',
					success: function(response){
						var data = $.parseJSON(response);
						if( data.status == 'failed' ) {
							$('.ownerTable .tags-row').remove();
							toastr.options = {
								positionClass: 'toast-top-center'
							};
							toastr.warning("", data.message);
						}
						else {
						    $('.groupdetails').show();
						    $('.country').val(data.response.postc_country_code).change();
						    $("input[name='onwer_group_id']").val(data.response.id);
						    if( data.response.match_type == 1 ){
						        $('input[value="radius_around_postcode"]').prop('checked', true);
						        $('.postcode').val(data.response.postc_code);
						        $('.kmvalue').val(data.response.postc_radius);
						        $('.unitvaoue').val(data.response.postc_units).change();
						    }
						    else {
						        $('input[value="postcode_list"]').prop('checked', true);
						        $(".postcodelist").show();
						        $(".radius-around").hide();
						        
						        $('.areagrouptext').val(data.response.postc_list).change();
						    }
						}
						onloadhtml();
					}
				});
			} else {
				return false;
			}
		});
		
        $(document).on('click','input[name="areagroup"]',function(e){
            var thisObj = $(this);
            if( thisObj.val() == 'radius_around_postcode' ){
                $(".radius-around").show();
                $(".postcodelist").hide();
            }
            else {
                $(".radius-around").hide();
                $(".postcodelist").show();
            }
        });
        
        
        
        $(document).on('click','#save-group',function(e){
			e.preventDefault();
			var thisObj = $(this);
			var accountID = $(".infusaccount").val();
			var contact = $(".infuscontact").val();
			var infsName = $(".infuscontact option:selected").text();
			var country = $(".country").val();
			var postcode = $(".postcode").val();
			var kmvalue = $(".kmvalue").val();
			var unitvaoue = $(".unitvaoue").val();
			var areagrouptext = $(".areagrouptext").val();
			var areagroup = $("input[name='areagroup']:checked").val();

			if( accountID == '' ){
				toastr.options = {
					positionClass: 'toast-top-center'
				};
				toastr.warning("", 'Please select your account from the dropdown.');
				return false;
			}
			if( contact == '' ){
				toastr.options = {
					positionClass: 'toast-top-center'
				};
				toastr.warning("", 'Please select your contact from the dropdown.');
				return false;
			}
			if( country == '' ){
				toastr.options = {
					positionClass: 'toast-top-center'
				};
				toastr.warning("", 'Please select country from the dropdown.');
				return false;
			}
			if( areagroup == 'radius_around_postcode' ){
			    if( $.trim(postcode) == '' ){
    				toastr.options = {
    					positionClass: 'toast-top-center'
    				};
    				toastr.warning("", 'Please enter postcode.');
    				return false;
    			}
    			if( $.trim(kmvalue) == '' ){
    				toastr.options = {
    					positionClass: 'toast-top-center'
    				};
    				toastr.warning("", 'Please enter radius.');
    				return false;
    			}
			}
			else{
			    if( $.trim(areagrouptext) == '' ){
    				toastr.options = {
    					positionClass: 'toast-top-center'
    				};
    				toastr.warning("", 'Please enter postcode list.');
    				return false;
    			}
			}

			thisObj.prop('disabled',true);
			thisObj.find('i').addClass('fa-spinner fa-spin');
			$.ajax({
				'type': 'post',
				'url' : '{{ url("/save-group") }}',
				'data': { 'accountID':accountID,'contact':contact,'country':country,'postcode':postcode,'kmvalue':kmvalue,'areagrouptext':areagrouptext,'areagroup':areagroup,'unit':unitvaoue,'infsName':infsName,'_token':"{{ csrf_token() }}" },
				'dataType':'html',
				success: function(response){
					var data = $.parseJSON(response);
					if( data.status == 'failed' ) {
						
						toastr.options = {
							positionClass: 'toast-top-center'
						};
						toastr.warning("", data.message);
					}
					else {
						toastr.options = {
							positionClass: 'toast-top-center'
						};
						toastr.success("", data.message);
						$('.ownerTable').html('');
						$('.ownerTable').html(data.response);
					}
					thisObj.find('i').removeClass('fa-spinner fa-spin');
					thisObj.prop('disabled',false);
				}
			});
		});
		
		//Update group info
		$(document).on('click','#update-group',function(e){
			e.preventDefault();
			var thisObj = $(this);
			var data = [];
			
			var accountID = $(".infusaccount").val();
			var contact = $(".infuscontact").val();
			var infsName = $(".infuscontact option:selected").text();
			var country = $(".country").val();
			var postcode = $(".postcode").val();
			var kmvalue = $(".kmvalue").val();
			var unitvaoue = $(".unitvaoue").val();
			var areagrouptext = $(".areagrouptext").val();
			var areagroup = $("input[name='areagroup']:checked").val();
			var onwer_id = $("input[name='onwer_id']").val();
            var onwer_group_id = $("input[name='onwer_group_id']").val();
            
			if( accountID == '' ){
				toastr.options = {
					positionClass: 'toast-top-center'
				};
				toastr.warning("", 'Please select your account from the dropdown.');
				return false;
			}
			if( contact == '' ){
				toastr.options = {
					positionClass: 'toast-top-center'
				};
				toastr.warning("", 'Please select your contact from the dropdown.');
				return false;
			}
			
			thisObj.prop('disabled',true);
			thisObj.find('i').addClass('fa-spinner fa-spin');
			
			data.push({name: "_token", value: "{{ csrf_token() }}"});
			data.push({name: "id", value:onwer_id});
			data.push({name: "accountID", value:accountID});  
			data.push({name: "contact", value:contact});
			data.push({name: "infsName", value:infsName});
			
			if( onwer_group_id != '' && onwer_group_id != undefined ){
			    if( country == '' ){
    				toastr.options = {
    					positionClass: 'toast-top-center'
    				};
    				toastr.warning("", 'Please select country from the dropdown.');
    				return false;
    			}
			    if( areagroup == 'radius_around_postcode' ){
    			    if( $.trim(postcode) == '' ){
        				toastr.options = {
        					positionClass: 'toast-top-center'
        				};
        				toastr.warning("", 'Please enter postcode.');
        				return false;
        			}
        			if( $.trim(kmvalue) == '' ){
        				toastr.options = {
        					positionClass: 'toast-top-center'
        				};
        				toastr.warning("", 'Please enter radius.');
        				return false;
        			}
    			}
    			else{
    			    if( $.trim(areagrouptext) == '' ){
        				toastr.options = {
        					positionClass: 'toast-top-center'
        				};
        				toastr.warning("", 'Please enter postcode list.');
        				return false;
        			}
    			}
    			
    			data.push({name: "onwer_group_id", value:onwer_group_id});
    			data.push({name: "country", value:country});
    			data.push({name: "areagroup", value:areagroup});
    			data.push({name: "postcode", value:postcode});
    			data.push({name: "kmvalue", value:kmvalue});
    			data.push({name: "unit", value:unitvaoue});
    			data.push({name: "areagrouptext", value:areagrouptext});
			}
			
			$.ajax({
				'type': 'post',
				'url' : '{{ url("/update-owner") }}',
				'data': data,
				'dataType':'html',
				success: function(response){
					var data = $.parseJSON(response);
					if( data.status == 'failed' ) {
						
						toastr.options = {
							positionClass: 'toast-top-center'
						};
						toastr.warning("", data.message);
					}
					else {
					    onloadhtml();
					    $('.ownerTable').html('');
						$('.ownerTable').html(data.response);
						
						if( data.response2 != '' ){
    						$('.group-list').html('');
    						$('.group-list').html(data.response2);
						}
						toastr.options = {
							positionClass: 'toast-top-center'
						};
						toastr.success("", data.message);
					}
					thisObj.find('i').removeClass('fa-spinner fa-spin');
					thisObj.prop('disabled',false);
				}
			});
		});
        
        $(document).on('click','.btnaddnewareadrp',function(e){
			e.preventDefault();
			var thisObj = $(this);
			var onwer_group_id = $("input[name='onwer_group_id']").val('new');
			
			var country = $(".country").val('');
			var postcode = $(".postcode").val('');
			var kmvalue = $(".kmvalue").val('');
			var unitvaoue = $(".unitvaoue").val('');
			var areagrouptext = $(".areagrouptext").val('');
			var areagroup = $("input[value='radius_around_postcode']").prop('checked',true);
			$(".groupdetails").show();
        });	
        
        $(document).on('click','.delete-owner',function(e){
            e.preventDefault();
			var thisObj = $(this);
			var ownerid = thisObj.data('id');
			var accountID = $(".infusaccount").val();
			
			if(confirm( "Do you really want to delete the owner?" )){
    			$.ajax({
                    'type': 'post',
                    'url' : '{{ url("/delete-owner") }}',
                    'data': { 'id':ownerid,'accountID':accountID,'_token':"{{ csrf_token() }}" },
                    'dataType':'html',
                    success: function(response){
                        var data = $.parseJSON(response);
                        
                        if( data.status == 'failed' ) {
                            toastr.options = {
                                positionClass: 'toast-top-center'
                            };
                            toastr.warning("", data.message);
                        }
                        else {
                            toastr.options = {
                                positionClass: 'toast-top-center'
                            };
                            toastr.success("", data.message);
                            
                            $('.ownerTable').html('');
						    $('.ownerTable').html(data.response);
						    $(".ownersection").hide();
						    onloadhtml();
                        }
                    }
                });
			}
        });
        
        $(document).on('click','.delete-owner-group',function(e){
            e.preventDefault();
			var thisObj = $(this);
			var groupOwnerId = thisObj.data('id');
			var onwer_id = $('input[name="onwer_id"]').val();
			
			if(confirm( "Do you really want to delete the group?" )){
    			$.ajax({
                    'type': 'post',
                    'url' : '{{ url("/delete-owner-group") }}',
                    'data': { 'owner_gourp_id':groupOwnerId,'owner_id':onwer_id,'_token':"{{ csrf_token() }}" },
                    'dataType':'html',
                    success: function(response){
                        var data = $.parseJSON(response);
                        
                        if( data.status == 'failed' ) {
                            toastr.options = {
                                positionClass: 'toast-top-center'
                            };
                            toastr.warning("", data.message);
                        }
                        else {
                            toastr.options = {
                                positionClass: 'toast-top-center'
                            };
                            toastr.success("", data.message);
                            
                            $('.group-list').html('');
    						$('.group-list').html(data.response);
						    onloadhtml();
                        }
                    }
                });
			}
        });
        
        function onloadhtml(){
            $(".kmvalue").keypress(function(event) {
                return /\d/.test(String.fromCharCode(event.keyCode));
            });
            
            $(".postcode").blur(function(){
                var code = $(this).val();
                var country = $(".country").val();
                
                $.ajax({
                    'type': 'post',
                    'url' : '{{ url("/post-code") }}',
                    'data': { 'code':code,'country':country,'_token':"{{ csrf_token() }}" },
                    'dataType':'html',
                    success: function(response){
                        var data = $.parseJSON(response);
                        $('.suburb-code').html('');
                        if( data.status == 'failed' ) {
                            toastr.options = {
                                positionClass: 'toast-top-center'
                            };
                            toastr.warning("", data.message);
                        }
                        else {
                            $('.suburb-code').html(data.suburb);
                        }
                    }
                });
            }); 
        }
        
        function init(lat,long,radius) {

            var mapCenter = new google.maps.LatLng(lat,long);
            var mapOptions = {
                zoom: 9,
                center: mapCenter,
                mapTypeId: google.maps.MapTypeId.ROADMAP,
        	    disableDefaultUI: true
            }
            var map = new google.maps.Map(document.getElementById("map"), mapOptions);
        
            var marker1 = new google.maps.Marker({
                position: mapCenter,
                map: map,
                zIndex: 2
            });
        
            var circle = new google.maps.Circle({
                map: map,
                radius: radius*1000, //Radius in Millimeter
                center: mapCenter,
                strokeColor: "#0000FF",
                strokeOpacity: 0.4,
                strokeWeight: 2,
                zIndex: 1,
                fillColor: "#FFCC00",
                fillOpacity: 0.25, 
            });
        
            var myOptions = {
                    disableAutoPan: true,
                    pixelOffset: new google.maps.Size(90, -170),
                    position: mapCenter,
                    closeBoxURL: "",
                    isHidden: false,
                    pane: "mapPane",
                    zIndex: 3,
                    enableEventPropagation: true
            };
            var ibLabel = new InfoBox(myOptions);
            ibLabel.open(map);
        
            map.fitBounds(circle.getBounds());
        }
	});
</script>
@endsection
