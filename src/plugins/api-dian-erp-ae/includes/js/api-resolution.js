jQuery(document).ready(function($) {
    console.log('Script api-resolution.js cargado correctamente');
    
    // Verificar si el botón existe
    if ($('#config_resolution').length) {
        console.log('Botón config_resolution encontrado');
    } else {
        console.log('ADVERTENCIA: Botón config_resolution NO encontrado');
    }
    
    $('#config_resolution').on('click', function() {
        console.log('Botón config_resolution presionado');
        
        // Mostrar un indicador visual de carga
        var loadingMsg = $('<div class="notice notice-info"><p>Procesando solicitud...</p></div>');
        $(this).after(loadingMsg);
        
        var abs_url = api_script.ajaxurl;
        
        // Enviar la solicitud AJAX con el parámetro type
        $.ajax({
            type: "POST",
            url: abs_url,
            data: {
                'action': 'api_service_config_resolution',
                'type': 'resolution'
            },
            beforeSend: function() {
                console.log('Enviando solicitud a: ' + abs_url);
            },
            success: function(response) {
                console.log('Respuesta recibida', response);
                loadingMsg.remove();
                
                try {
                    // Comprobar si la respuesta ya es un objeto
                    var obj;
                    if (typeof response === 'object') {
                        // La respuesta ya es un objeto, no necesita ser parseada
                        obj = response;
                    } else {
                        // La respuesta es una cadena, intentamos limpiarla y parsearla
                        response = response.replace(/Array/g, '');
                        
                        // Buscar el inicio de un objeto JSON válido
                        var jsonStart = response.indexOf('{');
                        if (jsonStart >= 0) {
                            response = response.substring(jsonStart);
                        }
                        
                        obj = JSON.parse(response);
                    }
                    
                    console.log('Objeto de respuesta:', obj);
                    
                    if (obj.success === true) {
                        // Mostrar mensaje de éxito
                        var successMsg = $('<div class="notice notice-success"><p>Resolución configurada correctamente.</p></div>');
                        $('#config_resolution').after(successMsg);
                        
                        // Actualizar el textarea
                        var strong = JSON.stringify(obj, undefined, 4);
                        $('#facturaloperu_api_resolution_response').val(strong);
                    } else {
                        // Mostrar mensaje de error
                        var errorMsg = $('<div class="notice notice-error"><p>Error: ' + (obj.message || 'Ocurrió un error al procesar la solicitud') + '</p></div>');
                        $('#config_resolution').after(errorMsg);
                        
                        console.error('Error en la respuesta:', obj);
                        $('#facturaloperu_api_resolution_response').val(JSON.stringify(obj, undefined, 4));
                    }
                } catch (e) {
                    console.error('Error al procesar la respuesta JSON:', e, response);
                    var parseErrorMsg = $('<div class="notice notice-error"><p>Error al procesar la respuesta: ' + e.message + '</p></div>');
                    $('#config_resolution').after(parseErrorMsg);
                    
                    // Guardar la respuesta cruda en el textarea
                    $('#facturaloperu_api_resolution_response').val('Error al procesar la respuesta: ' + (typeof response === 'object' ? JSON.stringify(response) : response));
                }
            },
            error: function(xhr, status, error) {
                loadingMsg.remove();
                console.error('Error en la solicitud AJAX:', status, error, xhr.responseText);
                
                var ajaxErrorMsg = $('<div class="notice notice-error"><p>Error en la solicitud: ' + error + '</p></div>');
                $('#config_resolution').after(ajaxErrorMsg);
                
                $('#facturaloperu_api_resolution_response').val('Error en la solicitud: ' + error + '\n\nRespuesta: ' + xhr.responseText);
            }
        });
    });
});