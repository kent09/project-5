@extends('layouts.apptools')
@section('title', 'Postcode Radius Tagging')
@section('content') 
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
<script src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAP_JS_API') }}"></script>

<link rel="stylesheet" href="{{ asset('assets/vendors/sweetalert2/sweetalert2.css') }}">
<script src="{{ asset('assets/vendors/sweetalert2/sweetalert2.js') }}"></script>
<script src="{{ URL::to('assets/js/infobox.js') }}"></script>
    <h1 class="title">Postcode Radius Tagging</h1>
        
    <div class="inner-content panel-body">
        <div class="row topboxgrey">
            <div class="col-lg-2">
                <img src="{{ asset('assets/images/premium_03.jpg') }}" class="img-responsive">
            </div>
            <div class="col-lg-10">
               <h4> What does this script do?</h4>
<p>This tool allows you to enter a postcode and a radius, then it will tag all of your contacts that are within that radius with a specific tag. For example, contacts within 3km of 3104 AUSTRALIA.</p>
<p>This is perfect for geo-targetted email campaigns for promotions and events that are location specific.</p>

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
                    <!-- <a class="btn btn-primary" href="{{ url('/manageaccounts/add') }}"> Add New</a> -->
                    <i class="fa loader"></i>
                </div>          
            </div>
        </div>
        
         <div class="row martop">
            <div class="col-lg-7">
                <table border="0" cellspacing="0" cellpadding="10" class="tagstable infotable spacertwnty" style="margin:0;">
                  <tr>
                    <td align="left" valign="middle" bgcolor="#eeeeee"><strong>Saved Radius</strong></td>
                    <td width="98" align="left" valign="middle" bgcolor="#eeeeee"><strong>Tag ID</strong></td>
                    <td width="98" align="left" valign="middle" bgcolor="#eeeeee"><strong>Count</strong></td>
                    <td width="209" align="left" valign="middle" bgcolor="#eeeeee"><strong>Action</strong></td>
                  </tr>
                </table>    
            </div>
        </div>
        
        <div class="new-post-code" style="display:none;">
            <div class="row martop">
               <div class="col-lg-12">
                 <p><strong>New Postcode Radius Tag</strong></p>
                </div>
            </div>
            
             <br/>
             <div class="row ">
             <div class="col-lg-4">
             <div class="form-inline">
                        <select name="pccountry" class="pccountry form-control" style="width:100%;">
                          <option value="">Country</option>
                          @foreach (App\PostcCountries::where('country_code', '<>', '')->get() as $country )
                              <option value="{{ $country->country_code }}">{{ $country->country_name }}</option>
                          @endforeach
                        </select>
                    </div> 
             </div>
             </div>
             <br/>
             <div class="suburb" style="display:none;">
                 <div class="row">
                    <div class="col-lg-3">
                        <input name="areagroup" checked type="radio" value="radius_around_postcode" id="radiuspostcode" class="radiuspostcoderap"/> <label for="radiuspostcode"> Radius Around Postcode</label>
                    </div>
                </div>
                <div class="radius-around " >
                    <div class="row ">
                       <div class="col-lg-2">
                        <input name="Postcode" type="text" placeholder="Postcode" class="newgrpin Postcode" />
                        </div>
                        <div class="col-lg-2 suburb-code"></div>
                    </div>
                    <br/>
                    <div class="row ">
                       <div class="col-lg-2">
                            <input name="kmvalue" type="text" placeholder="100" class="newgrpin kmvalue" />
                        </div>
                        <div class="col-lg-2">
                        <div class="form-inline">
                                <select name="unitvaoue" class="radius unitvaoue form-control" style="width:100%;">
                                  <option value="KM">KM</option>
                                  <option value="MI">MI</option>
                                </select>
                            </div>  
                        </div>
                        <div class="clearfix"></div>
                        <div class="col-lg-7 text-left">
                            <button class="btn btn-primary" id="radiusMap" style="margin:20px 0px;"><i class="fa"></i> View Postcode List & Radius On Map</button>
                        </div>
                    </div>
                </div>
                <div class="clearfix"></div>
                <div class="row">
                    <div class="col-lg-3">
                        <input name="areagroup" type="radio" value="postcode_list" id="postcode_list" class="postcode_listrap"/> <label for="postcode_list"> Postcode List</label>
                    </div>
                </div>
                <div class="row postcodelist" style="display:none;">
                    <div class="col-lg-7">
                        <textarea name="areagrouplist" cols="" rows="" class="areagrouptext"></textarea>
                    </div>
                    <div class="col-lg-4 smalltext">
                        <p>comma-delimited list, but can include ranges and wildcards.</p>
                
                        <p>IE. 3136, 3140, 3150-3160, 317*, 31**</p>
                    </div>
                </div>
                <br/>
                <div class="row ">
                   <div class="col-lg-7 text-right">
    
                    <button class="btn btn-primary" id="tagContact"><i class="fa"></i> Tag Contacts</button>
                    </div>
                    
                </div>
            </div>
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
        
        /* Get all tags */
        $(document).on('change','#infsBtn',function(e){
            e.preventDefault();
            var thisObj = $(this);
            var accountID = thisObj.val();
            
            if( accountID == '' ){
                $(".new-post-code").hide();
                toastr.options = {
                    positionClass: 'toast-top-center'
                };
                toastr.warning("", 'Please select your account from the dropdown.');
                return false;
            }
            $(".new-post-code").show();
            thisObj.prop('disabled',true);
            $('.loader').addClass('fa-spinner fa-spin');
            if( accountID) {
                $.ajax({
                    'type': 'post',
                    'url' : '{{ url("/postc-tags") }}',
                    'data': { 'accountID':accountID,'_token':"{{ csrf_token() }}" },
                    'dataType':'html',
                    success: function(response){
                        var data = $.parseJSON(response);
                        if( data.status == 'failed' ) {
                            $('.tagstable .tags-row').remove();
                            toastr.options = {
                                positionClass: 'toast-top-center'
                            };
                            toastr.warning("", data.message);
                        }
                        else {
                            $('.tagstable').html('');
                            $('.tagstable').html(data.message);
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
        
        $(document).on('change','.pccountry',function(e){
            e.preventDefault();
            if( $(this).val() != '' ){
                $(".suburb").show();
            }
            else{
                $(".Postcode").val('');
                $(".kmvalue").val('');
                $(".suburb").hide();
            }
            $(".radiuslist div").html('');
            $(".radiusmap").hide();
        });    

        //Retag
        $(document).on('click','.re-tag',function(e){
            e.preventDefault();
            var thisObj = $(this);

            $('.row-td-loader-'+thisObj.data('id')).fadeIn('fast', function() {
                $('.row-td-option-'+thisObj.data('id')).fadeOut('fast');
            });

            Swal.fire({
                title: 'Are you sure?',
                text: "Do you really want to re-apply the tag?",
                type: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, re-apply it!',
                cancelButtonText: 'No, cancel!',
                reverseButtons: true,
                confirmButtonClass: 'btn btn-success',
                cancelButtonClass: 'btn btn-danger',
                buttonsStyling: true,
                allowEscapeKey: false,
                allowOutsideClick: false,
            }).then((result) => {
              if (result.value) {

                $.ajax({
                    'type': 'post',
                    'url' : '{{ url("/post-code-retag") }}',
                    'data': { 'id':thisObj.data('id'),'_token':"{{ csrf_token() }}" },
                    'dataType':'html',
                    success: function(response){
                        var data = $.parseJSON(response);
                        $('.suburb-code').html('');
                        if( data.status == 'failed' ) {
                            toastr.options = {
                                positionClass: 'toast-top-center'
                            };
                            toastr.warning("", data.message);

                            $('.row-td-loader-'+thisObj.data('id')).fadeOut('fast', function() {
                                $('.row-td-option-'+thisObj.data('id')).fadeIn('fast');
                            });
                        }
                        else {
                            toastr.options = {
                                positionClass: 'toast-top-center'
                            };
                            toastr.success("", data.message);
                            $('.tagstable').html(data.response);

                            $('.row-td-loader-'+thisObj.data('id')).fadeOut('fast', function() {
                                $('.row-td-option-'+thisObj.data('id')).fadeIn('fast');
                            });
                        }
                    }
                });

              } else if (
                // Read more about handling dismissals
                result.dismiss === Swal.DismissReason.cancel
              ) {
                Swal.fire(
                  'Cancelled',
                  'Your tag is safe.',
                  'error'
                )

                $('.row-td-loader-'+thisObj.data('id')).fadeOut('fast', function() {
                    $('.row-td-option-'+thisObj.data('id')).fadeIn('fast');
                });
              }
            })

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
        
        //Delete tag
        $(document).on('click','.delete-tag',function(e){
            e.preventDefault();
            var thisObj = $(this);

            $('.row-td-loader-'+thisObj.data('id')).fadeIn('fast', function() {
                $('.row-td-option-'+thisObj.data('id')).fadeOut('fast');
            });

            Swal.fire({
                title: 'Are you sure?',
                text: "Do you really want to delete the tag?",
                type: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'No, cancel!',
                reverseButtons: true,
                confirmButtonClass: 'btn btn-success',
                cancelButtonClass: 'btn btn-danger',
                buttonsStyling: true,
                allowEscapeKey: false,
                allowOutsideClick: false,
            }).then((result) => {
                if (result.value) {

                    $.ajax({
                        'type': 'post',
                        'url' : '{{ url("/post-code-delete") }}',
                        'data': { 'id':thisObj.data('id'),'_token':"{{ csrf_token() }}" },
                        'dataType':'html',
                        success: function(response){
                            var data = $.parseJSON(response);
                            $('.suburb-code').html('');
                            if( data.status == 'failed' ) {
                                toastr.options = {
                                    positionClass: 'toast-top-center'
                                };
                                toastr.warning("", data.message);

                                $('.row-td-loader-'+thisObj.data('id')).fadeOut('fast', function() {
                                    $('.row-td-option-'+thisObj.data('id')).fadeIn('fast');
                                });                                
                            }
                            else {
                                toastr.options = {
                                    positionClass: 'toast-top-center'
                                };
                                toastr.success("", data.message);
                                $('.tagstable').html(data.response);

                                $('.row-td-loader-'+thisObj.data('id')).fadeOut('fast', function() {
                                    $('.row-td-option-'+thisObj.data('id')).fadeIn('fast');
                                });
                            }
                        }
                    });

                } else if (result.dismiss === Swal.DismissReason.cancel) {
                    Swal.fire(
                      'Cancelled',
                      'Your tag is safe.',
                      'error'
                    )

                    $('.row-td-loader-'+thisObj.data('id')).fadeOut('fast', function() {
                        $('.row-td-option-'+thisObj.data('id')).fadeIn('fast');
                    });
                }
            })

        });
       
        $(".Postcode").blur(function(){
            var code = $(this).val();
            var country = $(".pccountry").val();
            
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
        
        $(document).on('click','#tagContact',function(e){
            e.preventDefault();
            var thisObj = $(this);
            
            var country = $(".pccountry").val();
            var postcode = $(".Postcode").val();
            var kmvalue = $(".kmvalue").val();
            var radius = $(".radius").val();
            var suburb = $(".suburb-code").html();
            var unit = $(".unitvaoue").val();
            var account = $(".infusaccount").val();
            var areagrouptext = $(".areagrouptext").val();
            var areagroup = $("input[name='areagroup']:checked").val();
            
            
            
            if( $.trim(country) == '' ){
                toastr.options = {
                    positionClass: 'toast-top-center'
                };
                toastr.warning("", 'Please select country from the dropdown.');
                return false;
            }
            if( areagroup == 'radius_around_postcode' ){
                if( $.trim(suburb) == '' ){
                    toastr.options = {
                        positionClass: 'toast-top-center'
                    };
                    toastr.warning("", 'Please enter a valid code to get a suburb.');
                    return false;
                }
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
            thisObj.find('i').addClass('fa-spinner fa-spin');
            thisObj.prop('disabled',true);
            $.ajax({
                'type': 'post',
                'url' : '{{ url("/tag-contact") }}',
                'data': { 'account':account,'country':country,'postcode':postcode,'radius':kmvalue,'unit':unit,'areagrouptext':areagrouptext,'areagroup':areagroup,'_token':"{{ csrf_token() }}" },
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
                        $('.suburb-code').html('');
                        $('.Postcode').val('');
                        $('.kmvalue').val('');
                        $('.pccountry').val('');
                        $('.suburb').hide();
                        $('.areagrouptext').html('');
                        if( data.response ) {
                            $('.tagstable').html(data.response);
                        }
                    }
                    thisObj.find('i').removeClass('fa-spinner fa-spin');
                    thisObj.prop('disabled',false);
                }
            });
        });
        
        $(document).on('click','#radiusMap',function(e){
            e.preventDefault();
            var thisObj = $(this);
            var postcode = $('.Postcode').val();
            var country =  $('.pccountry').val();
            var radius = $('.kmvalue').val();
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
        
        $(".kmvalue").keypress(function(event) {
            return /\d/.test(String.fromCharCode(event.keyCode));
        });
    });
</script>
@endsection