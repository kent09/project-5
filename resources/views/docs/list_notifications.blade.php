@extends('layouts.appdocs')
@section('title', 'PandaDoc Notifications')
@section('content')
	<h1 class="title">PandaDoc Notifications</h1>
	<div class="row">
		<div class="col-md-12">
			<div class="panel" style="">
				<div class="panel-body inner-content" style="">
					@include('docs/_list_notifications_ajax')
				</div>
			</div>
		 
		</div>
	</div>
	
	<style>
		.action-icons i {
			font-size:18px;
			margin-right:5px;
		}
	</style>
	
@endsection

@section('script')
	<script type="text/javascript">
	var notty_arr = [];
	$(document).ready(function(){
		
		$(':checkbox').each(function(checkbox) {
			if ( $(this).is(':checked') ) {
				$(this).prop('checked',false);
			}
		});
		
		$(document).off('click','.notty-check').on('click','.notty-check',function(e){
			
			if ( $('.notty-check:checked').length == $('.notty-check').length ) {
					$('.notty-all-check').prop('checked',true);
			} else {
				$('.notty-all-check').prop('checked',false);
			}
			
			var len = $("input:checked").length;
			
			if ( len >= 1 ) {
				$('#remove').css('visibility','visible');
			} else {
				$('#remove').css('visibility','hidden');
			}
			var notty_id = $(this).attr('data-id');
			
			if ( notty_arr.indexOf(notty_id) == -1) {
				notty_arr.push( notty_id );
			} else {
				var index = notty_arr.indexOf(notty_id);
				notty_arr.splice( index, 1);
			}
			
			
			
		});
		
		$(document).off('click','.notty-all-check').on('click','.notty-all-check',function(e){
			if ( $(this).is(':checked') ) {
				
				$('.notty-check').each( function( index, element ) {
					var notty_id = $(this).attr('data-id');
					
					if ( !$(this).is(':checked') ) {
						$(this).prop('checked',true);
						notty_arr.push( notty_id );
					} 
				});
			} else {
				$('.notty-check').each( function( index, element ) {
					var notty_id = $(this).attr('data-id');
					if ( $(this).is(':checked') ) {
						$(this).prop('checked',false);
						var index = notty_arr.indexOf(notty_id);
						notty_arr.splice( index, 1);
					} 
					
				});
			}
			var len = $("input:checked").length;
			
			if ( len >= 1 ) {
				$('#remove').css('visibility','visible');
			} else {
				$('#remove').css('visibility','hidden');
			}
		});
		
		$(document).off('click','#remove').on('click','#remove',function(e){
			e.preventDefault();
			$('.success_msg').text('');
			if ( notty_arr ) {
				var url = "{{ url('manage-panda-account/delete-notty') }}";
				
				$.ajax({
					'type': 'post',
					'url' : url,
					'data': { 'selectedNottyArr':notty_arr,'_token':'{{ csrf_token() }}' },
					'dataType':'html',
					success: function(response){
						if( response ) {
							$('.panel-body').html('');
							$('.panel-body').html(response);
							$('.success_msg').html('Notification(s) removed successfully.');
						} 
					}
				});
			} else {
				alert('please select at-least one notification.');
			}
			
			
		});
	});

</script>
@endsection
