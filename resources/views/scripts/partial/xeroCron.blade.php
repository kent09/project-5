<div class="row martop xlshide">
	<div class="col-lg-12">
		<p><strong>How should we match your Infusionsoft and Xero contacts?</strong></p>
	</div>
</div>
<br/>
<div class="row xlshide">
    <div class="col-lg-1">
		Status
	</div>
	<div class="col-lg-4">
		<div class="form-inline">
			<select name="status" class="status form-control">
				<option value="1">Active</option>
				<option value="0">Deactive</option>
			</select>
		</div>   
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
				<!--    <option value="{{ $fieldname }}" @if( $fieldname == '_XeroID' ) selected @endif>{{ $fieldname }}</option>-->
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
			<select name="contact" class="contact form-control fullwidthsel">
				@foreach( $contactFields as $fieldname => $type )
				    @php if( $fieldname == '_XeroID' ) continue @endphp
				    <option value="{{ $fieldname }}" @php if( isset($xero_settings["company"]) && $xero_settings["company"] == $fieldname ) echo "selected"; @endphp >{{ $fieldname }}</option>
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
				<option value="DRAFT" @php if( isset($xero_settings["invoice_status"]) && $xero_settings["invoice_status"] == 'DRAFT' ) echo "selected"; @endphp >DRAFT</option>
				<option value="SUBMITTED" @php if( isset($xero_settings["invoice_status"]) && $xero_settings["invoice_status"] == 'SUBMITTED' ) echo "selected"; @endphp >SUBMITTED</option>
				<option value="AUTHORISED" @php if( isset($xero_settings["invoice_status"]) && $xero_settings["invoice_status"] == 'AUTHORISED' ) echo "selected"; @endphp >AUTHORISED</option>
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
    				    <option value="{{ $account['account_id'] }}" @php if( isset($xero_settings["sale_account"]) && $xero_settings["sale_account"] == $account['account_id'] ) echo "selected"; @endphp >{{ $account['account_name'] }}</option>
    				@endforeach
				@endif
			</select> 
		</div>   
	</div>
</div>
<br/>
<div class="row xlshide">
	<div class="col-lg-2">
		Default Tax Status:
	</div>
	<div class="col-lg-3">
		<div class="form-inline">
			<select name="tax_status" class="tax_status form-control fullwidthsel">
				<option value="0" @php if( isset($xero_settings["tax_status"]) && $xero_settings["tax_status"] == '0' ) echo "selected"; @endphp >Tax Included</option>
				<option value="1" @php if( isset($xero_settings["tax_status"]) && $xero_settings["tax_status"] == '1' ) echo "selected"; @endphp >Add Tax</option>
				<option value="2" @php if( isset($xero_settings["tax_status"]) && $xero_settings["tax_status"] == '2' ) echo "selected"; @endphp >No Tax</option>
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
