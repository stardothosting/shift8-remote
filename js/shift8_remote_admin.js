jQuery(document).ready(function() {
    jQuery('#shift8-remote-api-button').click(function(e) {
        e.preventDefault();
        var data = {
            action: 'shift8_remote_response',
            nonce: the_ajax_script.nonce,
        };
        // the_ajax_script.ajaxurl is a variable that will contain the url to the ajax processing file
        jQuery.post(the_ajax_script.ajaxurl, data, function(response) {
            //console.debug(response);
            //console.debug(data);
            //console.debug(the_ajax_script.ajaxurl);
            jQuery('#shift8-api-key-display').text(response);
            jQuery('input[name=shift8_remote_api_key]').val(response);
            alert('Note : Re-generating the API key will invalidate all previously set API queries. Dont forget to save after re-generating!');
        });
        return false;
    });

});