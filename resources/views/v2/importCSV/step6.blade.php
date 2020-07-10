@extends('layouts.apptools')
@section('title', 'Import CSV')
@section('content')
    <h1 class="title">Import CSV</h1>

    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default panel-import">
                <div class="inner-content panel-body text-center">
                    <h4><span>Step 5: Complete</span></h4>

                    <h5 class="sub-title text-center" style="">{{ $message }}</h5>
                    <div class="row">
                        <div class="col-md-5 col-md-offset-1" style="margin-top:20px;">
                            <a class="pull-right btn" href=" {{ url('/csvimport') }}" style="color:black;"><strong>Import another List</strong></a>
                        </div>
                        <div class="col-md-5" style="margin-top:20px; color:black;">
                            <a class="pull-left btn" href="http://{{ $account }}" target="_blank" style="color:black;"><strong>Go to InfusionSoft <i class="fa fa-chevron-right"></i></strong></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>


    </style>

@endsection
