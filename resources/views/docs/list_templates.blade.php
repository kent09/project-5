	
	@if ( count($templates) ) 
	 <div class="panel-group" id="accordion">
	        @foreach ( $templates as $id => $name )
		       <div class="panel panel-default" >
                    <div class="panel-heading">
                        <h4 class="panel-title">
                            <a data-toggle="collapse" data-parent="#accordion" href="#{{ $id }}"><span class="glyphicon glyphicon-plus"></span>{{ $name }}</a>
                        </h4>
                    </div>
                    <div id="{{ $id }}" class="panel-collapse collapse" data-id="{{ $id }}">
                        <div class="panel-body ajax">
                            <img src="{{ url('assets/images/loader.gif') }}"/>
                        </div>
                    </div>
                </div>
	        @endforeach
	    </div>
	@else 
		<div class="row">
			<div class="col-md-5">
				No Record Found.
			</div>
		</div>
	@endif
    <style type="text/css">
    .panel-title .glyphicon{
        font-size: 14px;
    }
    </style>
    @section('script')
    <script>
    
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
        
    });
    
    function ajaxRequest( selector )
    {
        var contentBody = selector.children();
        contentBody.find('img').show();
    	var tempID    = selector.attr('data-id');
    	var url		  = "{{ url('manage-panda-account/get-template-details') }}";
    	if( tempID ) {
    	    
        	 $.ajax({
        		'type': 'post',
        		'url' : url,
        		'data': { 'tempID':tempID, '_token':'{{ csrf_token() }}' },
        		'dataType':'html',
        		success: function(response){
        			if( response != "error" ) {
        			    contentBody.html('');
        			    contentBody.html(response);
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
</script>