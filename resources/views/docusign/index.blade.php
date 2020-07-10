@extends('layouts.appdocs')
@section('content')
    <h1 class="title">Docusign Account Auth</h1>
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li style="text-align: center">{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    
    <div class="inner-content panel-body">
		<div class="row">
            <div class="col-lg-12 text-center">
                <div class="formholder nopadside">
                    <div class="marleft">
                        @if( $dcusign_connect && $dcusign_connect->expires_date < \Carbon\Carbon::now() )
                            <a id="connectPandaDocs" href="{{url('/connect/docusign/reauth')}}" >
                                <h2 class="syncaccount" >
                                    Reauth Your
                                    <strong>
                                        Docusign Account
                                    </strong>
                                    <br/>
                                    <div style="">
                                        <span style="font-size: 11px;" class="connect-account">
                                            click here to reauth
                                        </span>
                                    </div>  
                                </h2>
                            </a>
                        @elseif( $dcusign_connect && $dcusign_connect->expires_date > \Carbon\Carbon::now() )
                            <h2 style="color:#337ab7;">Your docusign account is authenticated.</h2>
                        @else
                            <a id="connectPandaDocs" href="{{url('/connect/docusign')}}" >
                                <h2 class="syncaccount" >
                                    Connect Your
                                    <strong>
                                        Docusign Account
                                    </strong>
                                    <br/>
                                    <div style="">
                                        <span style="font-size: 11px;" class="connect-account">
                                            click here to connect
                                        </span>
                                    </div>  
                                </h2>
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection