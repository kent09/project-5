@extends('layouts.appinvoices')
@section('title', 'Xero Link Settings')
@section('content')
<style>
  .addNewField .xlshide:first-child .minussign {
  display: none;
  }
</style>
<h1 class="title">Xero Link Settings</h1>
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
          @if( count(\Auth::user()->xeroAccount) > 0 )
          @foreach( \Auth::user()->xeroAccount as $account )
          <option value="{{ $account->id }}">{{ $account->app_name }}</option>
          @endforeach
          @endif
        </select>
      </div>
    </div>
    <div class="col-lg-2">
      <a class="btn btn-primary"  href="{{ url('/invoices/xeroaccount') }}"> Add New</a>
      <i class="fa"></i>
    </div>
  </div>
  <input type="hidden" id="loadjs" value="loadjs">
  <div class="infsContacts"></div>
  <div class="row">
    <div class="col-lg-12">
      <h3>Quick Start Guide</h3>
      <p>To trigger this script and assign an copy a new Infusionsoft order to Xero:</p>
      <div class="qsgtable">
        <h4>POST URL</h4>
        <input name="URL" type="text" class="posturlin" value="https://app.fusedtools.com/scripts/" /><input name="Submit" type="button" value="Merge" class="posturlmerge" />
        <h4 class="spacertwnty">Name/Value Pairs</h4>
        <input name="mode" type="text" class="namein" value="mode" /> = <input name="mode_pair" type="text" class="pairin" value="xero_invoice_copy" /><br/>
        <input name="FuseKey" type="text" class="namein" value="FuseKey" /> = <input name="fused_user_pair" type="text" class="pairin" value="{{ \Auth::user()->FuseKey }}" /><br/>
        <input name="app" type="text" class="namein" value="app" /> = <input name="app_pair" type="text" class="pairin" value="a123" /><br/>
        <input name="contactid" type="text" class="namein" value="contactID" /> = <input name="contactid_pair" type="text" class="pairin" value="~Contact.ID~" /><br/>
        <input name="stageid" type="text" class="namein" value="SalesAccount" /> = <input name="stageid_pair" type="text" class="pairin" value="IE. 201" /><br/>
        <input name="stageid" type="text" class="namein" value="InvoiceStatus" /> = <input name="stageid_pair" type="text" class="pairin" value="1 = send, 0 = draft" /><br/>
        <input name="stageid" type="text" class="namein" value="TaxStatus" /> = <input name="stageid_pair" type="text" class="pairin" value="2 = No Tax, 1 = Add Tax, 0 = Tax Included" /><br/>
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
          <td align="left" valign="top" bgcolor="#f8f8f8">This tells us which script you are trying to use. In this case it is xero invoice copy. <strong>(REQUIRED)</strong>
          </td>
          <td align="left" valign="top" bgcolor="#f8f8f8"><strong id="docs-internal-guid-35de7689-e1e4-c2be-51b1-8b9e3045a200">xero_invoice_copy</strong></td>
        </tr>
        <tr style="border-bottom:solid 2px #eeeeee;">
          <td align="left" valign="top" bgcolor="#f8f8f8">FuseKey</td>
          <td align="left" valign="top" bgcolor="#f8f8f8">This is a fixed value and tells us what fusedtools account this post belongs to.<br />
            (REQUIRED) - Your unique user ID is shown in the value column.
          </td>
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
          <td align="left" valign="top" bgcolor="#f8f8f8">SalesAccount</td>
          <td align="left" valign="top" bgcolor="#f8f8f8">This is the sales account you want the invoice items to match to.<strong>(REQUIRED)</strong>
          </td>
          <td align="left" valign="top" bgcolor="#f8f8f8"><strong id="docs-internal-guid-35de7689-e1e4-e9be-418f-9c9e4f6b0b7e">IE. 201</strong></td>
        </tr>
        <tr style="border-bottom:solid 2px #eeeeee;">
          <td align="left" valign="top" bgcolor="#f8f8f8">InvoiceStatus</td>
          <td align="left" valign="top" bgcolor="#f8f8f8">This is the status of the invoice you want us to create.<strong>(REQUIRED)</strong></td>
          <td align="left" valign="top" bgcolor="#f8f8f8"><strong id="docs-internal-guid-35de7689-e1e4-e9be-418f-9c9e4f6b0b7e2">1 = send, 0 = draft</strong></td>
        </tr>
        <tr style="border-bottom:solid 2px #eeeeee;">
          <td align="left" valign="top" bgcolor="#f8f8f8">TaxStatus</td>
          <td align="left" valign="top" bgcolor="#f8f8f8">This tells us whether to mark the invoice as tax inclusive or to add tax.<strong>(REQUIRED)</strong></td>
          <td align="left" valign="top" bgcolor="#f8f8f8"><strong id="docs-internal-guid-35de7689-e1e4-e9be-418f-9c9e4f6b0b7e2">2 = No Tax, 1 = Add Tax, 0 = Tax Inclusive</strong></td>
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
        'url': '{{ url("/infs-contact-fields") }}',
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
    var xeroid = $(".xeroid").val();
    var compname = $(".compname").val();
    var invoice_status = $(".invoice_status").val();
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
      thisObj.find('i').addClass('fa-spinner fa-spin');
      $.ajax({
        'type': 'post',
        'url': '{{ url("/invoices/save-xero-invoice") }}',
        'data': {
          'accountID': accountID,
          'xeroID': xeroccount,
          'xeroid': xeroid,
          'compname': compname,
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
          thisObj.find('i').addClass('fa-spinner fa-spin');
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