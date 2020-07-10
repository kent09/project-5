@extends('layouts.apptools')
@section('title', 'Import CSV')
@section('content')
<h1 class="title">Import CSV</h1>
<div class="row">
  <div class="col-md-8 col-md-offset-2">
    <div class="panel panel-default panel-import">
      <div class="inner-content panel-body">
        <h4><span>Step 1</span></h4>
        <p class="note">Name and upload your document</p>
        @if ( Session::has('success') )
        <span class="help-block text-center" style=" color:green;">
          <strong>{{ Session::get('success') }}</strong>
        </span>
        @endif
        @if ( Session::has('error') )
        <span class="help-block text-center" style=" color:#C24842;">
          <strong>{{ Session::get('error') }}</strong>
        </span>
        @endif
        <form name="step1" id="step1" method="POST" action="{{ url('/csvimport/step2') }}" enctype="multipart/form-data">
          <br/>
          {{ csrf_field() }}
          <div class="form-group">
            <div class="row">
              <div class="col-md-12">
                <label for="exampleInputFile">Infusionsoft account</label>
                <select class="form-control" name="account" id="account">
                  @if ( count($infusionsoftAccounts) > 0 )
                  <option value=''>Select Infusionsoft Account</option>
                  <?php
                    $is_account_id = '';
                    $csv_file = '';
                    if (Session::has('CSV_import')) {
                      $CSV_import = Session::get('CSV_import');
                      
                      if (isset($CSV_import['account_id']) && !empty($CSV_import['account_id'])) {
                        $is_account_id = $CSV_import['account_id'];
                      }
                      if (isset($CSV_import['csv_file']) && !empty($CSV_import['csv_file'])) {
                        $csv_file = $CSV_import['csv_file'];
                      }
                    }
                    
                  ?>
                  @foreach( $infusionsoftAccounts as $account )
                  <?php
                    $i = 1;
                    $select = '';
                    if (isset($CSV_import)) {
                      if ($account->id == $is_account_id) {
                        $select = "selected='selected'";
                      }
                    } else {
                      if (count($infusionsoftAccounts) == 1 && $i = 1) {
                        $select = "selected='selected'";
                      }
                    }
                  ?>
                  <option value="{{ $account->id }}" <?php  echo $select; ?>>{{ $account->name }}</option>
                  <?php $i++; ?>
                  @endforeach
                  @else
                  <option value=''>Select Infusionsoft Account</option>
                  @endif
                </select>
                <div class="account_error"></div>
              </div>
            </div>
          </div>
          <div class="form-group">
            <div class="row">
              <div class="col-md-12">
                <div class="form-group">
                  <label for="exampleInputFile">Name this import</label>
                  <input type="text" name="import_title" class="form-control" @if ( isset( $CSV_import ) && isset($CSV_import['import_title']) )value="{{ $CSV_import['import_title'] }}" @endif>
                </div>
                @if ( isset( $CSV_import ) )
                <div class="form-group" id="uploaded-file-div">
                  <div class="col-md-3">
                    <label for="exampleInputFile">Uploaded File: </label>{{ $csv_file }}
                  </div>
                  <div class="col-md-12">
                    <input class="btn btn-sm btn-info" type="button" name="change" value="Change" id="change"/>
                  </div>
                </div>
                @endif 
                <div class="form-group" id="select-file-div" @if( isset( $CSV_import ) ) style='display:none' @endif>
                <label for="exampleInputFile">Select file to upload</label>
                <input type="file" name="csv_file">
              </div>
              <input type="hidden" name="parm" id="parm">
            </div>
          </div>
          <div class="form-group">
            <button class="btn btn-primary pull-right submit btn_cls" type="submit">Next <i class="fa fa-arrow-right"></i></button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<style>
</style>
@endsection
@section('script')
<script>
  $(document).ready(function () {
    $(document).off('click', '.submit').on('click', '.submit', function (e) {
      e.preventDefault();
      if ($('#select-file-div').is(':visible')) {
        $("#step1").validate({
          rules: {
            account: {
              required: true
            },
            csv_file: {
              required: true,
              //~ accept: 'in:txt|csv|xls|xlsx'
            }
          },
          messages: {
            account: {
              required: "Please select Infusionsoft Account."
            },
            csv_file: {
              required: "Please upload file.",
              //~ accept: "Invalid file extension, valid extensions are in:txt,csv,xls,xlsx."
            },
          }
        });
        if ($("#step1").valid() == true) {
          $("#step1").submit();
        } else {
          return false;
        }
      } else {
        $("#step1").validate({
          rules: {
            account: {
              required: true
            },
          },
          messages: {
            account: {
              required: "Please select Infusionsoft Account."
            },
          }
        });
        if ($("#step1").valid() == true) {
          $('#parm').val('back');
          $("#step1").submit();
        } else {
          return false;
          $('#parm').val('');
        }
      }

    });

    $(document).off('click', '#change').on('click', '#change', function (e) {
      e.preventDefault();
      $('#uploaded-file-div').css('display', 'none');
      $('#select-file-div').css('display', 'block');
    });
});
  
</script>
@endsection