@extends('layouts.apptools')
@section('title', 'Import CSV')
@section('content')
<div class="">
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

	<h1 class="title">Import CSV</h1>
	<div class="row">
        <div class="col-md-12">
            <div class="panel panel-default panel-import">
                <div class="inner-content panel-body text-center">
					<h4><span>Step 4: Tags</span></h4>
					
					<form name="step3" method="post" action="{{ url('/csvimport/step5') }}" id="myForm">
						{{ csrf_field() }}

						<div class="form-group">
							<label>Would you like to apply tag(s) to ALL of these contacts</label>
							<div>
								<label class="radio-inline">
									<input type="radio" name="apply_tags" value="yes" checked> Yes
								</label>
								<label class="radio-inline">
									<input type="radio" name="apply_tags" value="no" > No
								</label>
							</div>
						</div>
						<div class="form-group" id="search-tags">
							<label for="exampleInputFile">Search and pick tags to apply</label>
							<input type="text" class="form-control" id="select-tags" name="tags">
							<span class="help-block error_msg" style="display:none; color:#C24842;">
								<strong>Please pick atleast one tag .</strong>
							</span>
						</div>
						<div class="form-group row">
							<a class="btn btn-danger pull-left btn_cls"  href="{{ url('/csvimport/step3') }}"><i class="fa fa-arrow-left"></i> Back</a>

							<button class="pull-right btn btn-success" id="finish" type="button">Next <i class="fa fa-"></i></button>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>

@endsection

@section('script')
<script type="text/javascript">
	$(document).ready(function(){

		var val = $('input[name=apply_tags]:checked', '#myForm').val();
		if( val == "yes" ) {
			$('#search-tags').css('display','block');
			$('.error_msg').css('display','none');
		} else {
			$('#search-tags').css('display','none');
			$('.error_msg').css('display','none');
		}


		var tags = <?php echo $tags; ?>;
//~ console.log(JSON.stringify(tags));
$("#select-tags").tokenInput(tags,{
	propertyToSearch: "GroupName",
	hintText: "Select Tags",
	noResultsText: "No results",
	searchingText: "Searching...",
	preventDuplicates: true,
	allowFreeTagging: true,
});

$('#myForm input').on('change', function() {
	var val = $('input[name=apply_tags]:checked', '#myForm').val();
	if( val == "yes" ) {
		$('#search-tags').css('display','block');
		$('.error_msg').css('display','none');
	} else {
		$('#search-tags').css('display','none');
		$('.error_msg').css('display','none');
	}
});

$("#finish").click(function () {
	var val = $('#select-tags').tokenInput("get");
	var radioVal = $('input[name=apply_tags]:checked', '#myForm').val();
	if( radioVal == "yes" && val == '' ) {
		$('.error_msg').css('display','block');
		return false; 
	}
//$("#hiddenfield").val(val);
$('#myForm').submit();	
});
});
</script>
@endsection

