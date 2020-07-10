@extends('layouts.apptools')
@section('title', 'Calculate Date & Save To Infusionsoft Field')
@section('content')
    <h1 class="title">Calculate Date & Save To Infusionsoft Field</h1>
		
	<div class="inner-content panel-body">
	    <div class="row topboxgrey">
	        <div class="col-lg-2">
	            <img src="{{ asset('assets/images/calculatecalendar_03.jpg') }}" class="img-responsive">
	        </div>
	        <div class="col-lg-10">
	           <h4> What does this script do?</h4>
<p>This simple script allows you to calculate and store a date that is 7 days, 14 days or any other number of days (or hours and minutes) after or before today, or another date you have stored.</p>

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
                <input name="URL" type="text" class="posturlin" value="{{ route('scripts') }}" /><input name="Submit" type="button" value="Merge" class="posturlmerge" />
                
                <h4 class="spacertwnty">Name/Value Pairs</h4>
                <input name="mode" type="text" class="namein" value="mode" /> = <input name="mode_pair" type="text" class="pairin" value="calculate_date" /><br/>
                <input name="fusekey" type="text" class="namein" value="fusekey" /> = <input name="fused_user_pair" type="text" class="pairin" value="{{ \Auth::user()->FuseKey }}" /><br/>
                <input name="app" type="text" class="namein" value="app" /> = <input name="app_pair" id="app_name" type="text" class="pairin" value="a123" /><br/>
                <input name="contactid" type="text" class="namein" value="contactId" /> = <input name="contactid_pair" type="text" class="pairin" value="~Contact.Id~" /><br/>
                <input name="stageid" type="text" class="namein" value="fieldto" /> = <input name="stageid_pair" type="text" class="pairin" value="Contact._CustomField2" /><br/>
                <input name="stageid" type="text" class="namein" value="startdate" /> = <input name="stageid_pair" type="text" class="pairin" value="today" /><br/>
                <input name="stageid" type="text" class="namein" value="add_time" /> = <input name="stageid_pair" type="text" class="pairin" value="14days" />
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
    <td align="left" valign="top" bgcolor="#f8f8f8"><strong>{{ route('scripts') }}</strong></td>
  </tr>
  <tr style="border-bottom:solid 2px #eeeeee;">
    <td align="left" valign="top" bgcolor="#f8f8f8">mode</td>
    <td align="left" valign="top" bgcolor="#f8f8f8">This tells us which script you are trying to use. In this case it is calculate_date. <strong>(REQUIRED) </strong></td>
    <td align="left" valign="top" bgcolor="#f8f8f8"><strong>calculate_date</strong></td>
  </tr>
 <tr style="border-bottom:solid 2px #eeeeee;">
   <td align="left" valign="top" bgcolor="#f8f8f8">fusekey</td>
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
    <td align="left" valign="top" bgcolor="#f8f8f8">This is the id of the contact you want to work with. Leave this as the merge field given. <strong>(REQUIRED) </strong></td>
    <td align="left" valign="top" bgcolor="#f8f8f8"><strong>~Contact.Id~</strong></td>
  </tr>
  <tr style="border-bottom:solid 2px #eeeeee;">
    <td align="left" valign="top" bgcolor="#f8f8f8">fieldto</td>
    <td align="left" valign="top" bgcolor="#f8f8f8">This is the field you want us to store the new date/time in. Read &quot;Important Notes&quot;. <strong>(REQUIRED) </strong></td>
    <td align="left" valign="top" bgcolor="#f8f8f8"><strong>Example: Contact._CustomField2</strong></td>
  </tr>
  <tr style="border-bottom:solid 2px #eeeeee;">
    <td align="left" valign="top" bgcolor="#f8f8f8">startdate</td>
    <td align="left" valign="top" bgcolor="#f8f8f8">This tells us what date/time to add the time too.<strong> (REQUIRED)</strong></td>
    <td align="left" valign="top" bgcolor="#f8f8f8"><strong>Example:
        <br>
      words: today, tomorrow, yesterday
