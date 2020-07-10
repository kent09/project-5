@extends('layouts.apptools')
@section('title', 'Import CSV')
@section('content')


    <div class="">

        <h1 class="title">Import CSV</h1>
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default panel-import">
                    <div class="inner-content panel-body">
                        <input type="hidden" id="counter-contact" value="{{$csv_import->hasSettings("contacts") ? count($csv_settings["contacts"]) : 0}}" />
                        <input type="hidden" id="counter-contact-results" value="{{isset($import_results['match']["contacts"]) ? count($import_results['match']["contacts"]) : 1}}" />
                        <input type="hidden" id="counter-company" value="{{$csv_import->hasSettings("company") ? count($csv_settings["company"]) : 0}}" />
                        <input type="hidden" id="counter-company-results" value="{{isset($import_results['match']["company"]) ? count($import_results['match']["company"]) : 1}}" />

                        <h4><span>Step 3</span></h4>
                        <p class="note">CSV Import Settings</p>
                        <br/>
                        <form name="step4" method="post" action="{{ url('/csvimport/step3') . '/' . $csv_import->id }}" id="myForm">
                            {{ csrf_field() }}
                            <div class="form-group col-md-6" style="min-height:120px;">
                                <label>Select Your Contact Options</label>
                                <div class="radio">
                                    <label>
                                        <input type="radio" name="filter_contact" value="both"
                                               {{ isset($import_results['filter_contact'])
                                                    ? $import_results['filter_contact'] == 'both'
                                                        ? 'checked' : '' : 'checked'}}
                                        >
                                        Create & Update customers
                                    </label>
                                </div>
                                <div class="radio">
                                    <label>
                                        <input type="radio"
                                               name="filter_contact"
                                               value="create"
                                                {{ isset($import_results['filter_contact'])
                                                    ? $import_results['filter_contact'] == 'create'
                                                        ? 'checked' : '' : ''}}
                                        >
                                        Create customers only
                                    </label>
                                </div>
                                <div class="radio">
                                    <label>
                                        <input
                                                type="radio"
                                                name="filter_contact"
                                                value="update"
                                                {{ isset($import_results['filter_contact'])
                                                    ?  $import_results['filter_contact'] == 'update'
                                                        ? 'checked' : '' : ''}}
                                        >
                                        Update Customers only
                                    </label>
                                </div>

                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" id="email_opt_in" name="email_opt_in"
                                        {{ isset($import_results['email_opt_in']) ? 'checked' : '' }}
                                        >Email Opt-in
                                    </label>
                                    <br>

                                    <div class="content-opt-in-reason" style="{{ isset($import_results['email_opt_in']) ? '' : 'display:none' }}">
                                        <label>Reason:</label>
                                        <label><input type="text" name="email_opt_in_reason" value="{{isset($import_results['email_opt_in']) ? $import_results['email_opt_in_reason'] : ''}}"></label>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group col-md-6 contact-fields" style="min-height:120px;">

                                <label>How Should We Match Contacts</label>

                                <div class="contact-matching">

                                    @if($csv_import->hasSettings("contacts"))

                                        @if(isset($import_results['match']["contacts"]))
                                            @foreach($import_results['match']["contacts"] as $key => $value)

                                                <div class="form-group">
                                                    @if($key == 0)
                                                        <label> First Try: </label>
                                                    @else
                                                        <label> Then Try: </label>
                                                    @endif

                                                    <div class="contact-dropdowns">
                                                        <select name="match[contacts][]" class="contact-select">
                                                            @foreach( $csv_settings["contacts"] as $csv_field => $infs_field)
                                                                <option
                                                                        value="{{ $infs_field }}"
                                                                        {{$value == $infs_field ? 'selected' : '' }}>
                                                                    {{ $infs_field[0] == '_' ? substr($infs_field, 1) : $infs_field }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        <i class="fa fa-plus add-field-match" data-name="contact"></i>
                                                        <i data-name="contact" class="fa fa-minus remove-field-match"></i>
                                                    </div>
                                                </div>
                                            @endforeach
                                        @else
                                            <div class="form-group">
                                                <label> First Try: </label>

                                                <div class="contact-dropdowns">
                                                    <select name="match[contacts][]">
                                                        @foreach( $csv_settings["contacts"] as $csv_field => $infs_field)
                                                            <option value="{{ $infs_field }}">
                                                                {{ $infs_field[0] == '_' ? substr($infs_field, 1) : $infs_field }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    <i class="fa fa-plus add-field-match" data-name="contact"></i>
                                                    <i data-name="contact" class="fa fa-minus remove-field-match"></i>
                                                </div>
                                            </div>
                                        @endif
                                    @endif
                                </div>

                                <small>Then create a contact.</small>
                            </div>

                        <div class="clearfix"></div>

                        <hr>

                        @if($csv_import->hasSettings("company"))

                            <div class="form-group col-md-6" style="min-height:120px;">
                                <label>Select Your Company Options</label>
                                <div class="radio">
                                    <label>
                                        <input type="radio" name="filter_company" value="both"
                                                {{ isset($import_results['filter_company'])
                                                    ? $import_results['filter_company'] == 'both'
                                                        ? 'checked' : '' : 'checked'}}
                                        >
                                        Create & Update companies
                                    </label>
                                </div>
                                <div class="radio">
                                    <label>
                                        <input type="radio"
                                               name="filter_company"
                                               value="create"
                                                {{ isset($import_results['filter_company'])
                                                    ? $import_results['filter_company'] == 'create'
                                                        ? 'checked' : '' : ''}}
                                        >
                                        Create companies only
                                    </label>
                                </div>
                                <div class="radio">
                                    <label>
                                        <input
                                                type="radio"
                                                name="filter_company"
                                                value="update"
                                                {{ isset($import_results['filter_company'])
                                                    ? $import_results['filter_company'] == 'update'
                                                        ? 'checked' : '' : ''}}
                                        >
                                        Update companies only
                                    </label>
                                </div>
                            </div>

                                <div class="form-group col-md-6 company-fields" style="min-height:120px;">

                                    <label>How Should We Match Companies</label>

                                    <div class="company-matching">

                                        @if($csv_import->hasSettings("company"))

                                            @if(isset($import_results['match']["company"]))
                                                @foreach($import_results['match']["company"] as $key => $value)
                                                    <div class="form-group">
                                                        @if($key == 0)
                                                            <label> First Try: </label>
                                                        @else
                                                            <label> Then Try: </label>
                                                        @endif

                                                        <div class="company-dropdowns">
                                                            <select name="match[company][]">
                                                                @foreach( $csv_settings["company"] as $csv_field => $infs_field)
                                                                    <option
                                                                            value="{{ $infs_field }}"
                                                                            {{$value == $infs_field ? 'selected' : '' }}>
                                                                        {{ $infs_field[0] == '_' ? substr($infs_field, 1) : $infs_field }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                            <i class="fa fa-plus add-field-match" data-name="company"></i>
                                                            <i data-name="company" class="fa fa-minus remove-field-match"></i>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            @else
                                                <div class="form-group">
                                                    <label> First Try: </label>

                                                    <div class="company-dropdowns">
                                                        <select name="match[company][]">
                                                            @foreach( $csv_settings["company"] as $csv_field => $infs_field)
                                                                <option value="{{ $infs_field }}">
                                                                    {{ $infs_field[0] == '_' ? substr($infs_field, 1) : $infs_field }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        <i class="fa fa-plus add-field-match" data-name="company"></i>
                                                        <i data-name="company" class="fa fa-minus remove-field-match"></i>
                                                    </div>
                                                </div>
                                            @endif
                                        @endif
                                    </div>

                                    <small>Then create a company.</small>
                                </div>

                        @endif

                <div class="form-group col-md-12">
                    <a class="btn btn-danger pull-left btn_cls" id="step-back" data-step="2" data-importid="{{$csv_import->id}}" href="{{ url('tools/csvimport/step2') .'/' .$csv_import->id }}"><i class="fa fa-arrow-left"></i> Back</a>
                    <button class="btn btn-primary pull-right btn_cls step4" type="submit">Apply Tags <i class="fa fa-arrow-right"></i></button>
                </div>
                </form>
            </div>
        </div>
    </div>
    </div>
    </div>
    <style>
        ul.token-input-list {
            width:350px !important;
        }
        .contact-dropdowns, .company-dropdowns, .order-dropdowns, .product-dropdowns {
            display: inline-block;
        }
    </style>
@endsection
@section('script')
    <script type="text/javascript">
        function createDropDown(field,dropdowns){
            var html = '<div class="form-group"><label>Then Try : </label>'+dropdowns+'</div>';
            return html;
        }

        $(document).ready(function(){
            var counter = {
                'company' : parseInt(document.getElementById('counter-company').value),
                'contact' : parseInt(document.getElementById('counter-contact').value),
                'company_counter' : parseInt(document.getElementById('counter-company-results').value),
                'contact_counter' : parseInt(document.getElementById('counter-contact-results').value),
            };

            $(document).on("click", ".add-field-match", function(){
                var thisObj = $(this);
                var field = thisObj.data('name');

                counter_field = counter[field + "_counter"];

                 if (counter_field < counter[field] ) {
                    dropdown_class = "." + field + "-dropdowns";

                    var dropdowns = $(dropdown_class).html();
                    var html = createDropDown(field,dropdowns, counter);

                    thisObj.closest('.'+field+'-matching').append(html);

                    counter[field + "_counter"] += 1;
                    console.log(counter);
                }
            });

            $(document).on("click", ".remove-field-match", function(){
                var thisObj = $(this);
                var field = thisObj.data('name');
                if (counter[field + "_counter"] - 1 > 0) {
                    thisObj.closest('.form-group').remove();

                    counter[field + "_counter"] -= 1;
                    console.log(counter);
                }
            });

            $('input[name=email_opt_in]').change(function(){
                var checked = $('#email_opt_in:checkbox:checked').length > 0;

                // change value if checked is true
                checked ? $(".content-opt-in-reason").show() : $(".content-opt-in-reason").hide();
            });

            $('input[name=filter_contact]').change(function(){
                var value = $(this).val();
                if( value != 'create' ){
                    $(".contact-fields").show();
                }
                else {
                    $(".contact-fields").hide();
                }
            });
            $('input[name=filter_company]').change(function(){
                var value = $(this).val();
                if( value != 'create' ){
                    $(".company-fields").show();
                }
                else {
                    $(".company-fields").hide();
                }
            });
        });
    </script>
@endsection