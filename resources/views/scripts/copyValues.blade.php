@extends('layouts.apptools')
@section('title', 'Copy Values Between Infusionsoft Fields')
@section('content')
    <h1 class="title">Copy Values Between Infusionsoft Fields</h1>
		
	<div class="inner-content panel-body">
	    <div class="row topboxgrey">
	        <div class="col-lg-2">
	            <img src="{{ asset('assets/images/2.png') }}" class="img-responsive">
	        </div>
	        <div class="col-lg-10">
	           <h4> What does this script do?</h4>
                <p>This simple script allows you to copy values between different contact fields.</p>
                <p>This is useful for joining field values together, backing up fields, or moving data before it is overwritten by a campaign.</p>
	        </div>
	    </div>
    	<div class="row">
    		<div class="col-lg-12">
    			<h3>Quick Start Guide</h3>

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

                <hr>     
                           
    			<p>To trigger this script you will need to setup a http post inside your campaign like so:</p>
    			<div class="qsgtable">
                <h4>POST URL</h4>
                <input name="URL" type="text" class="posturlin" value="https://app.fusedtools.com/scripts/" /><input name="Submit" type="button" value="Merge" class="posturlmerge" />
                
                <h4 class="spacertwnty">Name/Value Pairs</h4>
                <input name="mode" type="text" class="namein" value="mode" /> = <input name="mode_pair" type="text" class="pairin" value="copy_values" /><br/>
                <input name="FuseKey" type="text" class="namein" value="FuseKey" /> = <input name="fused_user_pair" type="text" class="pairin" value="{{ \Auth::user()->FuseKey }}" /><br/>
                <input name="app" type="text" class="namein" value="app" /> = <input name="app_pair" id="app_name" type="text" class="pairin" value="a123" /><br/>
                <input name="contactid" type="text" class="namein" value="contactID" /> = <input name="contactid_pair" type="text" class="pairin" value="~Contact.ID~" /><br/>
                <input name="stageid" type="text" class="namein" value="fieldfrom" /> = <input name="stageid_pair" type="text" class="pairin" value="Contact._CustomField1" /><br/>
                <input name="stageid" type="text" class="namein" value="fieldto" /> = <input name="stageid_pair" type="text" class="pairin" value="Contact._CustomField2" /><br/>
                <input name="stageid" type="text" class="namein" value="method" /> = <input name="stageid_pair" type="text" class="pairin" value="replace" />
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
                            <td align="left" valign="top" bgcolor="#f8f8f8">This tells us which script you are trying to use. In this case it is copy_values. <strong>(REQUIRED)</strong></td>
                            <td align="left" valign="top" bgcolor="#f8f8f8"><strong id="docs-internal-guid-35de7689-e1e4-c2be-51b1-8b9e3045a200">copy_values</strong></td>
                          </tr>
                         <tr style="border-bottom:solid 2px #eeeeee;">
                           <td align="left" valign="top" bgcolor="#f8f8f8">FuseKey</td>
                            <td align="left" valign="top" bgcolor="#f8f8f8">This is a fixed value and tells us what fusedtools account this post belongs to.<br />
                            <strong>(REQUIRED)</strong> - Your unique user ID is shown in the value column.</td>
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
                            <td align="left" valign="top" bgcolor="#f8f8f8">This is the id of the contact you want to work with. Leave this as the merge field given. <strong>(REQUIRED)</strong></td>
                            <td align="left" valign="top" bgcolor="#f8f8f8"><strong id="docs-internal-guid-35de7689-e1e4-d517-443f-fa6cc121202d">~Contact.Id~</strong></td>
                          </tr>
                          <tr style="border-bottom:solid 2px #eeeeee;">
                            <td align="left" valign="top" bgcolor="#f8f8f8">fieldfrom</td>
                            <td align="left" valign="top" bgcolor="#f8f8f8">This is the field you want us to copy the value from. Read &quot;Important Notes&quot;. <strong>(REQUIRED)</strong>
                        </td>
                            <td align="left" valign="top" bgcolor="#f8f8f8"><strong id="docs-internal-guid-35de7689-e1e4-e9be-418f-9c9e4f6b0b7e">Example: Contact._CustomField1</strong></td>
                          </tr>
                          <tr style="border-bottom:solid 2px #eeeeee;">
                            <td align="left" valign="top" bgcolor="#f8f8f8">fieldto</td>
                            <td align="left" valign="top" bgcolor="#f8f8f8">This is the field you want us to copy the value to. Read &quot;Important Notes&quot;. <strong>(REQUIRED)</strong></td>
                            <td align="left" valign="top" bgcolor="#f8f8f8"><strong id="docs-internal-guid-35de7689-e1e4-e9be-418f-9c9e4f6b0b7e2">Example: Contact._CustomField2</strong></td>
                          </tr>
                          <tr style="border-bottom:solid 2px #eeeeee;">
                            <td align="left" valign="top" bgcolor="#f8f8f8">method</td>
                            <td align="left" valign="top" bgcolor="#f8f8f8">
                        This tells us whether you want to replace the data in the “fieldto” or append it (add it to the end or the existing value).<strong>(REQUIRED)</strong></td>
                            <td align="left" valign="top" bgcolor="#f8f8f8"><strong id="docs-internal-guid-35de7689-e1e4-e9be-418f-9c9e4f6b0b7e2">replace = delete existing value and input the new one
                                <br>
                            append = add the new value to the end of the existing value </strong></td>
                          </tr>
                        </table>
    			    </p>
    		
    			</div>
    			</div>
    			<div class="row topboxgrey">
	        
	        <div class="col-lg-12">
	           <h4>Important Notes:</h4>
<ol style="margin-left:20px;">
    <li><strong>The easiest way to get this fieldto and fieldfrom value is to use the merge field, then remove ~</strong> - If you use the merge function and select the field you want to copy the value from or to, then remove the ~ on either side then this will be in the correct format.
    </li>
    <li><strong>You can copy from other tables as well</strong> - but it will only ever get the most recently modified value - for example, you could get the name of the recently ordered subscription or product, by using the table schema and entering orders._customfield or similar. This isn't a perfect system though, so test if using this way.</li>
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
                    'url' : '{{ url("/get-infusion-account") }}',
                    'data': { 'accountID':accountID,'_token':"{{ csrf_token() }}" },
                    'dataType': 'json',
                    success: function(response){
                        var data = response;
                        if( data.status == 'failed' ) {
                            toastr.options = {
                                positionClass: 'toast-top-center'
                            };
                            toastr.warning("", data.message);
                        }
                        else {
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