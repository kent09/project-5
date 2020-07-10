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
        @php
        $csv_import = '';
        if ( Session::has('CSV_import') && isset(Session::get('CSV_import')['fields_arr']) ) {
        $csv_import = Session::get('CSV_import')['fields_arr'];
        }
        @endphp
        @if ( count($file_fields_arr) )
        <div class="form-group">
          <div class="row">
            @if( count($userImports) > 0 )
            <div class="col-md-12">
              <div class="form-group">
                <lable>Use Settings from a previous import</lable>
                <select class="form-control" name="import_account" id="importAccount">
                  <option>Select imported settings</option>
                  @foreach( $userImports as $userImport )
                  <option value="{{ $userImport->id }}">{{ $userImport->import_title }}</option>
                  @endforeach()
                </select>
              </div>
            </div>
            <div class="clearfix"></div>
            <hr/>
            @endif
            <div class="col-md-4 ">
              <b>Your CSV Fields</b>
            </div>
            <div class="col-md-8">
              <b>Infusionsoft Fields</b>
            </div>
          </div>
        </div>
        @php
          if (Session::has('CSV_import')) {
            $CSV_import = Session::get('CSV_import');
            if (isset($CSV_import['fields_arr']) && !empty($CSV_import['fields_arr'])) {
              $fields_arr = $CSV_import['fields_arr'];
              //~ echo "<pre>"; print_r($CSV_import['fields_arr']); die;
            }
          }
        @endphp
        <form name="step2" method="Post" action="{{ url('/csvimport/step3') }}" id="step2">
          {{ csrf_field() }}
          @foreach( $file_fields_arr as $key => $fieldname)
          <div class="form-group">
            <div class="row">
              <div class="col-md-4">
                {{ $fieldname }}
                <input type="hidden"  name="csv_fields[]" value="{{ $fieldname }}"/>
              </div>
              <div class="col-md-8">
                <select class="form-control map-fields" name="infusionsoft_fields[]">
                  <option value='0' class="fields">Skip this Field</option>
                  @if ( count($IS_fields) ) 
                  @foreach( $IS_fields as $is_field => $type )
                  <option 
                  value='{{ $is_field }}' 
                  class="fields" 
                  @if( 
                  isset($csv_import->contacts) && 
                  isset($csv_import->contacts->$fieldname) && 
                  $csv_import->contacts->$fieldname == $is_field ) 
                  selected 
                  @endif> {{ $is_field }}</option>
                  @endforeach
                  @endif
                </select>
              </div>
            </div>
          </div>
          @endforeach
          <div class="form-group row">
            <div class="col-md-12">
              <div class="checkbox">
                <label>
                <input type="checkbox" value="yes" name="company_creation" 
                {{ isset($csv_import->company) ? 'checked' : '' }} > 
                Are We Creating and/or Linking Company Records?
                </label>
              </div>
            </div>
          </div>
          <div class="companyFields" @if( !isset($csv_import->company) ) style="display:none;" @endif >
          @foreach( $file_fields_arr as $key => $fieldname)
          <div class="form-group row">
            <div class="col-md-4">
              {{ $fieldname }}
              <input type="hidden"  name="company_fields[]" value="{{ $fieldname }}"/>
            </div>
            <div class="col-md-8">
              <select class="form-control map-fields" name="comp_infusionsoft_fields[]">
                <option value='0' class="fields">Skip this Field</option>
                @foreach( config('infusionsoft.companyFields') as $is_field => $type )
                <option value='{{ $is_field }}' 
                class="fields" 
                @if( 
                isset($csv_import->company) && 
                isset($csv_import->company->$fieldname) && 
                $csv_import->company->$fieldname == $is_field ) 
                selected 
                @endif> {{ $is_field }}</option>
                @endforeach
              </select>
            </div>
          </div>
          @endforeach
      </div>
      <div class="form-group row">
        <div class="col-md-12">
          <div class="checkbox">
            <label>
              <input type="checkbox" value="yes" name="order_creation" {{ isset($csv_import->orders) ? 'checked' : '' }}>
              Are We Creating Orders?
            </label>
          </div>
        </div>
      </div>
      <div class="orders" @if( !isset($csv_import->orders) ) style="display:none;" @endif>
        <div class="form-group">
          <div class="row">
            <div class="col-md-4">
              SKU
              <input type="hidden"  name="order_fields[]" value="sku"/>
            </div>
            <div class="col-md-8">
              <select class="form-control map-fields" name="order_infusionsoft_fields[]">
                <option value='0' class="fields">Skip this Field</option>
                @if ( count($file_fields_arr) ) 
                  @foreach( $file_fields_arr as $key => $fieldname)
                  <option value='{{ $fieldname }}' 
                  class="fields"
                  @if( 
                    isset($csv_import->orders) && 
                    isset($csv_import->orders->sku) && 
                    $csv_import->orders->sku == $fieldname ) 
                      selected 
                  @endif> {{ $fieldname }}</option>
                  @endforeach
                @endif
              </select>
            </div>
          </div>
        </div>
        <div class="form-group">
          <div class="row">
            <div class="col-md-4">
              Product Name
              <input type="hidden"  name="order_fields[]" value="product_name"/>
            </div>
            <div class="col-md-8">
              <select class="form-control map-fields" name="order_infusionsoft_fields[]">
                <option value='0' class="fields">Skip this Field</option>
                @if ( count($file_fields_arr) ) 
                  @foreach( $file_fields_arr as $key => $fieldname)
                    <option value='{{ $fieldname }}' 
                    class="fields" 
                    @if( 
                    isset($csv_import->orders) && 
                    isset($csv_import->orders->product_name) && 
                    $csv_import->orders->product_name == $fieldname) 
                    selected 
                    @endif> {{ $fieldname }}</option>
                  @endforeach
                @endif
              </select>
            </div>
          </div>
        </div>
        <div class="form-group">
          <div class="row">
            <div class="col-md-4">
              Qty
              <input type="hidden"  name="order_fields[]" value="qty"/>
            </div>
          <div class="col-md-8">
            <select class="form-control map-fields" name="order_infusionsoft_fields[]">
              <option value='0' class="fields">Skip this Field</option>
              @if ( count($file_fields_arr) ) 
                @foreach( $file_fields_arr as $key => $fieldname)
                  <option 
                  value='{{ $fieldname }}' 
                  class="fields" 
                  @if( 
                    isset($csv_import->orders) && 
                    isset($csv_import->order->qty) && 
                    $csv_import->order->qty == $fieldname ) 
                    selected 
                  @endif> {{ $fieldname }}</option>
                @endforeach
              @endif
            </select>
          </div>
        </div>
      </div>
      <div class="form-group">
      <div class="row">
        <div class="col-md-4">
          Price
          <input type="hidden"  name="order_fields[]" value="price"/>
        </div>
        <div class="col-md-8">
          <select class="form-control map-fields" name="order_infusionsoft_fields[]">
            <option value='0' class="fields">Skip this Field</option>
            @if ( count($file_fields_arr) ) 
              @foreach( $file_fields_arr as $key => $fieldname)
              <option 
              value='{{ $fieldname }}' 
              class="fields" 
              @if( 
                isset($csv_import->orders) && 
                isset($csv_import->orders->price) && 
                $csv_import->orders->price == $fieldname ) 
                selected 
              @endif> {{ $fieldname }}</option>
              @endforeach
            @endif
          </select>
        </div>
      </div>
      </div>
    </div>
    <div class="form-group">
    <a class="btn btn-danger pull-left btn_cls"  href="javascript:history.back()"><i class="fa fa-arrow-left"></i> Back</a>
    <button class="btn btn-primary pull-right submit btn_cls" type="button">Select Import Options <i class="fa fa-arrow-right"></i></button>
    </div>
    </form>
    @else 
    <div>
      Uploaded file is empty
    </div>
    @endif
  </div>
