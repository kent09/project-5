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

                    <form name="step1" id="step1" method="POST" action="{{ url('/csvimport/step1') . '/'. $csv_import->id }}" enctype="multipart/form-data">
                        <br/>
                        {{ csrf_field() }}
                        <div class="form-group">
                            <div class="row">
                                <div class="col-md-12">
                                    <label for="exampleInputFile">Infusionsoft account</label>
                                    <select class="form-control" name="account_id" id="infs_account">
                                            <option value=''>Select Infusionsoft Account</option>
                                            @foreach($accounts as $account)
                                                <option value="{{ $account->id }}" {{ $csv_import->account_id == $account->id ? 'selected' : ''}}>{{ $account->name }}</option>
                                            @endforeach
                                    </select>
                                    <div class="account_error"></div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <label for="exampleInputFile">Name this import</label>
                                                <input type="text" name="import_title" class="form-control" value="{{ $csv_import->import_title == 'DRAFT' ? '' : $csv_import->import_title }}">
                                            </div>
                                        </div>
                                    </div>
                                    @if ($csv_import->import_title !== 'DRAFT')
                                    @else
                                        <div class="form-group" id="select-file-div">
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <label for="exampleInputFile">Select file to upload</label>
                                                </div>
                                                <div class="col-md-12">
                                                    <input class="form-control-file" type="file" name="csv_file" />
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    <input type="hidden" name="id" id="id" value="{{$csv_import->id}}">
                                </div>
                            </div>
                            <div class="form-group">
                                <button class="btn btn-primary pull-right submit btn_cls" type="submit">Next <i class="fa fa-arrow-right"></i></button>
                            </div>
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