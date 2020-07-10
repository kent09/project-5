@extends('layouts.apptools')
@section('title', 'Import CSV')
@section('content')
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
<div class="">
  @php
    $csv_import = '';
    $is_fields = '';
    if ( Session::has('CSV_import') && isset(Session::get('CSV_import')['fields_arr']) ) {
      $csv_import = Session::get('CSV_import')['fields_arr'];
      $is_fields = Session::get('CSV_import');
    }
  @endphp
  <h1 class="title">Import CSV</h1>
  <div class="row">
    <div class="col-md-12">
      <div class="panel panel-default panel-import">
        <div class="inner-content panel-body">
          <input type="hidden" id="counter-order" value="{{count($is_fields['csv_fields'])}}" />
          <input type="hidden" id="counter-company" value="{{count($is_fields['csv_fields'])}}" />
          <input type="hidden" id="counter-contact" value="{{count($is_fields['csv_fields'])}}" />
          <input type="hidden" id="counter-product" value="{{count($is_fields['csv_fields'])}}" />
          <h4><span>Step 3</span></h4>
          <p class="note">CSV Import Settings</p>
          <br/>
          <form name="step3" method="post" action="{{ url('/csvimport/step4') }}" id="myForm">
            {{ csrf_field() }}
            <div class="form-group col-md-6" style="min-height:120px;">
              <label>Select Your Contact Options</label>
              <div class="radio">
                <label>
                <input type="radio" name="filter_contact" value="both" checked>
                Create & Update customers
                </label>
              </div>
              <div class="radio">
                <label>
                <input type="radio" 
                name="filter_contact" 
                value="create" 
                @if( 
                  isset($is_fields->settings) &&
                  isset($is_fields->settings->contacts) &&
                  isset($is_fields->settings->contacts->type) &&
                  $is_fields->settings->contacts->type == 'create' ) 
                checked @endif>
                Create customers only
                </label>
              </div>
              <div class="radio">
                <label>
                <input 
                  type="radio" 
                  name="filter_contact" 
                  value="update" 
                  @if( 
                    isset($is_fields->settings) &&
                    isset($is_fields->settings->contacts) &&
                    isset($is_fields->settings->contacts->type) &&
                    $is_fields->settings->contacts->type == 'update' ) 
                  checked @endif>
                Update Customers only
                </label>
              </div>

              <div class="checkbox">
                <label><input type="checkbox" id="email_opt_in" name="email_opt_in">Email Opt-in</label>
                <br>
                <div class="content-opt-in-reason">
                  <label>Reason:</label>
                  <label><input type="text" name="email_opt_in_reason"></label>
                </div>
              </div>

            </div>
            <div 
              class="form-group col-md-6 contact-fields" 
              style="min-height:120px; 
              @if( 
                isset($is_fields->settings) &&
                isset($is_fields->settings->contacts) &&
                isset($is_fields->settings->contacts->type) &&
                $is_fields->settings->contacts->type == 'update' ) 
              checked @endif>
              <label>How Should We Match Contacts</label>
              <div class="contact-matching">
                @if(isset($is_fields->settings) &&
                isset($is_fields->settings->contacts) &&
                isset($is_fields->settings->contacts->fields))
                @php $i = 0; @endphp
                @foreach( $is_fields->settings->contacts->fields as $key => $value )
                <div class="form-group">
                  <label>
                  @if($i == 0 ) First @else Then @endif Try :
                  </label>
                  <div class="contact-dropdowns">
                    <select name="match_contact_csv[]">
                      @if( isset($is_fields['csv_fields']) )
                      @foreach( $is_fields['csv_fields'] as $field )
                      <option value="{{ $field }}" @if( $key == $field ) selected @endif>{{ $field }}</option>
                      @endforeach
                      @else
                      <option></option>
                      @endif
                    </select>
                    and
                    <select name="match_contact_infs[]">
                      @if( isset($is_fields['IS_fields']) )
                      @foreach( $is_fields['IS_fields'] as $is_field => $data_type )
                      <option value='{{ $is_field }}' class="fields" @if( $value == $is_field ) selected @endif> {{ $is_field }}</option>
                      @endforeach
                      @else
                      <option></option>
                      @endif
                    </select>
                  </div>
                  <i class="fa fa-plus add-field-match" data-name="contact"></i>
                  @if($i != 0 )
                  <i data-name="contact" class="fa fa-minus remove-field-match"></i>
                  @endif
                </div>
                @php $i++; @endphp
                @endforeach
                @else
                <div class="form-group">
                  <label>
                  First Try :
                  </label>
                  <div class="contact-dropdowns">
                    <select name="match_contact_csv[]">
                      @if( isset($is_fields['csv_fields']) )
                      @foreach( $is_fields['csv_fields'] as $field )
                      <option value="{{ $field }}">{{ $field }}</option>
                      @endforeach
                      @else
                      <option></option>
                      @endif
                    </select>
                    and
                    <select name="match_contact_infs[]">
                      @if( isset($is_fields['IS_fields']) )
                      @foreach( $is_fields['IS_fields'] as $is_field => $data_type )
                      <option value='{{ $is_field }}' class="fields"> {{ $is_field }}</option>
                      @endforeach
                      @else
                      <option></option>
                      @endif
                    </select>
                  </div>
                  <i class="fa fa-plus add-field-match" data-name="contact"></i>
                </div>
                @endif
              </div>
              <small>Then create a contact.</small>
            </div>
            <div class="clearfix"></div>
            <hr>

            @if( isset($csv_import['company']) && !empty($csv_import['company']) )
            <div class="form-group col-md-6" style="min-height:120px;">
              <label>Select Your Company Options</label>
              <div class="radio">
                <label>
                <input type="radio" name="filter_company" value="both" checked>
                Create & Update Companies
                </label>
              </div>
              <div class="radio">
                <label>
                <input type="radio" name="filter_company" value="create" @if( isset($is_fields['settings']['company']['type']) && $is_fields['settings']['company']['type'] == 'create' ) checked @endif>
                Create Companies only
                </label>
              </div>
              <div class="radio">
                <label>
                <input type="radio" name="filter_company" value="update" @if( isset($is_fields['settings']['company']['type']) && $is_fields['settings']['company']['type'] == 'update' ) checked @endif>
                Update Companies only
                </label>
              </div>
            </div>
            <div class="form-group col-md-6  company-fields  {{ isset($is_fields['settings']['company']['type']) && $is_fields['settings']['company']['type'] == 'create' ? 'hide' : '' }}" style="min-height:120px;">
              <label>How Should We Match Companies</label>
              <div class="company-matching">
                @if( isset($is_fields['settings']['company']['fields']) )
                @php $i = 0; @endphp
                @foreach( $is_fields['settings']['company']['fields'] as $key => $value )
                <div class="form-group">
                  <label>
                  @if($i == 0 ) First @else Then @endif Try :
                  </label>
                  <div class="company-dropdowns">
                    <select name="match_company_csv[]">
                      @if( isset($is_fields['csv_fields']) )
                      @foreach( $is_fields['csv_fields'] as $field )
                      <option value="{{ $field }}" @if( $key == $field ) selected @endif>{{ $field }}</option>
                      @endforeach
                      @else
                      <option></option>
                      @endif
                    </select>
                    and
                    <select name="match_company_infs[]">
                      @if( config('infusionsoft.companyFields') > 0 )
                      @foreach( config('infusionsoft.companyFields') as $is_field => $data_type )
                      <option value="{{ $is_field }}" @if( $value == $is_field ) selected @endif>{{ $is_field }}</option>
                      @endforeach
                      @else
                      <option></option>
                      @endif
                    </select>
                  </div>
                  <i class="fa fa-plus add-field-match" data-name="company"></i>
                  @if($i != 0 )
                  <i data-name="contact" class="fa fa-minus remove-field-match"></i>
                  @endif
                </div>
                @php $i++; @endphp
                @endforeach
                @else
                <div class="form-group">
                  <label>
                  First Try :
                  </label>
                  <div class="company-dropdowns">
                    <select name="match_company_csv[]">
                      @if( isset($is_fields['csv_fields']) )
                      @foreach( $is_fields['csv_fields'] as $field )
                      <option value="{{ $field }}">{{ $field }}</option>
                      @endforeach
                      @else
                      <option></option>
                      @endif
                    </select>
                    and
                    <select name="match_company_infs[]">
                      @if( config('infusionsoft.companyFields') > 0 )
                      @foreach( config('infusionsoft.companyFields') as $is_field => $data_type )
                      <option value="{{ $is_field }}">{{ $is_field }}</option>
                      @endforeach
                      @else
                      <option></option>
                      @endif
                    </select>
                  </div>
                  <i class="fa fa-plus add-field-match" data-name="company"></i>
                </div>
                @endif
              </div>
              <small>Then create a company.</small>
            </div>
            <div class="clearfix"></div>
            <hr>
            @endif
            @if( isset($csv_import['orders']) && !empty($csv_import['orders']) )
            <div class="form-group col-md-6" style="min-height:120px;">
              <label>Select Your Order Options</label>
              <div class="radio">
                <label>
                <input type="radio" name="filter_order" value="both" checked>
                Create & Update Orders
                </label>
              </div>
              <div class="radio">
                <label>
                <input type="radio" name="filter_order" value="create" @if( isset($is_fields['settings']['orders']['type']) && $is_fields['settings']['orders']['type'] == 'create' ) checked @endif>
                Create orders only
                </label>
              </div>
              <div class="radio">
                <label>
                <input type="radio" name="filter_order" value="update"  @if( isset($is_fields['settings']['orders']['type']) && $is_fields['settings']['orders']['type'] == 'update' ) checked @endif>
                Update orders only
                </label>
              </div>
              <label>How are your order items broken up in this file?</label>
              <div class="radio">
                <label>
                <input type="radio" name="order_split" value="custom" @if( isset($is_fields['settings']['orders']['settings']['splitType']) && $is_fields['settings']['orders']['settings']['splitType'] == 'custom' ) checked @endif> <strong>Each Line Is A New Order -</strong> Products on each order are delimited in single field IE. A field has Product 1, Product 2, Product 3 
                </label>
              </div>
              <div class="clearfix"></div>
              <div class="order_split_custom {{ isset($is_fields['settings']['orders']['settings']['splitType']) && $is_fields['settings']['orders']['settings']['splitType'] == 'custom' ? 'hide' : ''}} "  >
                <div class="mb30">
                  <strong>SKU Delimeter:</strong> <input type="text" name="sku_delimiter" value="@if( isset($is_fields['settings']['orders']['settings']['split']['sku']) ) {{ $is_fields['settings']['orders']['settings']['split']['sku'] }} @endif">
                </div>
                <div class="mb30">
                  <strong>Product Name Delimeter:</strong> <input type="text" name="name_delimiter" value="@if( isset($is_fields['settings']['orders']['settings']['split']['name']) ) {{ $is_fields['settings']['orders']['settings']['split']['name'] }} @endif"></br>EG. Symbole like "/", "," or "|" are common.
                </div>
                <div class="mb30">
                  <strong>Qty Delimiter:</strong> <input type="text" name="qty_delimiter" value="@if( isset($is_fields['settings']['orders']['settings']['split']['qty']) ) {{ $is_fields['settings']['orders']['settings']['split']['qty'] }} @endif">
                </div>
                <div class="mb30">
                  <strong>Price Delimiter:</strong> <input type="text" name="price_delimiter" value="@if( isset($is_fields['settings']['orders']['settings']['split']['price']) ) {{ $is_fields['settings']['orders']['settings']['split']['price'] }} @endif">
                </div>
              </div>
              <div class="radio">
                <label>
                <input type="radio" name="order_split" value="line" @if( isset($is_fields['settings']['orders']['settings']['splitType']) && $is_fields['settings']['orders']['settings']['splitType'] == 'line' ) checked @endif> <strong>Each Line Is An Item On An Order</strong> - and there is an order ID that can be used to know if two lines belong on the same order 
                </label>
              </div>
              <div class="order_split_line col-md-10" @if( isset($is_fields['settings']['orders']['settings']['splitType']) && $is_fields['settings']['orders']['settings']['splitType'] == 'line' ) @else style="display:none;" @endif>
              What is the order id field that links line from the same order : 
              <select name="order_id">
                @if( isset($is_fields['csv_fields']) )
                @foreach( $is_fields['csv_fields'] as $field )
                <option value="{{ $field }}" @if( isset($is_fields['settings']['orders']['settings']['order_id']) && $is_fields['settings']['orders']['settings']['order_id'] == $field ) selected @endif >{{ $field }}</option>
                @endforeach
                @else
                <option></option>
                @endif
              </select>
            </div>
          </div>
          <div class="form-group col-md-6" style="min-height:120px;">
            <div class="order-fields" style="@if( isset($is_fields['settings']['orders']['type']) && $is_fields['settings']['orders']['type'] == 'create' )display:none; @endif ">
              <label>How Should We Match Orders</label>
              <div class="order-matching">
                @if( isset($is_fields['settings']['orders']['fields']) )
                @php $i = 0; @endphp
                @foreach( $is_fields['settings']['orders']['fields'] as $key => $value )
                <div class="form-group">
                  <label>
                  @if($i == 0 ) First @else Then @endif Try :
                  </label>
                  <div class="order-dropdowns">
                    <select name="match_order_csv[]">
                      @if( isset($is_fields['csv_fields']) )
                      @foreach( $is_fields['csv_fields'] as $field )
                      <option value="{{ $field }}"  @if( $key == $field ) selected @endif>{{ $field }}</option>
                      @endforeach
                      @else
                      <option></option>   
                      @endif
                    </select>
                  and
                    <select name="match_order_infs[]">
                      @if( isset($is_fields['order_fields']) )
                      @foreach( $is_fields['order_fields'] as $is_field => $data_type )
                      <option value='{{ $is_field }}' class="fields"  @if( $value == $is_field ) selected @endif> {{ $is_field }}</option>
                      @endforeach
                      @else
                      <option></option>   
                      @endif
                    </select>
                  </div>
                  <i class="fa fa-plus add-field-match" data-name="order"></i>
                  @if($i != 0 )
                  <i data-name="contact" class="fa fa-minus remove-field-match"></i>
                  @endif
                </div>
                @php $i++; @endphp
                @endforeach
                @else
                <div class="form-group">
                <label>
                First Try :
                </label>
                <div class="order-dropdowns">
                  <select name="match_order_csv[]">
                    @if( isset($is_fields['csv_fields']) )
                    @foreach( $is_fields['csv_fields'] as $field )
                    <option value="{{ $field }}">{{ $field }}</option>
                    @endforeach
                    @else
                    <option></option>   
                    @endif
                  </select>
                and
                  <select name="match_order_infs[]">
                    @if( isset($is_fields['order_fields']) )
                    @foreach( $is_fields['order_fields'] as $is_field => $data_type )
                    <option value='{{ $is_field }}' class="fields"> {{ $is_field }}</option>
                    @endforeach
                    @else
                    <option></option>   
                    @endif
                  </select>
                </div>
                <i class="fa fa-plus add-field-match" data-name="order"></i>
              </div>
              @endif
            </div>
          </div>
          
          <small>Then create an order.</small>
        </div>

        <div class="clearfix"></div><hr>

        <div class="form-group col-md-6" style="min-height:120px;">
          <label>Select Product Options</label>
          <div class="radio">
            <label>
            <input type="radio" name="filter_product" value="both" checked>
            Find & Create Products
            </label>
          </div>
            
          <div class="radio">
            <label>
              <input type="radio" name="filter_product" value="find" >
              Find Product Only
            </label>
          </div>
          <div class="radio">
            <label>
              <input type="radio" name="filter_product" value="text_based" >
              Use Text Based Product Descriptions (Not recommended)
            </label>
          </div>
        </div>

        <div class="form-group col-md-6 product-fields" style="min-height:120px; @if( isset($is_fields['settings']['products']['type']) && $is_fields['settings']['products']['type'] == 'text_based' )display:none; @endif ">
          <label>How Should We Match Products</label>

        <div class="product-matching">
          @if( isset($is_fields['settings']['products']['fields']) )
          @php $i = 0; @endphp
          @foreach( $is_fields['settings']['products']['fields'] as $fkey => $fvalue )
          <div class="form-group">
            <label>
            @if($i == 0 ) First @else Then @endif Try :
            </label>
            <div class="product-dropdowns">
              <select name="match_product_csv[]">
                @if( isset($is_fields['csv_fields']) )
                @foreach( $is_fields['csv_fields'] as $field )
                <option value="{{ $field }}" @if( $fkey == $field ) selected @endif>{{ $field }}</option>
                @endforeach
                @else
                <option></option>   
                @endif
              </select>
              and
              <select name="match_product_infs[]">
                @if( config('infusionsoft.productInfsFields') > 0 )
                @foreach( config('infusionsoft.productInfsFields') as $key => $value )
                <option value="{{ $key }}" @if( $fvalue == $key ) selected @endif>{{ $key }}</option>
                @endforeach
                @else
                <option></option>
                @endif
              </select>
            </div>
            <i class="fa fa-plus add-field-match" data-name="product"></i>
            @if($i != 0 )
            <i data-name="contact" class="fa fa-minus remove-field-match"></i>
            @endif
          </div>
          @php $i++; @endphp
          @endforeach
          @else
        <div class="form-group">
          <label>
            First Try :
          </label>
          <div class="product-dropdowns">
            <select name="match_product_csv[]">
              @if( isset($is_fields['csv_fields']) )
              @foreach( $is_fields['csv_fields'] as $field )
              <option value="{{ $field }}">{{ $field }}</option>
              @endforeach
              @else
              <option></option>   
              @endif
            </select>
            and
            <select name="match_product_infs[]">
              @if( config('infusionsoft.productInfsFields') > 0 )
              @foreach( config('infusionsoft.productInfsFields') as $key => $value )
              <option value="{{ $key }}" @if( $key == 'Sku' ) selected @endif>{{ $key }}</option>
              @endforeach
              @else
              <option></option>
            @endif
            </select>
          </div>
          <i class="fa fa-plus add-field-match" data-name="product"></i>
        </div>

        @endif
        </div>
        <small>Then create a product.</small>
        </div>
        @endif
        <div class="form-group col-md-12">
          <a class="btn btn-danger pull-left btn_cls"  href="{{ url('/csvimport/step2') }}"><i class="fa fa-arrow-left"></i> Back</a>
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
  function contactDropDown(field,dropdowns){
  	var html = '<div class="form-group"><label>Then Try : </label>'+dropdowns+'<i class="fa fa-plus add-field-match" data-name="'+field+'"></i> <i data-name="'+field+'" class="fa fa-minus remove-field-match"></i></div>';
    return html;
  }
  
  function orderDropDown(field,dropdowns){
    var html = '<div class="form-group"><label>Then Try : </label>'+dropdowns+'<i class="fa fa-plus add-field-match" data-name="'+field+'"></i> <i data-name="'+field+'" class="fa fa-minus remove-field-match"></i></div>';
    return html;
  }
  
  $(document).ready(function(){
  	var counter = {
      'order' : document.getElementById('counter-order').value,
      'company' : document.getElementById('counter-company').value,
      'contact' : document.getElementById('counter-contact').value,
      'product' : document.getElementById('counter-product').value,
      'order_counter' : 0,
      'company_counter' : 0,
      'contact_counter' : 0,
      'product_counter' : 0,
    };

    $(".content-opt-in-reason").hide();
  
    $(document).on("click", ".add-field-match", function(){
      var thisObj = $(this);
      var field = thisObj.data('name');
        
      counter_field = counter[field + "_counter"];
      if (counter[field] > counter_field) {
      $dropdown_class = "." + field + "-dropdowns";
      
      var dropdowns = $($dropdown_class).html();
      var html = contactDropDown(field,dropdowns);
      
      thisObj.closest('.'+field+'-matching').append(html);
      
      counter[field + "_counter"] += 1;
      console.log(counter[field + "_counter"]);
      }
    });
    
    $(document).on("click", ".remove-field-match", function(){
      var thisObj = $(this);
      var field = thisObj.data('name');

      thisObj.parent('.form-group').remove();

      if (counter[field + "_counter"] > 0) {
        counter[field + "_counter"] -= 1;
        console.log(counter[field + "_counter"])
      }
    });

    $('input[name=email_opt_in]').change(function(){
      var value = $(this).val();

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
    $('input[name=filter_order]').change(function(){
      var value = $(this).val();
      if( value != 'create' ){
        $(".order-fields").show();
      }
      else {
        $(".order-fields").hide();
      }	
    });
    $('input[name=filter_product]').change(function(){
      var value = $(this).val();
      if( value != 'text_based' ){
        $(".product-fields").show();
      }
      else {
        $(".product-fields").hide();
      }	
    });
    $('input[name=order_split]').click(function(){
      var thisObj = $(this);
      if( thisObj.val() == 'custom' ){
        $(".order_split_custom").show();
        $(".order_split_line").hide();
      }
      else {
        $(".order_split_custom").hide();
        $(".order_split_line").show();
      }
    });
  });
</script>
@endsection