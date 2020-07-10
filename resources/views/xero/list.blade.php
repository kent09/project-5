@extends('layouts.appinvoices')
@section('title', 'Xero Accounts')
@section('content')

@if(session()->has('error'))
    <div class="alert alert-danger"> 
    {{ session('error') }}
    </div>
@elseif(session()->has('success'))
    <div class="alert alert-success"> 
    {{ session('success') }}
    </div>
@endif


<h1 class="title">Xero Accounts</h1> 

<div class="inner-content panel-body">
    <div class="row">
        <div class="col-md-12">
            <button style="margin:10px 0px;" class="btn btn-primary pull-right" type="button" data-toggle="modal" data-target="#xeroAdd"><i class="fa fa-plus"></i> Add</button>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <table class="table">
                <tr>
                    <th>Xero App ID</th>
                    <th>App name</th>
                    <th>Expires In</th>
                <tr>
                @foreach( $accounts as $account )
                    <tr>
                        <td>
                            {{ $account->app_id }}
                        </td>
                        <td>
                            {{ $account->app_name }}
                        </td>
                        <td>
                            @if( !empty($account->oauth_expires_in) )
                                @if( Carbon\Carbon::createFromTimestamp($account->oauth_expires_in)->toDateTimeString() < Carbon\Carbon::now() )
                                    <!--href="https://app.fusedtools.com/xeroauth?xero_app_id={{ $account->app_id }}"-->
                                    <a href="{{ url("invoices/xeroauth?xero_app_id=" . $account->app_id)  }}" target="_blank" class="btn btn-danger" title="Reauth"><i class="fa fa-refresh"> Reauth</i></a>
                                @else
                                    {{ Carbon\Carbon::createFromTimestamp($account->oauth_expires_in)->toDateTimeString() }}
                                @endif
                            @endif
                        </td>
                    </tr>
                @endforeach   
            </table>
        </div>    
    </div>
</div>

<div class="modal fade" id="xeroAdd" role="dialog">
    <div class="modal-dialog">
        <form method="POST" action='{{ url("/invoices/addxeroaccount") }}' id="frmAddXero">
            {{ csrf_field() }}
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Add Xero Account</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="name">Account Name:</label>
                        <input type="text" class="form-control" name="name">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit"  class="btn btn-primary"><i class="fa"></i> Add</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
$(document).ready(function(){

    /* Add xero account */
    $(document).on('click','#addXeroAcc',function(e){
        e.preventDefault();
        var thisObj = $(this);
        var name = $("#xeroAccform").find('input[name="account_name"]').val();
        
        if( $.trim(name) == '' ){
            toastr.options = {
                positionClass: 'toast-top-center'
            };
            toastr.warning("", 'Please enter account.');
            return false;
        }
        if( name ) {
            thisObj.prop('disabled',true);
            thisObj.find('i').addClass('fa-spinner fa-spin');
            $.ajax({
                'type': 'post',
                'url' : '{{ url("/invoices/addxeroaccount") }}',
                'data': { 'name':name,'_token':"{{ csrf_token() }}" },
                // 'dataType':'html',
                success: function(response){
                    // var data = $.parseJSON(response);
                    if( response.status == 'success' ){
                        // window.location.href = ;
                        window.open(response.url, '_blank'); 
                    }
                    else{
                        toastr.options = {
                            positionClass: 'toast-top-center'
                        };
                        toastr.warning("", response.message);
                    }
                    
                    thisObj.find('i').removeClass('fa-spinner fa-spin');
					thisObj.prop('disabled',false);
                }
            });
        }
    });
    
   function loadWithHtml(){
       $(document).on('click','.plussign',function(e){
           var row = $(".addNewField > div:first-child()").html();
           $(".addNewField").append('<div class="row xlshide">'+row+'</div>');
       });
       $(document).on('click','.minussign',function(e){
           var thisObj = $(this);
           thisObj.parent('div').parent('.xlshide').remove();
       });
   } 
});
</script>
@endsection