@extends('layouts.app')

@section('content')

<h1 class="title">Infusionsoft Automated PandaDoc Proposals</h1>
<div class="inner-content">
	@if ( Session::has('success') )
		<span class="help-block text-center" style=" color:green;">
			<strong>{{ Session::get('success') }}</strong>
			{{ Session::forget('success') }}
		</span>
	@endif
	@if ( Session::has('error') )
		<span class="help-block text-center" style=" color:#C24842;">
			<strong>{{ Session::get('error') }}</strong>
			{{ Session::forget('error') }}
		</span>
	@endif

	<div>
		@if ( count($account) == 0 )
		<a class="button" href="{{ url('/manageaccounts') }} " style="width:100%">Please add your PandaDoc account here</a>
		@endif
	</div>
	@if ( count($account) > 0 ) 
		<div>
			<a class="button get-templates" href="javascript:void(0)">Get Your Templates</a>
			<img src="{{ url('assets/images/loader.gif') }}" id ="loader" style="display:none;"/>
			<span style="color:red; display:none;" id="auth_account_msg">Please authorise your PandaDoc account to get templates.</span>
		</div>
	@endif
	<div id="templates" style="margin-top:10px; margin-left:20px;">
	</div>

	<div id="wrapper">
	</div>
	<div id="tips">
		<h3 class="tips">How To's & Tips </h3>
		<ul class="simple-text">
			@if ( count($account) == 0 )
				<li>Authorise Your pandaDoc Account.</li>
			@else
				<li><span>Get Tokens & Fields</span> - This button will list all the fields and tokens of associated template. </li>
				<li>Save the roles of Client & Signer in Roles section under Send HTTP Post section. The role parameter is optional. If passed, a person is assigned all fields matching their corresponding role. If not passed, a person will receive a read-only link to view the document.</li>
				<li>Post the fields shown in Name/Value Pairs section under Send HTTP Post to the POST URL.</li>
				<li>Field's name must be unique.</li>
				<li>Asterisk symbole(*) used to indicate the mandatory fields.</li>
			@endif
		</ul>
	</div>
</div>
	<style>
		.bg-color {
			background:#FFFFFF !important;
		}
		.wrapper_template {
			background-color: #F5F5F5 !important;
		
		}
		#container {
			background-color: #FFFFFF !important;
			margin-left: 70px;
			padding-left:10px;
			padding-top:10px;
			padding-top:10px;
		}
		#tokens_fields {
			margin-bottom:10px;
		}
		ul.simple-text li {
			padding-bottom: 0px;
		}
		ul.simple-text {
			width:100% !important;
		}
</style>
	</style>
	
@endsection

