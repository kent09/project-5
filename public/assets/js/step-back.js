$(document).ready(function(){

    $('#step-back').click(function() {
        let step = $(this).data('step');
        let importid = $(this).data('importid');
        let CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
        console.log(CSRF_TOKEN);
        $.ajax({
            url: '/csvimport-update-step',
            type: 'POST',
            data: {
                _token: CSRF_TOKEN, 
                step: step, 
                id: importid
            },
            dataType: 'JSON',
            success: function (data) {
                console.log(data);
            }
        });

    });
});