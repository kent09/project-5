@extends('layouts.appdocs')
@section('title', 'Pandadoc Account Auth')
@section('content')
    <h1 class="title">Pandadoc Account Auth</h1>
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
                        @if(!$pandadoc_connect)
                        <a id="connectPandaDocs" href="{{route('/connect/pandadocs')}}" >
                            <h2 class="{{$pandadoc_connect ? 'synedAccount' : 'syncaccount'}}" >
                                Connect Your
                                <strong>
                                    PandaDoc Account
                                </strong>
                                <br/>
                                <div style="">
                                    <span style="font-size: 11px;" class="connect-account">
                                        click here to connect
                                    </span>
                                </div>  
                            </h2>
                        </a>
                        @else
                            <h2 style="color:#337ab7;">Your pandadoc account is authenticated.</h2>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    
@endsection