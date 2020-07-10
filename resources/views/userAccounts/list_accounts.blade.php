@extends('layouts.apptools')
@section('title', 'Integrations')
@section('content')
<h1 class="title">Manage Integrations  @if( \Auth::user()->accountLimit() )<a href="{{ url('/manageaccounts/add') }}" style="margin-top:-8px;" class="btn btn-success pull-right"><i class="fa fa-plus"></i> Add Infusionsoft Account</a> @endif </h1>
@if(session()->has('error'))
<div class="alert alert-danger"> 
  {{ session('error') }}
</div>
@elseif(session()->has('success'))
<div class="alert alert-success"> 
  {{ session('success') }}
</div>
@endif
<div class="row">
  @if($accounts->isEmpty())
  <div class="col-md-4 col-sm-6">
    <div class="panel integration-card">
      <div class="panel-body integration-body">
        <div class="row integration-card-status">
          <div class="col-md-12 text-right">
            <span class="integration-status-span integration-disconnected"> <span class="fa fa-exclamation"></span> </span>
          </div>
        </div>
        <div class="row integration-card-img">
          <div class="col-md-12 text-center">
            <img class="img" src="{{ asset('assets/images/infs_logo.png') }}"/>
          </div>
        </div>
        <div class="row integration-card-txt">
          <div class="col-md-12 text-center">
            <p>Status: <strong class="text-danger">Not connected</strong></p>
            <p>&nbsp;</p>
            <p>&nbsp;</p>
          </div>
        </div>
        <div class="row">
          <div class="col-md-12 text-center">
            <a href="{{ url('/manageaccounts/add') }}" class="btn btn-success btn-sm"   title="New Infusionsoft Account">
            <span class="fa fa-plus"></span> Connect
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
  @else
  @foreach($accounts as $account)
  <div class="col-md-4 col-sm-6">
    <div class="panel integration-card">
      <div class="panel-body integration-body">
        <div class="row integration-card-status">
          <div class="col-md-12 text-right">
            @if($account->expire_date < Carbon\Carbon::now())
            <span class="integration-status-span integration-disconnected"> <span class="fa fa-exclamation"></span> </span>
            @else
            <span class="integration-status-span integration-connected"> <span class="fa fa-check"></span> </span>
            @endif
          </div>
        </div>
        <div class="row integration-card-img">
          <div class="col-md-12 text-center">
            <img class="img" src="{{ asset('assets/images/infs_logo.png') }}"/>
          </div>
        </div>
        <div class="row integration-card-txt">
          <div class="col-md-12 text-center">
            <p>Status: {!! $account->expire_date < Carbon\Carbon::now() ? '<strong class="text-danger">Expired</strong>' : '<strong class="text-success">Connected</strong>' !!}</p>
            <p>Account: {{ $account->name or '' }}</p>
            <p>APP: {{ $account->account or '' }}</p>
          </div>
        </div>
        <div class="row">
          <div class="col-md-12 text-center">
            <button class="btn btn-success btn-sm updateAccountClientIdAndSecretModal" title="Add your own Client and Secret ID" data-id="{{ $account->id }}" >
            <span class="fa fa-key"></span>
            </button>
            <button class="btn btn-primary btn-sm rename" title="Rename Account" data-id="{{ $account->id }}" >
            <span class="fa fa-edit"></span>
            </button>
            <button class="btn btn-info btn-sm reauthBtn" data-id="{{ $account->id }}"  title="Re-authenticate Account">
            <span class="fa fa-refresh"></span>
            </button>
            <button class="btn btn-danger btn-sm removeAccount" title="Delete Account" data-id="{{ $account->id }}">
            <span class="fa fa-trash"></span>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
  @endforeach
  @endif
  <div class="col-md-4 col-sm-6">
    <div class="panel integration-card">
      <div class="panel-body integration-body">
        <div class="row integration-card-status">
          <div class="col-md-12 text-right">
            @if($pandadocConnect)
            <span class="integration-status-span integration-connected"> <span class="fa fa-check"></span> </span>
            @else
            <span class="integration-status-span integration-disconnected"> <span class="fa fa-exclamation"></span> </span>
            @endif
          </div>
        </div>
        <div class="row integration-card-img">
          <div class="col-md-12 text-center">
            <img class="img" src="{{ asset('assets/images/pandadoc_logo.png') }}"/>
          </div>
        </div>
        <div class="row integration-card-txt">
          <div class="col-md-12 text-center">
            <p>Status: {!! $pandadocConnect ? '<strong class="text-success">Connected</strong>' : '<strong class="text-danger">Not connected</strong>' !!}</p>
            <p>&nbsp;</p>
            <p>&nbsp;</p>
          </div>
        </div>
        <div class="row">
          <div class="col-md-12 text-center">
            @if($pandadocConnect)
            <a class="btn btn-info btn-sm" href="{{url('/connect/pandadocs')}}"  title="Reconnect pandadoc">
            <span class="fa fa-refresh"></span>
            </a>
            <button class="btn btn-danger btn-sm removepandadoc" title="Delete integration">
            <span class="fa fa-trash"></span>
            </button>
            @else
            <a href="{{ url('/connect/pandadocs') }}" class="btn btn-success btn-sm"   title="New Pandadoc Account">
            <span class="fa fa-plus"></span> Connect
            </a>
            @endif
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-4 col-sm-6">
    <div class="panel integration-card">
      <div class="panel-body integration-body">
        <div class="row integration-card-status">
          <div class="col-md-12 text-right">
            @if(!$docusign || $docusign->expires_date < \Carbon\Carbon::now())
            <span class="integration-status-span integration-disconnected"> <span class="fa fa-exclamation"></span> </span>
            @else
            <span class="integration-status-span integration-connected"> <span class="fa fa-check"></span> </span>
            @endif
          </div>
        </div>
        <div class="row integration-card-img">
          <div class="col-md-12 text-center">
            <img class="img" src="{{ asset('assets/images/docusign_logo.png') }}"/>
          </div>
        </div>
        <div class="row integration-card-txt">
          <div class="col-md-12 text-center">
            <p>Status: 
              @if( $docusign && $docusign->expires_date < \Carbon\Carbon::now() )
              <strong class="text-danger">Expired</strong>
              @elseif( !$docusign)
              <strong class="text-danger">Not connected</strong>
              @else
              <strong class="text-success">Connected</strong>
              @endif
            </p>
            <p>&nbsp;</p>
            <p>&nbsp;</p>
          </div>
        </div>
        <div class="row">
          <div class="col-md-12 text-center">
            @if( ($docusign && $docusign->expires_date < \Carbon\Carbon::now()) || $docusign)
            <a class="btn btn-info btn-sm" href="{{url('/connect/docusign/reauth')}}"  title="refresh token">
            <span class="fa fa-refresh"></span>
            </a>
            <button class="btn btn-danger btn-sm removedocusign" title="delete">
            <span class="fa fa-trash"></span>
            </button>
            @elseif( !$docusign)
            <a href="{{url('/connect/docusign')}}" class="btn btn-success btn-sm"   title="New Docusign Account">
            <span class="fa fa-plus"></span> Connect
            </a>
            @endif
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- Update Xero Accounts -->
  @if($xero_accounts->isEmpty())
  <div class="col-md-4 col-sm-6">
    <div class="panel integration-card">
      <div class="panel-body integration-body">
        <div class="row integration-card-status">
          <div class="col-md-12 text-right">
            <span class="integration-status-span integration-disconnected"> <span class="fa fa-exclamation"></span> </span>
          </div>
        </div>
        <div class="row integration-card-img">
          <div class="col-md-12 text-center">
            <img class="img"width="40%" src="{{ asset('assets/images/xero.png') }}"/>
          </div>
        </div>
        <div class="row integration-card-txt">
          <div class="col-md-12 text-center">
            <p>Status: <strong class="text-info">Not Connected</strong></p>
            <p>&nbsp;</p>
            <p>&nbsp;</p>
          </div>
        </div>
        <div class="row">
          <div class="col-md-12 text-center">
            <a href="{{ url('/invoices/xeroaccount') }}" class="btn btn-success btn-sm"   title="New Xero Account">
            <span class="fa fa-plus"></span> Connect
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
  @else
  @foreach($xero_accounts as $xero)
  <div class="col-md-4 col-sm-6">
    <div class="panel integration-card">
      <div class="panel-body integration-body">
        <div class="row integration-card-status">
          <div class="col-md-12 text-right">
            <span class="integration-status-span integration-connected"> <span class="fa fa-check"></span> </span>
          </div>
        </div>
        <div class="row integration-card-img">
          <div class="col-md-12 text-center">
            <img class="img"width="40%" src="{{ asset('assets/images/xero.png') }}"/>
          </div>
        </div>
        <div class="row integration-card-txt">
          <div class="col-md-12 text-center">
            @if($xero->oauth_expires_in != '')
            <p>Status: {!! date('Y-m-d H:i:s',$xero->oauth_expires_in) < Carbon\Carbon::now() ? '<strong class="text-danger">Expired</strong>' : '<strong class="text-success">Connected</strong>' !!}</p>
            @else
            <p>Status: <strong class="text-warning">Unknown</strong></p>
            @endif
            <!--<p>Account: {{ $xero->app_id  }}</p> -->
            <p>APP: {{ $xero->app_name }}</p>
          </div>
        </div>
        <div class="row">
          <div class="col-md-12 text-center">
            <!--<button class="btn btn-primary btn-sm" title="rename" >
              <span class="fa fa-edit"></span>
              </button>-->
            <a href="{{ url('/invoices/xeroaccount') }}" class="btn btn-primary btn-sm" title="Edit Xero Account">
            <span class="fa fa-edit"></span>
            </a>
            <button class="btn btn-danger btn-sm deleteXero" title="Delete Xero Account" data-id="{{ $xero->id }}">
            <span class="fa fa-trash"></span>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
  @endforeach
  @endif
  <!-- XERO -->
