<tr>
    <td align="left" valign="middle" bgcolor="#eeeeee"><strong>Saved Radius</strong></td>
    <td width="98" align="left" valign="middle" bgcolor="#eeeeee"><strong>Tag ID</strong></td>
    <td width="98" align="left" valign="middle" bgcolor="#eeeeee"><strong>Count</strong></td>
    <td width="98" align="left" valign="middle" bgcolor="#eeeeee"><strong>Status</strong></td>
    <td width="98" align="left" valign="middle" bgcolor="#eeeeee"><strong>Last Updated</strong></td>
    <td width="209" align="left" valign="middle" bgcolor="#eeeeee"><strong>Action</strong></td>
</tr>
@if($postTags)
    @foreach( $postTags as $postTag )
        <tr style="border-bottom:solid 2px #eeeeee;" class="tags-row">
            <td align="left" valign="top" bgcolor="#f8f8f8">
                @if( $postTag->postc_type == 1 )
                    {{ $postTag->postc_country_code }}: {{ $postTag->postc_code }} + {{ $postTag->postc_radius }} {{ $postTag->postc_units }}
                @else
                    {{ $postTag->postc_country_code }}: {{ \Carbon\Carbon::parse($postTag->created_at)->format('d-m-Y H:i') }} List
                @endif
            </td>
            <td align="left" valign="top" bgcolor="#f8f8f8">{{ $postTag->tag_id }}</td>
            <td align="left" valign="top" bgcolor="#f8f8f8">{{ $postTag->tag_count }}</td>
            <td align="left" valign="top" bgcolor="#f8f8f8">{{ $status_ar[$postTag->status] }}</td>
            <td align="left" valign="top" bgcolor="#f8f8f8">{{ $postTag->updated_at }}</td>
            <td align="left" valign="top" bgcolor="#f8f8f8">
                <div class="row-td-loader-{{ $postTag->id }}" style="display: none; text-align: center;">
                    <span class="fa fa-spinner fa-spin"></span>
                </div>
                <div class="row-td-option-{{ $postTag->id }}">
                    <a href="javascript:void(0);" class="re-tag" data-id="{{ $postTag->id }}">Re-Tag Now</a> <br/> <a class="delete-tag" href="javascript:void(0);" data-id="{{ $postTag->id }}">Delete Area & Remove Tag</a>
                </div>
            </td>
        </tr>
    @endforeach
@endif