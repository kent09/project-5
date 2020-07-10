<div class="row">
    <input type="hidden" name="onwer_id" value="{{ $ownerGroups->id }}">
	<div class="col-lg-5">
		<p><strong>New Postcode / Radius Based Owner Group</strong></p> 
		<select name="infscontact" class="infuscontact form-control">
			<option value="">User</option>
			@if( !empty($contacts) )
    			@foreach( $contacts as $contact )
    			    @if( isset($contact['FirstName']) && isset($contact['LastName']) )
    			        <option value="{{ $contact['Id'] }}" @if( isset($ownerGroups) && $ownerGroups->infs_person_id == $contact['Id'] ) selected @endif > {{ $contact['FirstName'] }} {{ $contact['LastName'] }}</option>
    			    @endif
    			@endforeach
			@endif
		</select>
	</div>
</div>

@if( isset($ownerGroups) )
    <div class="row martop group-list">
    	<div class="col-lg-7">
    		<p><strong>Area Groups</strong></p> 
    		<table border="0" cellspacing="0" cellpadding="10" class="infotable" style="margin:0;">
    			<tr>
    				<td width="94" align="left" valign="middle" bgcolor="#eeeeee"><strong>Country</strong></td>
    				<td align="left" valign="middle" bgcolor="#eeeeee"><strong>Saved Radius/Postcodes</strong></td>
    				<td width="109" align="left" valign="middle" bgcolor="#eeeeee"><strong>Action</strong></td>
    			</tr>
    			@foreach( $ownerGroups->group as $group )
    			    @if( $group->status == 0 ) @php continue; @endphp  @endif
        			<tr style="border-bottom:solid 2px #eeeeee;">
        				<td align="left" valign="top" bgcolor="#f8f8f8">{{ \CommanHelper::getCountry($group->postc_country_code) }}</td>
        				<td align="left" valign="top" bgcolor="#f8f8f8">
        				    @if( $group->match_type == 1 )
        				        {{ \CommanHelper::getCountry($group->postc_country_code) }} +{{ $group->postc_radius }}
        				    @else
        				        {{ $group->postc_list }}
        				    @endif
        				</td>
        				<td align="left" valign="top" bgcolor="#f8f8f8"><a class="edit-owner-group" data-id="{{ $group->id }}" href="javascript:void(0);">Edit</a> | <a class="delete-owner-group" data-id="{{ $group->id }}" href="javascript:void(0);">Delete</a></td>
        			</tr>
                @endforeach
    			<tr>
    				<td colspan="4" align="right" valign="top" ><button class="btn btn-primary btnaddnewareadrp">Add New</button></td>
    			</tr>  
    
    		</table>   	
    	</div>
    </div>
@endif
<div class="groupdetails" style="display:none;">
    <input type="hidden" name="onwer_group_id" value="">
    <div class="row ">
    	<div class="col-lg-12">
    		<p><strong>New Area Group</strong></p>
    	</div>
    </div>
    <div class="row marbottom">
        <div class="col-lg-5">
            <select name="country" class="country form-control">
            	<option value="">Country</option>
            	@foreach( $countries as $country )
            	    <option value="{{ $country->country_code }}">{{ $country->country_name }}</option>
            	@endforeach
            </select>
        </div>
    </div>
    <div class='clearfix'></div>
    <div class="row">
    	<div class="col-lg-3">
    		<input name="areagroup" type="radio" value="radius_around_postcode" id="radiuspostcode" class="radiuspostcoderap"/> <label for="radiuspostcode"> Radius Around Postcode</label>
    	</div>
    </div>
    <div class="radius-around">
        <div class="row">
        	<br/> 
        	<div class="col-lg-2">
        		<input name="postcode" type="text" placeholder="Postcode" class="postcode form-control" />
        	</div>
        	<div class="col-lg-2 suburb-code">
        	</div>            
        </div>
        
        <div class="row">
        	<br/>
        	<div class="col-lg-2 ">
        		<input name="kmvalue" type="text" placeholder="100" class="kmvalue form-control" />
        	</div>
        	<div class="col-lg-2">
        		<div class="form-inline">
        			<select name="unitvaoue" class="form-control unitvaoue">
        				<option value="KM">KM</option>
        				<option value="MI">MI</option>
        			</select>
        		</div>  
        	</div>
        </div>
        <div class="col-lg-7 text-left">
    	    <button class="btn btn-primary" id="radiusMap" style="margin:20px 0px;"><i class="fa"></i> View Postcode List &amp; Radius On Map</button>
    	</div>
    </div>
    
    <div class="row" >
    	<br/>
    	<div class="col-lg-3">
    		<input name="areagroup" type="radio" value="postcode_list" id="postcodelist" class="postcodelistpl"/> <label for="postcodelist"> Postcode List</label>
    	</div>
    </div>
    
    <div class="row postcodelist" style="display:none;">
    	<div class="col-lg-7">
    		<textarea name="areagrouplist" cols="" rows="" class="areagrouptext"></textarea>
    	</div>
    	<div class="col-lg-4 smalltext">
    		<p>comma-delimited list, but can include ranges and wildcards.</p>
    
    		<p>IE. 3136, 3140, 3150-3160, 317*</p>
    	</div>
    </div>
</div>
<br/>
<div class="row ">
	<div class="col-lg-7 text-right">
		<button class="btn btn-primary" id="update-group"><i class="fa"></i> Update Group</button>
	</div>

</div>