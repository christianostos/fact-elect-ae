jQuery(document).ready(function($) {
    $('#config_software').click(function() {
        console.log('aqui');
        var abs_url = api_script.ajaxurl
        $(this).nextAll().remove();
        $.ajax({
            type: "POST",
            url: abs_url,
            data: {
                'action': 'api_service_config_software'
            },
            success: function(reponse){
                console.log(reponse);
                reponse = reponse.split('Array').join('');
                var obj = JSON.parse(reponse);
                if (obj.success == true) {
                    var strong = JSON.stringify(obj, undefined, 4);
                    $('#facturaloperu_api_software_response').val(strong);
                } else {
                    console.log(obj);
                }
            }
        });
    });
});