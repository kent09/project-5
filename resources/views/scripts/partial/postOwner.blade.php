<tr>
	<td width="119" align="left" valign="middle" bgcolor="#eeeeee"><strong>User</strong></td>
	<td align="left" valign="middle" bgcolor="#eeeeee"><strong>Saved Radius/Postcodes</strong></td>
	<td width="109" align="left" valign="middle" bgcolor="#eeeeee"><strong>Action</strong></td>
</tr>
@if( $postOwner )
    @foreach( $postOwner as $owner )
        <tr style="border-bottom:solid 2px #eeeeee;" class="tags-row">
        	<td align="left" valign="top" bgcolor="#f8f8f8">{{ $owner->owner_name }}</td>

        	<td align="left" valign="top" bgcolor="#f8f8f8">
        	   @foreach($owner->group as $group)
        	        @if( !empty($group->match_type) && !empty($group->postc_country_code) )
        	            @if( $group->match_type == 1 )
        	                {{ $group->postc_country_code }}: {{ $group->postc_code }} + {{ $group->postc_radius }} {{ $group->postc_units }}
        	            @endif
        	            @if( $group->match_type == 2 )
        	                {{ $group->postc_country_code }}: {{ $group->postc_list }}
        	            @endif
        	       </br>
        	        @endif
        	        
        	    @endforeach
        	    
        	</td>
        	<td align="left" valign="top" bgcolor="#f8f8f8"><a href="javascript:void(0);" data-id="{{ $owner->id }}" class="edit-owner">Edit</a> | <a href="javascript:void(0);" class="delete-owner" data-id="{{ $owner->id }}">Delete</a></td>
        </tr>
    @endforeach
@endif
<tr>
    <td colspan="2" align="right" valign="top" ><button class="btn btn-primary reassignpostcode" data-reassign_acc_id="{{ $infs_account_id }}">Reassign All New Owners Based On These Rules</button>	</td>
	<td colspan="4" align="right" valign="top" ><button class="btn btn-primary addnewpostinfo"><i class="fa"></i> Add New</button></td>
</tr>