@section('script')
	<script>
	$(document).ready(function(){


		/* Get My Templates */
		$(document).off('click','.get-templates').on('click','.get-templates', function(e) {
			e.preventDefault();
			var account   = {{ count($account) }};
			if ( account == 0 ) {
				$('#auth_account_msg').show();
				return false;
			} else{
				$('#auth_account_msg').hide();
			}
			var url		  = "{{ url('manage-panda-account/list-templates') }}";
			$.ajax({
				'type': 'post',
				'url' : url,
				'data': { '_token':'{{ csrf_token() }}' },
				'dataType':'html',
				beforeSend: function() {
					$('#loader').show();
				},
				success: function(response){
					if( response != "error" ) {
						$('#templates').html('');
						$('#templates').html(response);
					}else{
						alert('Error occur while completing the request. Please try after some time');
					}
				},
				complete:function(data) {
					$('#loader').hide();
				}
			});
		});

		/* save template settings */
		$(document).off('click','#save_roles').on('click','#save_roles',function(e){
			e.preventDefault();
			$('.settings_success').text('');
			var errorCount		= 0;
			var dataArr			= [];
			var tagIDArr        = [];
			var loader			= $(this).parent().next();
			$('.role').each( function(){
				var val = $(this).val();

				if (val) {
					$(this).next('.role-error').hide();
					var role = $(this).val();
					dataArr.push(role);
				} else {
					$(this).next('.role-error').show();
					errorCount = errorCount+1;
				}
			});

			var temp_name = $('#temp_name').val();
			var created_by = $("#created_by").val();
			var selectedISAccount   = $('.IS_account option:selected').val();

			if ( selectedISAccount == "" ) {
			    $(".IS_account").next('.role-error').show();
			    errorCount = errorCount+1;
			} else {
			    $(".IS_account").next('.role-error').hide();
			}

			var draftStatusTag       = $('.draft option:selected').val();

			if ( draftStatusTag == "" ) {
			    $(".draft").next('.role-error').show();
			    errorCount = errorCount+1;
			} else {
			    $(".draft").next('.role-error').hide();
			}

			var sentStatusTag       = $('.sent option:selected').val();

			if ( sentStatusTag == "" ) {
			    $(".sent").next('.role-error').show();
			    errorCount = errorCount+1;
			} else {
			    $(".sent").next('.role-error').hide();
			}
		    var viewedStatusTag     = $('.viewed option:selected').val();

		    if ( viewedStatusTag == "" ) {
			    $(".viewed").next('.role-error').show();
			    errorCount = errorCount+1;
			} else {
			    $(".viewed").next('.role-error').hide();
			}
		    var completedStatusTag  = $('.completed option:selected').val();

		    if ( completedStatusTag == "" ) {
			    $(".completed").next('.role-error').show();
			    errorCount = errorCount+1;
			} else {
			    $(".completed").next('.role-error').hide();
			}

		    var voidedStatusTag     = $('.voided option:selected').val();

		    if ( voidedStatusTag == "" ) {
			    $(".voided").next('.role-error').show();
			    errorCount = errorCount+1;
			} else {
			    $(".voided").next('.role-error').hide();
			}
		    var rejectedStatusTag   = $('.rejected option:selected').val();

		    if ( rejectedStatusTag == "" ) {
			    $(".rejected").next('.role-error').show();
			    errorCount = errorCount+1;
			} else {
			    $(".rejected").next('.role-error').hide();
			}

			if ( errorCount ) {
				return false;
			}else {
				var tempID = $('#TemplateID').val();
				var url = "{{ url('manage-panda-account/save-template-settings') }}";
				$.ajax({
					'type': 'post',
					'url' : url,
					'data': { 'role_set':dataArr,'tempID':tempID,'temp_name':temp_name,'created_by':created_by,'selectedISAccount':selectedISAccount,'draftStatusTag':draftStatusTag,'sentStatusTag':sentStatusTag,'viewedStatusTag':viewedStatusTag,'completedStatusTag':completedStatusTag,'voidedStatusTag':voidedStatusTag,'rejectedStatusTag':rejectedStatusTag,'_token':'{{ csrf_token() }}' },
					'dataType':'html',
					beforeSend: function() {
						loader.show();
					},
					success: function(response){
						if( response != "error" ) {
							$('#save_roles').text("Update Template's Role Settings");
							$('.settings_success').text('Settings saved successfully.');
						}else {
							alert('Some error occur while completing the request. Please try after some time');
							return false;
						}
					},
					complete:function(data) {
						loader.hide();
					}
				});
			}
		});
		/* Get Detail of selected template ends */

		$(document).off('change','.IS_account').on('change','.IS_account', function(){
		    var IS_account_id = $(this).val();
		    var url           = "{{ url('manage-panda-account/get_tags_from_ISAccount') }}";
		    if ( IS_account_id ) {
		        $.ajax({
					'type': 'post',
					'url' : url,
					'data': { 'IS_account_id':IS_account_id,'_token':'{{ csrf_token() }}' },
					beforeSend: function() {
						$('#tag_loader').show();
					},
					success: function(response){
						if ( response ) {
						    var data = $.parseJSON(response);
						    $(".draft,.sent,.viewed,.completed,.voided,.rejected").empty();
						    $(".draft,.sent,.viewed,.completed,.voided,.rejected").append($("<option></option>") .attr("value", '').text('Select'));
						    $.each( data, function( key, value ) {
                                $(".draft,.sent,.viewed,.completed,.voided,.rejected").append($("<option></option>").attr("value", key).text(value));
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
		});


	});

</script>
@endsection
