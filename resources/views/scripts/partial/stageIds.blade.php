<tr>
    <td width="250" align="left" valign="middle" bgcolor="#eeeeee"><strong>Stage Name</strong></td>
    <td align="left" valign="middle" bgcolor="#eeeeee"><strong>stageid</strong></td>
</tr>
@if( $stages )
    @foreach( $stages as $stage )
        <tr style="border-bottom:solid 2px #eeeeee;" class="stage-row">
            <td height="45" align="left" valign="top" bgcolor="#f8f8f8">{{ $stage['StageName'] }}</td>
            <td align="left" valign="top" bgcolor="#f8f8f8">{{ $stage['Id'] }}</td>
        </tr>
    @endforeach
@endif