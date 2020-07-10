@extends('layouts.app')

@section('content')
	<h1 class="title">User Plans</h1>
	<div class="inner-content">
	    <h3>
            Select Any plan:
	    </h3>
			<button type="Button" id="addcount" onclick="addHistory()" class="button btn-primary">Make Perposal</button>
	    
	</div>

	<style>
	.inner-content {
		min-height:400px;
	}
	</style>
  <script>
	  function addHistory() {
          var url = "{{ url('doc') }}";
          $.ajax({
              type:"POST",
              data: {
                  "_token": "{{ csrf_token() }}"
              },
              url: url,
              success:function (responce) {
                  if(responce == 'true'){
                      toastr.options = {
                          positionClass: 'toast-top-center'
                      };
                      toastr.success("", "Verified");
				  }
				  else if (responce == 'false'){
                      toastr.options = {
                          positionClass: 'toast-top-center'
                      };
                      toastr.warning("", "Limit Over");
				  }else {
                      toastr.options = {
                          positionClass: 'toast-top-center'
                      };
                      toastr.warning("", responce);
                  }
              }
          });
      };

  </script>
@endsection
