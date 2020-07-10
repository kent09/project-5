@extends('layouts.appinvoices')
@section('title', 'Xero CRON Settings')
@section('content')
<style>
  .addNewField .xlshide:first-child .minussign {
  display: none;
  }
</style>
<h1 class="title">Xero CRON Settings</h1>
<div class="inner-content panel-body">
  <div class="row infsAccList">
    <div class="col-lg-5">
      <div class="form-inline">
        <select name="infusaccount" class="infusaccount form-control fullwidthsel" id="infsBtn">
          <option value="">Select Your Infusion Account</option>
          @if( count(\Auth::user()->infsAccounts) > 0 )
          @foreach( \Auth::user()->infsAccounts as $account )
          <option value="{{ $account->id }}">{{ $account->name }}</option>
          @endforeach
          @endif
        </select>
      </div>
    </div>
    <div class="col-lg-2">
      <!-- <a class="btn btn-primary addnewicxls" href="{{ url('/manageaccounts/add') }}"> Add New</a> -->
      <i class="fa"></i>
    </div>
  </div>
  <br/>
  <div class="row xeroAccList">
    <div class="col-lg-5">
      <div class="form-inline">
        <select name="xeroccount" class="xeroccount form-control fullwidthsel">
          <option value="">Select Your Xero Account</option>
          '
          @if( count(\Auth::user()->xeroAccount) > 0 )
          @foreach( \Auth::user()->xeroAccount as $account )
          <option value="{{ $account->id }}">{{ $account->app_name }}</option>
          @endforeach
          @endif
        </select>
      </div>
    </div>
    <div class="col-lg-2">
      <a class="btn btn-primary"  href="{{ url('invoices/xeroaccount') }}"> Add New</a>
      <i class="fa"></i>
    </div>
  </div>
  <input type="hidden" id="loadjs" value="loadjs">
  <div class="infsContacts"></div>
</div>
@endsection
@section('script')
<script>
  jQuery(document).ready(function() {
  $(document).on('change', '#infsBtn, .xeroccount', function(e) {
    e.preventDefault();
    var thisObj = $(this);
    var accountID = $(".infusaccount").val();
    var xeroccount = $(".xeroccount").val();

    if (accountID == '' || xeroccount == '') {
      $('.infsContacts').html('');
      return false;
    }

    if (accountID && xeroccount) {
      thisObj.parents('.infsAccList').find('.fa').addClass('fa-spinner fa-spin');
      thisObj.parents('.xeroAccList').find('.fa').addClass('fa-spinner fa-spin');
      $.ajax({
        'type': 'post',
        'url': '{{ url("/invoices/xero-cron-partial") }}',
        'data': {
          'accountID': accountID,
          'xeroID': xeroccount,
          '_token': "{{ csrf_token() }}"
        },
        'dataType': 'html',
        success: function(response) {
          var data = $.parseJSON(response);
          if (data.status == 'failed') {
            $('#allStages .stage-row').remove();
            toastr.options = {
              positionClass: 'toast-top-center'
            };
            toastr.warning("", data.message);
          } else {
            $('.infsContacts').html('');
            $('.infsContacts').html(data.response);
          }

          if ($("#loadjs").val() == 'loadjs') {
            loadWithHtml();
            $("#loadjs").remove();
          }
          thisObj.parents('.infsAccList').find('.fa').removeClass('fa-spinner fa-spin');
          thisObj.parents('.xeroAccList').find('.fa').removeClass('fa-spinner fa-spin');
          $(".xlshide").show();
          thisObj.prop('disabled', false);
        }
      });
    }

  });

  $(document).on('click', '#save-new-group', function(e) {
    e.preventDefault();
    var thisObj = $(this);
    var accountID = $(".infusaccount").val();
    var xeroccount = $(".xeroccount").val();
    var xero_field = $(".xeroid").val();
    var contact = $(".contact").val();
    var status = $(".status").val();
    var invoice_status = $(".invoice_status").val();
    var tax_status = $(".tax_status").val();
    var sale_account = $(".sale_account").val();

    var infs_fields = [];
    $("select[name='infs_fields[]']").each(function(index, value) {
      infs_fields.push($(this).val());
    });

    if (accountID == '') {
      toastr.options = {
        positionClass: 'toast-top-center'
      };
      toastr.warning("", 'Please select infusionsoft account.');
      return false;
    }
    if (xeroccount == '') {
      toastr.options = {
        positionClass: 'toast-top-center'
      };
      toastr.warning("", 'Please select xero account.');
      return false;
    }

    if (accountID && xeroccount) {
      thisObj.prop('disabled', true);
      thisObj.find('i').addClass('fa-spinner fa-spin');
      $.ajax({
        'type': 'post',
        'url': '{{ url("/invoices/save-xero-cron") }}',
        'data': {
          'accountID': accountID,
          'xeroID': xeroccount,
          'xero_field': xero_field,
          'contact': contact,
          'tax_status': tax_status,
          'status': status,
          'invoice_status': invoice_status,
          'sale_account': sale_account,
          'infs_fields': infs_fields,
          '_token': "{{ csrf_token() }}"
        },
        'dataType': 'html',
        success: function(response) {
          var data = $.parseJSON(response);
          if (data.status == 'failed') {
            $('#allStages .stage-row').remove();
            toastr.options = {
              positionClass: 'toast-top-center'
            };
            toastr.warning("", data.message);
          } else {
            toastr.options = {
              positionClass: 'toast-top-center'
            };
            toastr.success("", data.message);
            $('.infsContacts').html('');
            $(".infusaccount").val('');
            $(".xeroccount").val('');
          }
          thisObj.find('i').removeClass('fa-spinner fa-spin');
          thisObj.prop('disabled', false);
          $("#loadjs").remove();
        }
      });
    }

  });

  function loadWithHtml() {
    $(document).on('click', '.plussign', function(e) {
      var row = $(".addNewField .mainFieldHtml").html();
      $(".addNewField").append('<div class="row xlshide">' + row + '</div>');
    });
    $(document).on('click', '.minussign', function(e) {
      var thisObj = $(this);
      thisObj.parent('div').parent('.xlshide').remove();
    });
  }
});
</script>
@endsection