<div class="row martop xlshide">
	<div class="col-lg-12">
		<p><strong>How should we match your Infusionsoft and Xero contacts?</strong></p>
	</div>
</div>
<br/>
<div class="row xlshide">
	<div class="col-lg-1">
		First try:
	</div>
	<div class="col-lg-4">
		<div class="form-inline">
			<select name="xeroid" class="xeroid form-control fullwidthsel" disabled="true">
				<option value="_XeroID">Xero ID</option>
				<!--@foreach( $contactFields as $fieldname => $type )-->
				<!--    <option value="{{ $fieldname }}" @if( $fieldname == 'XeroID' ) selected @endif>{{ $fieldname }}</option>-->
				<!--@endforeach-->
			</select> 

		</div>   
	</div>
	<div class="col-lg-7">(This is a mandatory custom field we created in your Infusionsoft account)</div>
</div>
<br/>
<div class="addNewField">

    @if( count($saleAccounts) > 0 && isset($xero_settings["infs_fields"]) )
    @foreach( $xero_settings["infs_fields"] as $singlefield )
        <div class="row xlshide mainFieldHtml">
        	<div class="col-lg-1">
        		Then try:
        	</div>
        	<div class="col-lg-4">
        		<div class="form-inline">
        			<select name="infs_fields[]" class="form-control fullwidthsel">
        				@foreach( $contactFields as $fieldname => $type )
        				    @php if( $fieldname == '_XeroID' ) continue @endphp
        				    <option value="{{ $fieldname }}" @php if( $singlefield == $fieldname ) echo "selected"; @endphp >{{ $fieldname }}</option>
        				@endforeach
        			</select>
        		</div>   
        	</div>
        	<div class="col-lg-7"><a href="javascript:void(0);" class="plussign">+</a> <a href="javascript:void(0);" class="minussign">-</a></div>
        </div>
    @endforeach
    @else
    <div class="row xlshide mainFieldHtml">
    	<div class="col-lg-1">
    		Then try:
    	</div>
    	<div class="col-lg-4">
    		<div class="form-inline">
    			<select name="infs_fields[]" class="form-control fullwidthsel">
    				@foreach( $contactFields as $fieldname => $type )
    				    @php if( $fieldname == '_XeroID' ) continue @endphp
    				    <option value="{{ $fieldname }}">{{ $fieldname }}</option>
    				@endforeach
    			</select>
    		</div>   
    	</div>
    	<div class="col-lg-7"><a href="javascript:void(0);" class="plussign">+</a> <a href="javascript:void(0);" class="minussign">-</a></div>
    </div>
    @endif

</div>
<br/>
<div class="row xlshide">
	<div class="col-lg-4">
		Then, if not found, create a contact with this name:
	</div>
	<div class="col-lg-3">
		<div class="form-inline">
			<select name="compname" class="compname form-control fullwidthsel">
				@foreach( $contactFields as $fieldname => $type )
				    @php if( $fieldname == '_XeroID' ) continue @endphp

				    @php
						$selected = '';
						if(isset($xero_settings) || count((array) $xero_settings) > 0){
							$company_setting = isset($xero_settings['company']) ?: '';
							if($company_setting == $fieldname){
								$selected = 'selected';
							}
						}
					@endphp

				    <option value="{{ $fieldname }}" {{$selected}} >{{ $fieldname }}</option>
				@endforeach
			</select> 
		</div>   
	</div>
</div>
<br/>
<div class="row xlshide">
	<div class="col-lg-2">
		Default Invoice Status:
	</div>
	<div class="col-lg-3">
		<div class="form-inline">
			<select name="invoice_status" class="invoice_status form-control fullwidthsel">
				@php
					$selected = '';
					if(isset($xero_settings) || count((array) $xero_settings) > 0){
						$invoice_status = isset($xero_settings['invoice_status']) ?: '';
						switch($invoice_status){
							case 'DRAFT':
								$selected = 'selected';
								break;
							case 'SUBMITTED':
								$selected = 'selected';
								break;
							case 'AUTHORISED':
							 	$selected = 'selected';
								break;
						}
					}
				@endphp
				<option value="DRAFT" {{$selected}} >DRAFT</option>
				<option value="SUBMITTED" {{$selected}} >SUBMITTED</option>
				<option value="AUTHORISED" {{$selected}}>AUTHORISED</option>
			</select>
		</div>   
	</div>
</div>
<br/>
<div class="row xlshide">
	<div class="col-lg-2">
		Order Sales Account:
	</div>
	<div class="col-lg-3">
		<div class="form-inline">
			<select name="sale_account" class="sale_account form-control fullwidthsel">
				@if( count($saleAccounts) > 0 )
    				@foreach( $saleAccounts as $account )

    					@php
							$selected = '';
							if(isset($xero_settings) || count((array) $xero_settings) > 0){
								$account_id = isset($xero_settings['account_id']) ?: '';
								$sale_account = isset($xero_settings['sale_account']) ?: '';
								if($account_id == $sale_account){
									$selected = 'selected';
								}
							}
						@endphp
    				    <option value="{{ $account['account_id'] }}" {{$selected}} >{{ $account['account_name'] }}</option>
    				@endforeach
				@endif
			</select> 
		</div>   
	</div>
</div>
<br/>
<div class="row xlshide">
	<div class="col-lg-5 text-right">
		<button type="button" id="save-new-group" class="btn btn-primary"><i class="fa"></i> Save New Group</button>
	</div>
</div>
