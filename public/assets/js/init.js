jQuery.ajaxSetup({
  error: function(event, status, error) {
    var data = event.responseText 
    var content = 'Internal Error';
    try {
      var response = JSON.parse(data);
      content = response.message || content;
    } catch (e) {
    }

    toastr.error(content, error, {
      positionClass: 'toast-top-center'
    })
      .attr('style', 'width: 500px !important;')
  }    
});
