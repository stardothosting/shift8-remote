jQuery(document).ready(function() {
    jQuery('#shift8-remote-api-button').click(function(e) {
        e.preventDefault();
        var button = jQuery(this);
        var url = button.attr('href');

        jQuery.ajax({
            url: url, 
            data: {
                'action' : 'shift8_remote_response',
            },
            success:function(response) {
                //console.log('response : ' + JSON.stringify(response));
                //console.log(the_ajax_script.ajaxurl);
                jQuery('#shift8-api-key-display').text(response);
                jQuery('input[name=shift8_remote_api_key]').val(response);
                alert('Note : Re-generating the API key will invalidate all previously set API queries. Dont forget to save after re-generating!');
           },
           error: function(errorThrown) {
                console.debug('Failure : ' + JSON.stringify(errorThrown));
           }
       });
        return false;
    });
});