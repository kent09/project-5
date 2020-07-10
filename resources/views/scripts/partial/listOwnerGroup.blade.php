<div class="col-lg-7">
	<p><strong>Area Groups</strong></p> 
	<table border="0" cellspacing="0" cellpadding="10" class="infotable" style="margin:0;">
		<tr>
			<td width="94" align="left" valign="middle" bgcolor="#eeeeee"><strong>Country</strong></td>
			<td align="left" valign="middle" bgcolor="#eeeeee"><strong>Saved Radius/Postcodes</strong></td>
			<td width="109" align="left" valign="middle" bgcolor="#eeeeee"><strong>Action</strong></td>
		</tr>
		@foreach( $postOwnergroup as $group )
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