</div>
</div>
</div>
@endsection
@section('script')
<script>
  $(document).ready(function(){
  	$(document).on('click','input[name="company_creation"]',function() {
      var thiObj = $(this);
      if( thiObj.is(':checked') ){
        $(".companyFields").slideDown();
      }
      else {
        $(".companyFields").slideUp();
      }
  	});
  	
  	$(document).on('click','input[name="order_creation"]',function() {
      var thiObj = $(this);
      if( thiObj.is(':checked') ){
        $(".orders").slideDown();
      }
      else {
        $(".orders").slideUp();
      }
  	});
  	
  	$(document).off('click','.submit').on('click','.submit',function() {
  		var empty_val = 1;
  		$("#step2").find(".map-fields").each(function() {
  			if($(this).val() != 0){
  				empty_val = 0;
  			}
  		});
  		if(empty_val){
  			$('.error_msg').css('display','block');
  		} else {
  			$('#step2').submit();
  		}
  	});
  
    /* Get all stages */
    $(document).on('change','#importAccount',function(e){
      e.preventDefault();
      var thisObj = $(this);
      var importID = thisObj.val();
      
      thisObj.prop('disabled',true);
      $.ajax({
        'type': 'post',
        'url' : '{{ url("/csvimport/applysettings") }}',
        'data': { 'import_account':importID,'_token':"{{ csrf_token() }}" },
        'dataType':'html',
        success: function(response){
          var data = $.parseJSON(response);
          if( data.status == 'failed' ) {
            $('#allStages .stage-row').remove();
            toastr.options = {
              positionClass: 'toast-top-center'
            };
            toastr.warning("", data.message);
          }
          else {
            toastr.options = {
              positionClass: 'toast-top-center'
            };
            toastr.success("", data.message);
            setTimeout(function(){ location.reload(); }, 2000);
          }
          thisObj.prop('disabled',false);
        }
      }); 
    });
  });
</script>
@endsection