dates: merge in another field such as Contact._PurchaseDate</strong></td>
  </tr>
  <tr style="border-bottom:solid 2px #eeeeee;">
    <td align="left" valign="top" bgcolor="#f8f8f8">add_time</td>
    <td align="left" valign="top" bgcolor="#f8f8f8">How much time to add to the startdate <strong>(REQUIRED)</strong></td>
    <td align="left" valign="top" bgcolor="#f8f8f8"><strong>Example:
        <br>
    + 1 day, + 12 hours, - 1 week</strong></td>
  </tr>
</table>
    			    </p>
    			
    			</div>
    			</div>
    			<div class="row topboxgrey">
	        
	        <div class="col-lg-12">
	           <h4>Important Notes:</h4>
<ol style="margin-left:20px;">
    <li><strong>The easiest way to get this fieldto value is to use the merge field, then remove ~ </strong>- If you use the merge function and select the field you want to add/subtract from, then remove the ~ on either side then this will be in the correct format.
    </li>
    <li><strong>You can test whether your startdate and “add time” text will work with our testing function below</strong> - just enter your example values and see the output.
</li>
</ol>

	        </div>
	    </div>
	    <br/><br/>
	    <p>Test Your Values</p>
	    <div class="row">
    	    <div class="col-md-4">
    	        <form class="form-horizontal">
                    <div class="form-group">
                        <label class="control-label col-sm-2" for="email">StartDate: </label> 
                        <div class="col-sm-9 col-sm-offset-1">
                            <input type="text" name="start_date" class="form-control">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-sm-2" for="pwd">AddTime: </label>
                        <div class="col-sm-9 col-sm-offset-1">
                            <input type="text" name="add_time" class="form-control">
                        </div>
                    </div>
                    <div class="form-group"> 
                        <div class="col-sm-offset-2 col-sm-10">
                            <button type="button" class="btn btn-primary" id="calculate"><i class="fa"></i> Calculate</button>
                        </div>
                    </div>
                </form>
                <div>
                    <!--<p class="outcome">Outcome :  <strong></strong></p>-->
                    <!--<p class="start_calculated">StartDate Calculated :  <strong></strong></p>-->
                    <p class="otp_date">Output date/time :  <strong></strong></p>
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
    			<p><img src="{{ asset('assets/images/fusedtoolsdemotesting14.png') }}" class="img-responsive"></p>
    			
    			<br/>
    			<p>Example sequence setup:</p>
    			<p><img src="{{ asset('assets/images/fusedtoolsdemotesting8.png') }}" class="img-responsive"></p>
    			
    			<br/>
    			<p>Example HTTP Post - see specific setup at the top of this page</p>
    			
    		</div>
    	</div>
    </div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
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


    $(document).ready(function(){
        $(document).on('click','#calculate',function(e){
            var thisObj = $(this);
            var startDate = $('input[name="start_date"]').val();
            var addTime = $('input[name="add_time"]').val();
            
            if( $.trim(startDate) == '' ){
                toastr.options = {
                    positionClass: 'toast-top-center'
                };
                toastr.warning("", 'Please enter startdate');
                return false;
            }
            if( $.trim(addTime) == '' ){
                toastr.options = {
                    positionClass: 'toast-top-center'
                };
                toastr.warning("", 'Please enter addtime');
                return false;
            }
            
            $(".otp_date strong").html('');
            thisObj.prop('disabled',true);
            thisObj.find('i').addClass('fa-spinner fa-spin');
            $.ajax({
	            type: 'POST',
	            url: '{{ url("/get-dates") }}',
	            data: {
	                'startDate':startDate,
	                'addTime':addTime,
	                '_token':'{{ csrf_token() }}',
	            },
	            success: function(response){
	                if( response.time ){
                        $(".otp_date strong").html(response.time);
                    }
                    else{
                        toastr.options = {
                            positionClass: 'toast-top-center'
                        };
                        toastr.warning("", 'Please enter valid values');
                        return false;
                    }
                    thisObj.prop('disabled',false);
                    thisObj.find('i').removeClass('fa-spinner fa-spin');
	            },
	            error : function(response) {
	                toastr.options = {
                        positionClass: 'toast-top-center'
                    };
                    toastr.warning("", 'Please enter valid values');
                    thisObj.prop('disabled',false);
                    thisObj.find('i').removeClass('fa-spinner fa-spin');
                    return false;
	            }
	        });
        });
    });
</script>
@endsection