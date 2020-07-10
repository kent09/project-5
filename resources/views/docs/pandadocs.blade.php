@extends('layouts.appdocs')
@section('title', 'Manage pandadocs templates')
@section('content')
<link rel="stylesheet" href="{{ url('assets/css/selectize.css')}}" crossorigin="anonymous">
<link rel="stylesheet" href="{{ url('assets/css/selectize-bootstrap.css')}}" crossorigin="anonymous">
<h1 class="title">
    Manage pandadocs templates
    <a class="pull-right btn btn-default" href="{{ url('docs/pandadocs/setupguide') }}">
        <i class="fa fa-info-circle"> </i> Getting started
    </a>
</h1>
<div class="inner-content panel-body">
    <div class="row">
        <div class="col-lg-5">
			<div class="form-inline">
                <select name="infusaccount" class="infusaccount form-control fullwidthsel" id="infsBtn">
                    <option value="">Select Your Infusion Account</option>
                    @if( count(\Auth::user()->infsAccounts) > 0 )
                        @foreach( \Auth::user()->infsAccounts as $account )
                            <option value="{{ $account->id }}">{{ strstr($account->account,'.',true) }}</option>
                        @endforeach
                    @endif
                </select> 
            </div>    		
		</div>
        <div class="col-md-12 pandaTemplats" style="display:none;">
        
            <h3>Saved Proposal Templates</h3>

            @if ( is_array($templates) && count($templates) > 0 )
                <div class="panel-group" id="accordion">
                    @foreach ( $templates as $id => $name )
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h4 class="panel-title">
                                    <a data-toggle="collapse" data-parent="#accordion" href="#collapse{{ $id }}">
                                        {{ $name }}</a>
                                </h4>
                                <i class="fa fa-plus"></i>
                                <i class="fa fa-minus" style="display: none"></i>
                            </div>
                            <div id="collapse{{ $id }}" class="panel-collapse collapse" data-id="{{ $id }}">
                                <div class="panel-body ajax">
                                    <img src="{{ url('assets/images/loader.gif') }}"/>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>


<div id="createTagModal" class="modal fade" role="dialog">
    <div class="modal-dialog">

        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h2 class="modal-title"><strong>Create A New Tag</strong></h2>
            </div>
            <div class="modal-body">
                <div class="formholder packageholder">
                    <h4 class="modal-title">Enter Tag Name</h4>

                    <input class="form-control bg-color" value="" id="saveTagValue">
                    <br/>
                    <button type="button" id="ordernowsub"  style="    padding: 1px 10px 4px 16px!important; background: #ffba7c 15px -5px no-repeat !important" onclick="saveTag({{--{{$user->subscription->plan_id}}--}})">Create Tag</button>
                </div>
            </div>

        </div>

    </div>
</div>
<img src="<?php echo e(url('assets/images/loader.gif')); ?>" style="margin-left:10px; margin-bottom:5px; display:none;" id="tag_loader">
<input type="hidden" value="" id="is_account_id">
@endsection