</div>
<div class="modal fade" id="renameAccount" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel">Account Name</h4>
      </div>
      <div class="modal-body">
        <form class="accountRenamForm">
          <input type="hidden" name="id" value="">
          <div class="form-group">
            <label for="email">Name:</label>
            <input type="text" name="name" class="form-control">
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" id="accRenameBtn"><i class="fa"></i> Save</button>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="updateAccountClientIdAndSecret" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel">Add my Keys</h4>
      </div>
      <div class="modal-body">
        <form class="updateAccountClientIdAndSecret">
          <input type="hidden" name="id" value="">
          <div class="form-group">
            <label for="email">Client ID:</label>
            <input type="text" name="client_id" class="form-control">
          </div>
          <div class="form-group">
            <label for="email">Client Secret:</label>
            <input type="text" name="client_secret" class="form-control">
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" id="updateAccountClientIdAndSecretBtn"><i class="fa"></i> Save</button>
      </div>
    </div>
  </div>
</div>
<form action='{{ url("connect/pandadocs/delete") }}' id="frmDeletePandadoc" method="POST">
  {{ csrf_field() }}
</form>
<form action='{{ url("connect/docusign/delete") }}' id="frmDeleteDocusign" method="POST">
  {{ csrf_field() }}
</form>
@endsection
@section('script')
<script>
 $(document).ready(function() {

  /* Reauth account */
  $(document).on('click', '.reauthBtn', function(e) {
    e.preventDefault();
    var thisObj = $(this);
    var accountID = thisObj.attr('data-id');
    if (accountID) {
      thisObj.find('span').addClass('fa-spin');
      $.ajax({
        'type': 'post',
        'url': "{{ url('/manageaccounts/reauthaccount') }}",
        'data': {
          'accountID': accountID,
          '_token': $("input[name='_token']").val()
        },
        'dataType': 'html',
        success: function(response) {
          var data = JSON.parse(response);
          if (data.status == 'success') {
            toastr.options = {
              positionClass: 'toast-top-center'
            };
            toastr.success("", data.msg);
            window.location.href = '{{url("/manageaccounts")}}';
          }
          if (data.status == 'failed' && data.msg == 'Account authentication failed.') {
            toastr.options = {
              positionClass: 'toast-top-center'
            };
            toastr.warning("", data.msg + 'You will redirected to Infuisionsoft to grant a permission to your account.');
            setTimeout(function() {
              window.location.href = '{{url("/manageaccounts/regrant-permission")}}';
            }, 1500);

          }
        },
        error: function() {
          toastr.options = {
            positionClass: 'toast-top-center'
          };
          toastr.warning("", "Unknow error!");
        }
      });
    } else {
      return false;
    }
  });

  /* Delete Xero Account */
  $(document).on('click', '.deleteXero', function(e) {
    e.preventDefault();
    var thisObj = $(this);
    var xeroID = thisObj.attr('data-id');

    if (xeroID) {
      swal({
        title: "Are you sure?",
        icon: "warning",
        buttons: true,
        dangerMode: true
      }).then(function(isConfirm) {
        if (isConfirm) {
          $.ajax({
            'type': 'post',
            'url': "{{ url('invoices/deletexeroaccount') }}",
            'data': {
              'xero_app_id': xeroID,
              '_token': "{{ csrf_token() }}"
            },
            'dataType': 'html',
            success: function(response) {
              var data = JSON.parse(response);
              if (data.status != 'failed') {
                location.reload();
              } else {
                toastr.options = {
                  positionClass: 'toast-top-center'
                };
                toastr.warning("", data.msg);
              }
              thisObj.prop('disabled', false);
              thisObj.find('i').removeClass('fa-spinner fa-spin');
            }
          });
        } else {

        }
      })
    }
  });

  /* Delete Account start */
  $(document).on('click', '.removeAccount', function(e) {
    e.preventDefault();
    var thisObj = $(this);
    var accountID = thisObj.attr('data-id');
    if (accountID) {
      thisObj.prop('disabled', true);
      thisObj.find('i').addClass('fa-spinner fa-spin');
      swal({
        title: "Are you sure?",
        text: "Once deleted, all data will be deleted relevant to this account.",
        icon: "warning",
        buttons: true,
        dangerMode: true
      })
      .then((willDelete) => {
        if (willDelete) {
          $.ajax({
            'type': 'post',
            'url': "{{ url('/manageaccounts/delete') }}",
            'data': {
              'accountID': accountID,
              '_token': $("input[name='_token']").val()
            },
            'dataType': 'html',
            success: function(response) {
              var data = JSON.parse(response);
              if (data.status != 'failed') {
                location.reload();
              } else {
                toastr.options = {
                  positionClass: 'toast-top-center'
                };
                toastr.warning("", data.msg);
              }
              thisObj.prop('disabled', false);
              thisObj.find('i').removeClass('fa-spinner fa-spin');
            }
          });
        } else {
          thisObj.prop('disabled', false);
          thisObj.find('i').removeClass('fa-spinner fa-spin');
        }
      });
    } else {
      return false;
    }
  });
  /* Delete account ends */

  /* Rename account */
  $(document).on('click', '.rename', function(e) {
    var thisObj = $(this);
    $('#renameAccount').modal('show');
    var id = thisObj.data('id');
    $('#renameAccount input[name="id"]').val(id);

    $.ajax({
      'type': 'get',
      'url': "{{ url('/manageaccounts/getname') }}",
      'data': {
        'id': id
      },
      'dataType': 'html',
      success: function(response) {
        var data = JSON.parse(response);
        if (data.status == 'success') {
          $('#renameAccount input[name="name"]').val(data.name);
        } else {
          toastr.options = {
            positionClass: 'toast-top-center'
          };
          toastr.warning("", 'Something went, Please try again.');
        }
        thisObj.prop('disabled', false);
        thisObj.find('i').removeClass('fa-spinner fa-spin');
      }
    });
  });

  $(document).on('click', '#accRenameBtn', function(e) {
    var thisObj = $(this);
    var formObj = $('.accountRenamForm');

    var id = formObj.find('input[name="id"]').val();
    var name = formObj.find('input[name="name"]').val();

    if ($.trim(name) == '') {
      toastr.options = {
        positionClass: 'toast-top-center'
      };
      toastr.warning("", 'Please enter name.');
      return false;
    }

    thisObj.prop('disabled', true);
    thisObj.find('i').addClass('fa-spinner fa-spin');
    $.ajax({
      'type': 'post',
      'url': "{{ url('/manageaccounts/rename') }}",
      'data': {
        'id': id,
        'name': name,
        '_token': $("input[name='_token']").val()
      },
      'dataType': 'html',
      success: function(response) {
        var data = JSON.parse(response);
        if (data.status == 'success') {
          toastr.options = {
            positionClass: 'toast-top-center'
          };
          toastr.success("", data.message);
          location.reload(true);
        } else {
          toastr.options = {
            positionClass: 'toast-top-center'
          };
          toastr.warning("", data.msg);
        }
        thisObj.prop('disabled', false);
        thisObj.find('i').removeClass('fa-spinner fa-spin');
      }
    });
  });


  // Add own Client and Secret ID
  $(document).on('click', '.updateAccountClientIdAndSecretModal', function(e) {
    var thisObj = $(this);
    $('#updateAccountClientIdAndSecret').modal('show');
    var id = thisObj.data('id');
    $('#updateAccountClientIdAndSecret input[name="id"]').val(id);

    $.ajax({
      'type': 'get',
      'url': "{{ url('/manageaccounts/get-client-and-secret-id') }}",
      'data': {
        'id': id
      },
      'dataType': 'html',
      success: function(response) {
        var data = JSON.parse(response);
        if (data.status == 'success') {
          $('.updateAccountClientIdAndSecret input[name="client_id"]').val(data.client_id);
          $('.updateAccountClientIdAndSecret input[name="client_secret"]').val(data.client_secret);
        } else {
          toastr.options = {
            positionClass: 'toast-top-center'
          };
          toastr.warning("", 'Something went, Please try again.');
        }
        thisObj.prop('disabled', false);
        thisObj.find('i').removeClass('fa-spinner fa-spin');
      }
    });
  });

  $(document).on('click', '#updateAccountClientIdAndSecretBtn', function(e) {
    var thisObj = $(this);
    var formObj = $('.updateAccountClientIdAndSecret');

    var id = formObj.find('input[name="id"]').val();
    var client_id = formObj.find('input[name="client_id"]').val();
    var client_secret = formObj.find('input[name="client_secret"]').val();

    thisObj.prop('disabled', true);
    thisObj.find('i').addClass('fa-spinner fa-spin');
    $.ajax({
      'type': 'post',
      'url': "{{ url('/manageaccounts/add-own-client-id-and-secret') }}",
      'data': {
        'id': id,
        'client_id': client_id,
        'client_secret': client_secret,
        '_token': $("input[name='_token']").val()
      },
      'dataType': 'html',
      success: function(response) {
        var data = JSON.parse(response);
        if (data.status == 'success') {
          toastr.options = {
            positionClass: 'toast-top-center'
          };
          toastr.success("", data.message);
          location.reload(true);
        } else {
          toastr.options = {
            positionClass: 'toast-top-center'
          };
          toastr.warning("", data.msg);
        }
        thisObj.prop('disabled', false);
        thisObj.find('i').removeClass('fa-spinner fa-spin');
      }
    });
  });


  //remove pandadoc event
  $(document).on('click', '.removepandadoc', function(e) {
    e.preventDefault();
    var thisObj = $(this);

    swal({
      title: "Are you sure?",
      text: "Delete pandadoc integration?.",
      icon: "warning",
      buttons: true,
      dangerMode: true,
    })
    .then((willDelete) => {
      if (willDelete) {
        $('#frmDeletePandadoc').submit();
      } else {
        thisObj.prop('disabled', false);
        thisObj.find('i').removeClass('fa-spinner fa-spin');
      }
    });
  });

  $(document).on('click', '.removedocusign', function(e) {
    e.preventDefault();
    var thisObj = $(this);

    swal({
      title: "Are you sure?",
      text: "Delete docusign integration?.",
      icon: "warning",
      buttons: true,
      dangerMode: true,
    })
    .then((willDelete) => {
      if (willDelete) {
        $('#frmDeleteDocusign').submit();
      } else {
        thisObj.prop('disabled', false);
        thisObj.find('i').removeClass('fa-spinner fa-spin');
      }
    });
  });

});
  
</script>
@endsection