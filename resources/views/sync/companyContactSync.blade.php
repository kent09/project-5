@extends('layouts.apptools')
@section('title', 'Company Contact Sync')
@section('content')
    <h1 class="title">Company Contact Sync</h1>
    @if(session()->has('danger'))
        <script type="text/javascript">
        swal({
            title: "Error",
            icon: "warning",
            text: "{!! session('danger') !!}",
            buttons: false,
            timer: 2000
        });
        </script>
    @elseif(session()->has('success'))
        <script type="text/javascript">
            swal({
                title: "Success",
                icon: "success",
                text: "{!! session('success') !!}",
                buttons: false,
                timer: 2000
            });
        </script>
    @endif
    <div class="row topboxgrey">
        <div class="col-lg-2">
            <img src="{{ asset('assets/images/9.png') }}" class="img-responsive">
        </div>
        <div class="col-lg-10">
           <h4> What does this sync do?</h4>
            <p>This sync will allow you to map selected company fields to contact fields, that are associated or related to the company.</p>
            <p>Field mapping will be determined by the fields that you will set in the form below.</p>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-10">
            <h3>Quick Start Guide</h3>
            <p>To enable the sync, choose which account below and check the form below which events would be subscribed to and click the subscribe button</p>
                <form method="POST" action="{{url('sync/subscribe')}}">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <select name="infsAcct" class="infusaccount form-control" id="infsBtn">
                      <option value="">Select Your Infusion Account</option>
                      @if( count(\Auth::user()->infsAccounts) > 0 )
                          @foreach( \Auth::user()->infsAccounts as $account )
                            <option value="{{ $account->id }}">{{ $account->account }}</option>
                          @endforeach
                      @endif
                    </select><br/>
                    <table class="table table-bordered table-condensed table-striped">
                        <tr>
                            <td width="150" align="left" valign="middle" bgcolor="#eeeeee"><strong>Event Type</strong></td>
                            <td width="400" align="left" valign="middle" bgcolor="#eeeeee"><strong>Description</strong></td>
                            <td width="50" align="left" valign="middle" bgcolor="#eeeeee"></td>
                        </tr>
                        <tr style="border-bottom:solid 2px #eeeeee;">
                            <td align="left" valign="top" bgcolor="#f8f8f8">COMPANY ADDED</td>
                            <td align="left" valign="top" bgcolor="#f8f8f8">Sync will happen when a company is added</td>
                            <td align="center" valign="top" bgcolor="#f8f8f8">
                                <input id="companyadd" type="checkbox" name="company_add" value="1">
                            </td>
                        </tr>
                        <tr style="border-bottom:solid 2px #eeeeee;">
                            <td align="left" valign="top" bgcolor="#f8f8f8">COMPANY EDITED</td>
                             <td align="left" valign="top" bgcolor="#f8f8f8">Sync will happen when a company is edited/updated</td>
                             <td align="center" valign="top" bgcolor="#f8f8f8">
                                 <input id="companyedit" type="checkbox" name="company_edit" value="1">
                             </td>
                        </tr>
                        <tr style="border-bottom:solid 2px #eeeeee;">
                            <td align="left" valign="top" bgcolor="#f8f8f8">CONTACT ADDED</td>
                             <td align="left" valign="top" bgcolor="#f8f8f8">Sync will happen when a contact is added (if it has a linked company)</td>
                             <td align="center" valign="top" bgcolor="#f8f8f8">
                                 <input id="contactadd" type="checkbox" name="contact_add" value="1">
                             </td>
                        </tr>
                    </table>
                    <div id="procBtn">
                        <button type="submit" class="btn btn-sm btn-primary" id="subsBtn">Subscribe</button>
                        <a class="btn btn-sm btn-danger" id="unsubscribe">Unsubscribe</a>
                        <i class="fa loader"></i>
                    </div>
                </form>
        </div>
    </div>
    <hr>
    <div class="row">
        <div class="col-lg-8">
            <h3>Field Map &nbsp; <button class="btn btn-sm btn-info new-accnt-btn"> New Field </button></h3>
            <p>This is the field mapping associated with the company and contact entities.</p>
            <br />
                @if($accounts)
                    @foreach($accounts as $account)
                    <p id="map-{{ $account->id }}">
                        <h4>Infusionsoft Account: {{ $account->name }}</h4>
                        <table class="table table-bordered table-condensed table-striped">
                            <tr>
                               <th width="400" align="left" valign="middle" bgcolor="#eeeeee"><strong>Company Field</strong></th>
                               <th width="400" align="left" valign="middle" bgcolor="#eeeeee"><strong>Contact Field Map</strong></th>
                               <th width="250" align="left" valign="middle" bgcolor="#eeeeee"><strong>Action</strong></th>
                            </tr>
                            @foreach($account->companyContactMap()->get() as $mapping)
                                <tr>
                                    <td>{{ $mapping->company_field_map }}</td>
                                    <td>{{ $mapping->contact_field_map }}</td>
                                    <td>
                                        <a class="btn btn-xs btn-danger" href="{{ url('sync/fields/'.$mapping->id.'/delete') }}" onclick="return confirm('Are you sure you want to delete this field?')" title="Delete Field">Delete</a>
                                    </td>
                                </tr>
                            @endforeach
                        </table>
                    </p>
                    @endforeach
                @else
                   <p><strong>No Mapping Available</strong></p>
                @endif
        </div>
    </div>
    <hr>

    <div class="modal fade" id="modalAdd" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog" role="document">
            <form method="POST" action="{{url('sync/fields')}}" id="addField"/>
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            <h4 class="modal-title" id="myModalLabel">New Field Mapping</h4>
                        </div>
                        <div class="modal-body">
                           <p class="small">Fill up the form to create a new mapping.</p>
                           <br/>
                           <div class="form-group row">
                               <div class="col-md-8 col-md-offset-2">
                               <label class="form-label">Infusionsoft Account </label>&nbsp;<i class="fa loader2"></i>
                                    <select id="addInfsAcct" class="form-control" name="account" required>
                                        <option value="">Select Your Infusion Account</option>
                                         @foreach($accounts as $account)
                                             <option value="{{ $account->id }}" >{{ $account->name }}</option>
                                         @endforeach
                                    </select>

                                    @if ($errors->has('account'))
                                         <span class="help-block">
                                               <strong>{{ $errors->first('account') }}</strong>
                                         </span>
                                    @endif
                               </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-md-8 col-md-offset-2">
                                <label class="form-label">Company Field</label>
                                    <select id="addComFld" class="form-control" name="cfield" required>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-md-8 col-md-offset-2">
                                <label class="form-label">Contact Field</label>
                                    <select id="addCtFld" class="form-control" name="ctfield" required>
                                    </select>
                                </div>
                            </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Save changes</button>
                        </div>
                </div>
            </div>
            </form>
        </div>
    </div><!-- add field modal -->


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

        // helper functions
        var helpers = {
            buildSelectOption : function(data, dropdown, message) {
                dropdown.html();
                dropdown.append('<option value="">' + message + '</option>')
                if(data != '') {
                    $.each(data, function(k,v) {
                    //console.log(v);
                        dropdown.append('<option value="' + v + '">' + v + '</option>')
                    });
                }
            }
        }

        $("#procBtn").hide();
        $("#unsubscribe").hide();

        // add field modal
        $(document).on('click', '.new-accnt-btn', function() {
            $('#modalAdd').modal('show');
        });

        $(document).on('change','#addInfsAcct', function(e){
            $('.loader2').addClass('fa-spinner fa-spin');
            var compfld;
            var confld;
            e.preventDefault();
            var thisObj = $(this);
            var infsID = thisObj.val();

            $.ajax({
                'type': 'post',
                'url' : '{{ url("/sync/infs/fields") }}',
                'data': { 'infs_account_id':infsID, 'form':'-6' ,'_token':"{{ csrf_token() }}" },
                'dataType':'html',
                success: function(response){
                    compfld = $.parseJSON(response);
                    helpers.buildSelectOption(compfld.fields,$('#addComFld'),'Select Field');
                    $('.loader2').removeClass('fa-spinner fa-spin');
                }
            });

            $.ajax({
                'type': 'post',
                'url' : '{{ url("/sync/infs/fields") }}',
                'data': { 'infs_account_id':infsID, 'form':'-1' ,'_token':"{{ csrf_token() }}" },
                'dataType':'html',
                success: function(response){
                    confld = $.parseJSON(response);
                    helpers.buildSelectOption(confld.fields,$('#addCtFld'),'Select Field');
                    $('.loader2').removeClass('fa-spinner fa-spin');
                }
            });

        });

        $(document).on('change','#infsBtn',function(e){
            $("#procBtn").show();
            $("#companyedit").removeProp("checked");
            $("#contactadd").removeProp("checked");
            $("#companyadd").removeProp("checked");
            e.preventDefault();
            var thisObj = $(this);
            var accountID = thisObj.val();
            var url = '{{ url("/sync") }}';

            // handle the account property
            $.ajax({
                'type': 'get',
                'url' : url + '/' + accountID + '/config',
                'dataType':'html',
                success: function(response){
                    var data = $.parseJSON(response);
                    console.log(data);
                    if(data.count > 0) {
                        $("#unsubscribe").show();
                        if(data.sync.company_add == 1) {
                            $("#companyadd").prop("checked",true);
                        }
                        if(data.sync.company_edit == 1) {
                            $("#companyedit").prop("checked",true);
                        }
                        if(data.sync.contact_add == 1) {
                            $("#contactadd").prop("checked",true);
                        }
                    } else {
                        $("#unsubscribe").hide();
                    }
                }
            });

            $("#unsubscribe").click(function() {
                $('.loader').addClass('fa-spinner fa-spin');
                // unsubscribe the INFS
                $.ajax({
                    'type': 'post',
                    'url' : '{{ url("/sync/unsubscribe") }}',
                    'data': { 'infsAcct':accountID,'_token':"{{ csrf_token() }}" },
                    'dataType':'html',
                    success: function(response){
                        var data = $.parseJSON(response);
                        console.log(data);
                        if( data.status == 'failed' ) {
                            swal({
                                title: "Error",
                                icon: "warning",
                                text: data.message,
                                timer: 1250,
                                buttons: false
                            });
                            $('.loader').removeClass('fa-spinner fa-spin');
                            location.reload();
                        }
                        else {
                            swal({
                                title: "Success",
                                icon: "success",
                                text: data.message,
                                timer: 1250,
                                buttons: false
                            });
                            $('.loader').removeClass('fa-spinner fa-spin');
                            setTimeout(function wait(){
                                    location.reload();
                            }, 1500);
                        }
                    }
                });
            });
        });

    });
</script>
@endsection