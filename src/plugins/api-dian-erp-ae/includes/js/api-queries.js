jQuery(document).ready(function($) {
    $('#config_company').click(function() {
        var abs_url = api_script_object.ajaxurl;
        $(this).nextAll('.response-message').remove();
        
        // Mostrar indicador de carga
        $(this).after('<span class="response-message">Enviando datos a la API, por favor espere...</span>');
        
        $.ajax({
            type: "POST",
            url: abs_url,
            data: {
                'action': 'api_service_config_company'
            },
            success: function(data) {
                // Eliminar indicador de carga
                $('.response-message').remove();
                
                try {
                    // Intentar limpiar la respuesta si contiene "Array"
                    if (typeof data === 'string') {
                        data = data.split('Array').join('');
                        var obj = JSON.parse(data);
                    } else {
                        var obj = data;
                    }
                    
                    if (obj.success == true) {
                        $('#config_company').after('<span class="response-message" style="color:green;">Datos enviados correctamente</span>');
                        var strong = JSON.stringify(obj, undefined, 4);
                        $('#facturaloperu_api_config_response').val(strong);
                        
                        // Si existe el campo de token, actualízalo
                        if ($('#facturaloperu_api_config_token').length) {
                            $('#facturaloperu_api_config_token').val(obj.token);
                        }
                    } else {
                        $('#config_company').after('<span class="response-message" style="color:red;">Error: ' + (obj.message || 'No se pudo procesar la respuesta') + '</span>');
                        console.log(obj);
                    }
                } catch (e) {
                    $('#config_company').after('<span class="response-message" style="color:red;">Error al procesar la respuesta: ' + e.message + '</span>');
                    console.log('Error al procesar respuesta:', e);
                    console.log('Datos recibidos:', data);
                }
            },
            error: function(xhr, status, error) {
                $('.response-message').remove();
                $('#config_company').after('<span class="response-message" style="color:red;">Error de conexión: ' + error + '</span>');
                console.log(xhr.responseText);
            }
        });
    });
});