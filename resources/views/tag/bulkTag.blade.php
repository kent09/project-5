@extends('layouts.apptools')
@section('title', 'Bulk Contact Tagging')
@section('content')
    <h1 class="title">Bulk Contact Tagging</h1>
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
            <img src="{{ asset('assets/images/10.png') }}" class="img-responsive">
        </div>
        <div class="col-lg-10">
           <h4> What does this bulk contact tagging do?</h4>
            <p>This bulk contact tagging will allow you to map selected company fields to contact fields, that are associated or related to the company.</p>
            <p>Field mapping will be determined by the fields that you will set in the form below.</p>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-10">
            <h3>Quick Start Guide</h3>
            <p>To enable the sync, choose which account below and check the form below which events would be subscribed to and click the subscribe button</p>
            <div class="form-group row">
               <div class="col-md-8">
               <label class="form-label">Infusionsoft Account </label>&nbsp;<i class="fa loader2"></i>
                    <select id="addInfsAcct" class="infusaccount form-control" name="account" required>
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
            <form method="POST" action="{{url('/tag/contact/bulk')}}" id="addField"/>
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <div class="form-group row">
                    <div class="col-md-8">
                    <label class="form-label">What field have you provided for matching?</label>
                        <select id="qfield" class="form-control" name="qfield" required>
                        </select>
                    </div>
                </div>

                <div class="form-group row">
                    <div class="col-md-8">
                    <label class="form-label">List of Data</label>
                        <textarea name="listOfData" id="listOfData" cols="30" rows="10" class="form-control" placeholder="Comma separated values, e.g. if field is email user1@email.com, user2@email.com" required></textarea>
                    </div>
                </div>

                <div class="form-group row">
                    <div class="col-md-8">
                    <label class="form-label">What tag do you want to apply?</label>
                        <select id="tag" class="form-control" required name="tags[]" multiple="multiple">
                        </select>
                    </div>
                </div>

                <div class="form-group row">
                    <div class="col-md-12">
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <hr>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>

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
            },

            buildSelectOption2 : function(data, dropdown, message) {
                dropdown.html();
                dropdown.append('<option value="">' + message + '</option>')
                if(data != '') {
                    $.each(data, function(k,v) {
                    //console.log(v);
                    dropdown.append('<option value="' + v.id + '">' + v.name + '</option>')
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
                'data': { 'infs_account_id':infsID, 'form':'-1' ,'_token':"{{ csrf_token() }}" },
                'dataType':'html',
                success: function(response){
                    compfld = $.parseJSON(response);
                    helpers.buildSelectOption(compfld.fields,$('#qfield'),'Select Field');
                    $('.loader2').removeClass('fa-spinner fa-spin');
                }
            });

            $.ajax({
                'type': 'post',
                'url' : '{{ url("/tag/fields") }}',
                'data': { 'infs_account_id':infsID ,'_token':"{{ csrf_token() }}" },
                'dataType':'html',
                success: function(response){
                    confld = $.parseJSON(response);
                    //console.log(confld);
                    helpers.buildSelectOption2(confld.fields,$('#tag'),'Select Tag');
                    $('.loader2').removeClass('fa-spinner fa-spin');

                    $('#tag').select2();
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