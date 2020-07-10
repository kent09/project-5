@extends('layouts.apptools')
@section('title', 'Import CSV')
@section('content')
    <h1 class="title">Import CSV</h1>
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default panel-import">
                <div class="inner-content panel-body">
                    <h4><span>Step 2</span></h4>
                    <p class="note">
                        Now let's match the columns in your uploaded file to your Infusion Soft list.
                    </p>
                    <span class="help-block text-center error_msg" style="display:none; color:#C24842;">
                    <strong>Please select Infusionsoft fields.</strong>
                    </span>

                    <div class="form-group">
                        <div class="row">

                            <div class="col-md-12">
                                <div class="form-group">
                                    <lable>Use Settings from a previous import</lable>
                                    <select class="form-control" name="import_account" id="importAccount">
                                        <option>Select imported settings</option>
                                        @foreach( $csv_imports as $import)
                                            <option value="{{ $import->id }}">{{ $import->import_title }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="clearfix"></div>
                            <hr/>

                            <div class="col-md-4 ">
                                <b>Your CSV Fields</b>
                            </div>
                            <div class="col-md-8">
                                <b>Infusionsoft Fields</b>
                            </div>
                        </div>
                    </div>

                    <form name="step2" method="POST" action="{{ url('/csvimport/step2') . '/'. $csv_import->id }}" id="step2">
                        {{ csrf_field() }}
                        <input type="hidden" id="csvimportid" value="{{$csv_import->id}}" />
                        @foreach( $csv_fields as $key => $csv_field_name)
                            <div class="contact-fields">
                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-md-4">
                                            {{ $csv_field_name }}
                                        </div>

                                        <div class="col-md-8">
                                            <select class="form-control map-fields" name="csv_settings[contacts][{{ $csv_field_name }}]">
                                                <option value='0' class="fields">Skip this Field</option>
                                                @if ( count($infs_fields['contacts']) )
                                                    @foreach($infs_fields['contacts'] as $infs_field => $value)
                                                            <option
                                                                    value='{{ $value['is_custom'] ? '_'.$infs_field : $infs_field }}'
                                                                    class="fields"
                                                                    {{ $csv_import->hasSettings("contacts")
                                                                        ? $csv_import->getSettingsKey("contacts","$csv_field_name") == $infs_field
                                                                            ? 'selected'
                                                                            : ''
                                                                        : ''
                                                                    }}
                                                            >
                                                                {{ $infs_field }}
                                                            </option>

                                                    @endforeach
                                                @endif
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        @endforeach

                        <div class="form-group row">
                            <div class="col-md-12">
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox"
                                               name="create_company"
                                               id="create_company"
                                               {{ $csv_import->hasSettings("company") ? 'checked' : '' }}
                                        >
                                        Are We Creating and/or Linking Company Records?
                                    </label>
                                </div>
                            </div>
                        </div>

                        @foreach( $csv_fields as $key => $csv_field_name)

                            <div class="company-fields"
                            style="{{ $csv_import->hasSettings("company") ? '' : 'display:none' }}">
                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-md-4">
                                            {{ $csv_field_name }}
                                        </div>

                                        <div class="col-md-8">
                                            <select class="form-control map-fields" name="csv_settings[company][{{ $csv_field_name }}]">
                                                <option value='0' class="fields">Skip this Field</option>
                                                @if ( count($infs_fields['company']) )
                                                    @foreach($infs_fields['company'] as $infs_field => $name)
                                                        <option
                                                                value='{{ $infs_field }}'
                                                                class="fields"
                                                                {{ $csv_import->hasSettings("company")
                                                                    ? $csv_import->getSettingsKey("company","$csv_field_name") == $infs_field
                                                                        ? 'selected'
                                                                        : ''
                                                                    : ''
                                                                }}
                                                        >
                                                            {{ $infs_field }}
                                                        </option>

                                                    @endforeach
                                                @endif
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        @endforeach

                        <div class="form-group">
                            <a class="btn btn-danger pull-left btn_cls" id="step-back" data-step="1" data-importid="{{$csv_import->id}}" href="{{ url('tools/csvimport/step1') .'/' .$csv_import->id }}"><i class="fa fa-arrow-left"></i> Back</a>
                            <button class="btn btn-primary pull-right submit btn_cls" type="submit">Select Import Options <i class="fa fa-arrow-right"></i></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')

    <script>
        $(document).ready(function(){
            $(document).on('click','input[name="create_company"]',function() {
                var thiObj = $(this);
                if( thiObj.is(':checked') ){
                    $(".company-fields").slideDown();
                }
                else {
                    $(".company-fields").slideUp();
                }
            });

            // $(document).on('click','input[name="order_creation"]',function() {
            //     var thiObj = $(this);
            //     if( thiObj.is(':checked') ){
            //         $(".orders").slideDown();
            //     }
            //     else {
            //         $(".orders").slideUp();
            //     }
            // });

            // $(document).off('click','.submit').on('click','.submit',function() {
            //     var empty_val = 1;
            //     $("#step2").find(".map-fields").each(function() {
            //         if($(this).val() != 0){
            //             empty_val = 0;
            //         }
            //     });
            //     if(empty_val){
            //         $('.error_msg').css('display','block');
            //     } else {
            //         $('#step2').submit();
            //     }
            // });

            /* Get all stages */
            $(document).on('change','#importAccount',function(e){
                e.preventDefault();
                var thisObj = $(this);
                var selected_import_id = thisObj.val();
                var import_id = $('#csvimportid').val();
                var url = "/csvimport/step2/" + import_id + "/settings";

                thisObj.prop('disabled',true);
                $.ajax({
                    'type': 'post',
                    'url' : url,
                    'data': {
                        'selected_import_id':selected_import_id,
                        '_token':"{{ csrf_token() }}",
                    },
                    'dataType':'html',
                    success: function(response){

                        if (response) {
                            toastr.options = {
                                positionClass: 'toast-top-center'
                            };
                            toastr.success("", "Success");

                            setTimeout(function(){ location.reload(); }, 2000);
                        } else {
                            $('#allStages .stage-row').remove();
                            toastr.options = {
                                positionClass: 'toast-top-center'
                            };
                            toastr.warning("", 'something went wrong');
                        }

                        thisObj.prop('disabled',false);
                    }
                });
            });
        });
    </script>
@endsection