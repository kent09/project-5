@extends('layouts.apptools')
@section('title', 'Import Dashboard')
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

    <h1 class="title">Import Dashboard</h1>

    <div class="inner-content panel-body">
        <div class="row" style="margin-bottom:30px;">
            <div class="col-md-12 text-right">
                <form name="newimport" id="newimport" method="POST" action="{{ url('/csvimport/new-import') }}">
                    {{ csrf_field() }}
                    <button type="submit" class="btn btn-primary">New Import</button>
                </form>
            </div>
        </div>
        <table class="table table-bordered">
            <thead>
            <tr>
                <th>Title</th>
                <th>INFS Account</th>
                <th>Status</th>
                <th>Date/Time</th>
                <th>Details</th>
                <th>Action</th>
            </tr>
            </thead>
            <tbody>
            @if( $imports )
                @foreach( $imports as $import )
                    <tr>
                        <td>{{ $import->import_title }}</td>
                        <td>{{ $import->infsAccount->name ?? 'N/A' }}</td>
                        <td>{{ $import->_status_str }}</td>
                        <td>{{ Carbon\Carbon::parse($import->created_at)->format('d-m-Y H:i') }}</td>
                        <td>
                            <div>
                                <div>Created Contacts: {{$import->createdContacts()->count()}}</div>
                                <div>Updated Contacts: {{$import->matchedContacts()->count()}}</div>
                                <div>Created Companies: {{$import->createdCompanies()->count()}}</div>
                                <div>Updated Companies: {{$import->matchedCompanies()->count()}}</div>
                            </div>
                        </td>
                            <td>

                        @if( $import->status == 0 )
                                <a href="{{ url('csvimport/start/') }}/{{ $import->id }}" >Edit</a>
                                | <a href="javascript:void(0)" data-id="{{ $import->id }}" class="delete-import">Delete</a>
                        @endif
                            </td>
                        @if( $import->status > 1)
{{--                            <td>
                                <a href="javascript:void(0)" data-id="{{ $import->id }}" class="delete-import">Delete</a>
                                <a href="javascript:void(0)" data-id="{{ $import->id }}" class="cancel-import">Cancel</a>
                            </td> --}}
                        @endif
                    </tr>
                @endforeach
            @endif
            </tbody>
        </table>

        {{ $imports->links() }}
    </div>

    <style>
        .panel-body {
            padding-top: 0px !important;
            margin-top:-9px;
        }
    </style>

@endsection
@section('script')
    <script>
        $(document).ready(function(){
            $(document).on('click','.cancel-import',function(e) {
                e.preventDefault();
                var thisObj = $(this);
                var id = thisObj.data('id');
                swal({
                    title: "Are you sure?",
                    text: "You want to cancel this import?",
                    icon: "warning",
                    buttons: true,
                    dangerMode: true,
                })
                    .then((willDelete) => {
                        if (willDelete) {
                            $.ajax({
                                'type': 'GET',
                                'url' : '/csvimport/cancel/'+id,
                                success: function(response){
                                    // var data = JSON.parse(response);
                                    if( response.status == 'success' ) {
                                        toastr.options = {
                                            positionClass: 'toast-top-center'
                                        };
                                        toastr.success("", response.message);
                                        location.reload();
                                    }
                                    else {
                                        toastr.options = {
                                            positionClass: 'toast-top-center'
                                        };
                                        toastr.warning("", response.message);
                                    }
                                    thisObj.prop('disabled',false);
                                }
                            });
                        } else {
                            thisObj.prop('disabled',false);
                        }
                    });
            });

            $(document).on('click','.delete-import',function(e) {
                e.preventDefault();
                var thisObj = $(this);
                var id = thisObj.data('id');
                swal({
                    title: "Are you sure?",
                    text: "Once deleted, all data will be deleted relevant to this import.",
                    icon: "warning",
                    buttons: true,
                    dangerMode: true,
                })
                    .then((willDelete) => {
                        if (willDelete) {
                            $.ajax({
                                'type': 'GET',
                                'url' : '/csvimport/delete/'+id,
                                success: function(response){
                                    // var data = JSON.parse(response);
                                    if( response.status == 'success' ) {
                                        toastr.options = {
                                            positionClass: 'toast-top-center'
                                        };
                                        toastr.success("", response.message);
                                        location.reload();
                                    }
                                    else {
                                        toastr.options = {
                                            positionClass: 'toast-top-center'
                                        };
                                        toastr.warning("", response.message);
                                    }
                                    thisObj.prop('disabled',false);
                                }
                            });
                        } else {
                            thisObj.prop('disabled',false);
                        }
                    });
            });
        });

    </script>
@endsection