@section('script')
    <script src="{{ URL::to('assets/js/selectize.js') }}"></script>
    <script>

        $( window ).load(function() {
            if ( $('.infusaccount option').length == 2 ) {
                $('.infusaccount option:last-child').attr('selected', 'selected');
                $( ".infusaccount" ).trigger( "change" );
            }  
        });
            
        $('.panel-collapse').on('show.bs.collapse', function () {
            $(this).parent('.panel').find('.fa-minus').show();
            $(this).parent('.panel').find('.fa-plus').hide();
        });
        $('.panel-collapse').on('hide.bs.collapse', function () {
            $(this).parent('.panel').find('.fa-minus').hide();
            $(this).parent('.panel').find('.fa-plus').show();
        });

        $(document).ready(function(){
            // Add minus icon for collapse element which is open by default
            $(".collapse.in").each(function(){
                $(this).siblings(".panel-heading").find(".glyphicon").addClass("glyphicon-minus").removeClass("glyphicon-plus");
            });

            // Toggle plus minus icon on show hide of collapse element
            $(".collapse").on('show.bs.collapse', function(){
                $(this).parent().find(".glyphicon").removeClass("glyphicon-plus").addClass("glyphicon-minus");
                ajaxRequest( $(this) );

            }).on('hide.bs.collapse', function(){
                $(this).parent().find(".glyphicon").removeClass("glyphicon-minus").addClass("glyphicon-plus");
                addLoader( $(this) );
            });
            
            $(document).on('change','.infusaccount', function(e) {
                var thisObj = $(this);
                
                if( thisObj.val() == '' ){
                    $(".pandaTemplats").hide();
                }
                else {
                    $(".pandaTemplats").show();
                    $("#is_account_id").val(thisObj.val());
                    $("#appName").val($(".infusaccount option:selected").text());
                }
            });
        });

        function ajaxRequest( selector )
        {
            var contentBody = selector.children();
            contentBody.find('img').show();
            var tempID    = selector.attr('data-id');
            var IS_account_id = $('#is_account_id').val();
            var url		  = "{{ url('docs/pandadocs/gettemplatedetails') }}";
            if( tempID ) {

                $.ajax({
                    'type': 'post',
                    'url' : url,
                    'data': { 'tempID':tempID, '_token':'{{ csrf_token() }}', 'account_id':IS_account_id },
                    'dataType':'html',
                    success: function(response){
                        if( response != "error" ) {
                            contentBody.html('');
                            contentBody.html(response);
                            var template = $("#temp_name").val();
                            $("#appName").val($(".infusaccount option:selected").text());
                            getTags('',template);
                        } else {
                            alert('Some error occur while completing the request. Please try after some time');
                            return false;
                        }
                    }
                });
            } else {
                return false;
            }
        }

        function addLoader( selector ) {
            selector.children().html('<img src="{{url('assets/images/loader.gif')}}" style="display:none;">');
        }

        

        function getTags(getNewTags,template = ''){
           
            var IS_account_id = $('#is_account_id').val();
            var url           = "{{ url('docs/gettagsfromisaccount') }}";
            var temp_id = $('input[name="temp_id"]').val();
            
            if ( IS_account_id ) {
                $.ajax({
                    'type': 'post',
                    'url' : url,
                    'data': { 'temp_id' : temp_id, 'IS_account_id':IS_account_id,'_token':'{{ csrf_token() }}','template': template   },
                    beforeSend: function() {
                        $('#tag_loader').show();
                    },
                    success: function(response){
                        if ( response ) {
                            var data = $.parseJSON(response);
                            $(".draft,.sent,.viewed,.completed,.voided,.rejected").empty();
                            $(".draft,.sent,.viewed,.completed,.voided,.rejected").append($("<option></option>") .attr("value", '').text('Select'));
                            
                            
                            $.each( data.all, function( key, value ) {
                                // $(".draft,.sent,.viewed,.completed,.voided,.rejected").append($("<option></option>").attr("value", key).text(value));
                                var selected1 = '';
                                if( data.saved['draft'] == key ){ 
                                    selected1 = 'selected';
                                }
                                $(".draft").append($("<option "+selected1+"></option>").attr("value", key).text(value));

                                var selected2 = '';
                                if( data.saved['sent'] == key ){
                                    selected2 = 'selected';
                                }
                                $(".sent").append($("<option "+selected2+"></option>").attr("value", key).text(value));

                                var selected3 = '';
                                if( data.saved['viewed'] == key ){
                                    selected3 = 'selected';
                                }
                                $(".viewed").append($("<option "+selected3+"></option>").attr("value", key).text(value));

                                var selected4 = '';
                                if( data.saved['completed'] == key ){
                                    selected4 = 'selected';
                                }
                                $(".completed").append($("<option "+selected4+"></option>").attr("value", key).text(value));

                                var selected5 = '';
                                if( data.saved['voided'] == key ){
                                    selected5 = 'selected';
                                }
                                $(".voided").append($("<option "+selected5+"></option>").attr("value", key).text(value));

                                var selected6 = '';
                                if( data.saved['rejected'] == key ){
                                    selected6 = 'selected';
                                }
                                $(".rejected").append($("<option "+selected6+"></option>").attr("value", key).text(value));
                            });
                            

                        } else {
                            alert('Some error occur while completing the request. Please try after some time');
                            return false;
                        }

                    },
                    complete:function(data) {
                        $('#tag_loader').hide();
                    }
                });
            } else {
                $(".draft,.sent,.viewed,.completed,.voided,.rejected").empty();
                $(".draft,.sent,.viewed,.completed,.voided,.rejected").append($("<option></option>") .attr("value", '').text('Select Infusionsoft Account to Get Tags'));
            }
        };
        function saveCategory(thisobj){
            var url = "{{ url('/docs/createtag') }}";
            thisobj.find('i').addClass('fa-spinner fa-spin');
            thisobj.prop('disabled',true);
            var IS_account_id = $('#is_account_id').val();
            
            $.ajax({
                'url': url,
                'type' : 'get',
                'data': {
                    'cat_name': $('#temp_name').val(),
                    'temp_id': $('input[name="temp_id"]').val(),
                    'account_id':IS_account_id,
                    'type':'pandadoc',
                },
                success: function( response){
                    if(JSON.parse(response).status ){
                        var template = $("#temp_name").val();
                        getTags('',template);
                        toastr.success("", "Tag Saved successfully. Going to reload you tags");
                    }else{
                        toastr.warning("", "Something went wrong while saving the tag");
                    }
                    thisobj.find('i').removeClass('fa-spinner fa-spin');
                    thisobj.prop('disabled',false);
                }
            })
        }

        function saveSelections(thisobj){
            var url = "{{ url('/docs/savetagselections') }}";
            thisobj.find('i').addClass('fa-spinner fa-spin');
            thisobj.prop('disabled',true);
            var IS_account_id = $('#is_account_id').val();
            
            var data = $('#template-form').serializeArray();
            data.push({name: "_token", value: '{{ csrf_token() }}'});
            data.push({name: "account_id", value: IS_account_id });
            data.push({name: "type", value: 'pandadoc' });
            $.ajax({
                'url': url,
                'type' : 'post',
                'data': data,
                success: function( response){
                    if( response.success ){
                        toastr.success("", "Tag Saved successfully.");
                    }else{
                        toastr.warning("", "Something went wrong while saving the tag");
                    }
                    thisobj.find('i').removeClass('fa-spinner fa-spin');
                    thisobj.prop('disabled',false);
                }
            })
        }

        function saveTag(){
            var url = "{{ url('/docs/createtag') }}";
            if($('#saveTagValue').val() === ""){
                toastr.options = {
                    positionClass: 'toast-top-center'
                };
                toastr.warning("", "Please enter the tag name you want to save");
                return;
            }

            $.ajax({
                'url': url,
                'type' : 'get',
                'data': {
                    'name': $('#saveTagValue').val()
                },
                success: function( response){
                    if(JSON.parse(response).status ){
                        $(".draft,.sent,.viewed,.completed,.voided,.rejected").empty();
                        getTags('reloadTags');
                        toastr.options = {
                            positionClass: 'toast-top-center'
                        };
                        toastr.success("", "Tag Saved successfully. Going to reload you tags");
                        $('#createTagModal').modal('toggle');
                    }else{
                        toastr.options = {
                            positionClass: 'toast-top-center'
                        };
                        toastr.warning("", "Something went wrong while saving the tag");
                        $('#createTagModal').modal('toggle');
                    }

                }
            })
        }

        $(document).on('click', '.btnSaveOptionals', function()
        {
            var url = "{{ url('/docs/saveadditionaloptions') }}";
            var thisBtn = $(this)
            thisBtn.find('i').addClass('fa-spinner fa-spin');
            thisBtn.prop('disabled',true);

            var data = $('#pseudoFrmMdditionalOption :input').serializeArray();
            data.push({name: "_token", value: '{{ csrf_token() }}'});
            data.push({name: "temp_id", value: $('#TemplateID').val()});
            $.ajax({
                'url': url,
                'type' : 'post',
                'data': data,
                success: function( response){
                    if( response.success ){
                        toastr.success("", "Saved successfully.");
                    }else{
                        toastr.error("", "Something went wrong while saving");
                    }
                    thisBtn.find('i').removeClass('fa-spinner fa-spin');
                    thisBtn.prop('disabled',false);
                },
                error: function(response)
                {
                    toastr.error("", "Something went wrong while saving");
                    thisBtn.find('i').removeClass('fa-spinner fa-spin');
                    thisBtn.prop('disabled',false);
                }
            })
        })

        $(document).on('change', '#chkboxSaveDocFields', function()
        {
            if($(this).is(':checked'))
            {
                $('.save-doc-wrapper').removeClass('hide');
                $('.addtional-option-btn-wrapper').removeClass('hide');
            }
            else
            {
                $('.save-doc-wrapper').addClass('hide');
                if($('#chkboxMostMostRecentOpportunity').is(':checked') == false)  $('.addtional-option-btn-wrapper').addClass('hide');
            }
        });

        $(document).on('change', '#chkboxMostMostRecentOpportunity', function()
        {
            if($(this).is(':checked'))
            {
                $('.most-recent-opp-wrapper').removeClass('hide');
                $('.addtional-option-btn-wrapper').removeClass('hide');
            }
            else
            {
                $('.most-recent-opp-wrapper').addClass('hide');
                if($('#chkboxSaveDocFields').is(':checked') == false)  $('.addtional-option-btn-wrapper').addClass('hide');
            }
        });
    </script>
@endsection
