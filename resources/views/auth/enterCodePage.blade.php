@php
$classButton = 'btn btn-primary';

switch(config('fusedsoftware.subdomain')) {
    case env('FUSEDSUITE_TOOLS_SUBDOMAIN'):
        $classButton = 'btn btn-warning';
        break;
    case env('FUSEDSUITE_DOCS_SUBDOMAIN'):
        $classButton = 'btn btn-success';
        break;
    case env('FUSEDSUITE_INVOICE_SUBDOMAIN'):
        $classButton = 'btn btn-primary';
        break;
}

@endphp
@extends('layouts.headers.initialheader')
@section('content')
    
    @if( \Auth::id() )
        <input id="user_id" type="hidden" value="{{\Auth::id()}}" >
    @elseif( Session::get('user_id') )
        <input id="user_id" type="hidden" value="{{ Session::get('user_id') }}" >
    @endif

    
    <div class="clearfix"></div>
    <div class="enter-code">
        <div class="container contentform">
            <div class="row">
                <div class="col-lg-8 col-lg-offset-3" style="margin: 0 0 115px;">
                    <h1>Confirm Your Email</h1>
                    <h3 class="connectacc">We’ve sent an email to confirm your account.<br>Enter the code below or click the link in the email.</h3>
                </div>
                <div class="col-lg-8 col-lg-offset-3">
                

                    <form method="post" action="#">
                        <div class="formholder marginneg confirmfrm">
                            <input name="confirmone" id="confirmone" type="text" maxlength="1" class="inputfirst data"/> - <input name="confirmtwo" id="confirmtwo" type="text" maxlength="1" class="data"/> - <input id="confirmthree" name="confirmthree" type="text" maxlength="1" class="data"/> - <input id="confirmfour" name="confirmfour" type="text" maxlength="1" class="data"/> - <input id="confirmfive" name="confirmfive" type="text" maxlength="1" class="inputlast data"/>
                        </div>
                        <div class="navigation">
                            <button type="button" class="btn btn-default" onclick="resendActivition();return false;">Resend Activation Code</button>
                            <button type="button" class="{{ $classButton }}" onclick="sendData();">Connect Your Account </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function resendActivition(e) {
            $('#resend-sub').prop('disabled', true);

            $.ajax({
                type: 'POST',
                url: 'resend-activation',
                data:{
                    _token: "{{ Session::token() }}"
                },
                success: function(response){
                    $('#resend-sub').prop('disabled', false);
                    toastr.options = {
                        positionClass: 'toast-top-center'
                    };
                    toastr.success("", "Sent activation code");
                    return false;
                },error: function(response){
                    $('#resend-sub').prop('disabled', false);
                    toastr.options = {
                        positionClass: 'toast-top-center'
                    };
                    toastr.warning("", "Invalid request");
                }
            })
        }

        function sendData(){
            var data = '';
            data +=document.getElementById('confirmone').value;
            data +=document.getElementById('confirmtwo').value;
            data +=document.getElementById('confirmthree').value;
            data +=document.getElementById('confirmfour').value;
            data +=document.getElementById('confirmfive').value;
            var userId = $("#user_id").val();
            $.ajax({
                type: 'POST',
                url: 'verifyCode',
                data:{
                    code: data,
                    userId: userId
                },
                success: function(response){
                    if(JSON.parse(response).status){
                        toastr.options = {
                            positionClass: 'toast-top-center'
                        };
                        toastr.success("", "Verified");

                        window.location.href = '{{route("showConnect")}}'
                    }else{
                        toastr.options = {
                            positionClass: 'toast-top-center'
                        };
                        toastr.warning("", "Please enter correct code");
                    }

                },error: function(response){

                }
            })
        }


        var container = document.getElementsByClassName("confirmfrm")[0];
        container.onkeyup = function(e) {
            var target = e.srcElement;
            var maxLength = parseInt(target.attributes["maxlength"].value, 10);
            var myLength = target.value.length;
            if (myLength >= maxLength) {
                var next = target;
                while (next = next.nextElementSibling) {
                    if (next == null)
                        break;
                    if (next.tagName.toLowerCase() == "input") {
                        next.focus();
                        break;
                    }
                }
            }
        }
    </script>
@endsection