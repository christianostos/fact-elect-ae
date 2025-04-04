/**
 * Script revisado para la funcionalidad de inicialización API
 */
jQuery(document).ready(function($) {
    console.log('API Initial Script Loaded');
    
    // Verificar si estamos en la página correcta
    if ($('#config_initial').length === 0) {
        console.log('Botón de configuración inicial no encontrado en esta página');
        return;
    }
    
    console.log('Botón de configuración inicial encontrado. Inicializando eventos...');
    
    // Función para mostrar mensajes de estado
    function showStatus(type, message) {
        if (type === 'error') {
            $('#api_error_message').html('<p>' + message + '</p>').show();
            $('#api_success_message').hide();
        } else {
            $('#api_success_message').html('<p>' + message + '</p>').show();
            $('#api_error_message').hide();
        }
    }
    
    // Función para formatear JSON
    function formatJsonOutput(data) {
        try {
            if (typeof data === 'string') {
                data = JSON.parse(data);
            }
            return JSON.stringify(data, null, 4);
        } catch (e) {
            console.error('Error al formatear JSON:', e);
            return data;
        }
    }
    
    // Registrar evento click
    $('#config_initial').on('click', function(e) {
        e.preventDefault();
        console.log('Botón de configuración inicial presionado');
        
        // Limpiar mensajes anteriores
        $('#api_error_message').hide();
        $('#api_success_message').hide();
        
        // Mostrar indicador de carga
        $('#loading_indicator').show();
        
        // Realizar la petición AJAX
        $.ajax({
            type: 'POST',
            url: api_script.ajaxurl,
            data: {
                action: 'api_service_config_initial',
                type: 'initial',
                nonce: api_script.nonce
            },
            success: function(response) {
                console.log('Respuesta recibida:', response);
                $('#loading_indicator').hide();
                
                if (response.success) {
                    showStatus('success', '¡Operación completada con éxito!');
                    
                    // Actualizar el textarea con la respuesta formateada
                    var formattedOutput = formatJsonOutput(response.data);
                    $('#facturaloperu_api_initial_response').val(formattedOutput);
                } else {
                    var errorMsg = response.data && response.data.message 
                        ? response.data.message 
                        : 'Ocurrió un error al procesar la solicitud';
                    
                    showStatus('error', errorMsg);
                    
                    // Mostrar detalles del error en el textarea
                    var formattedOutput = formatJsonOutput(response.data);
                    $('#facturaloperu_api_initial_response').val(formattedOutput);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error en la solicitud AJAX:', status, error);
                $('#loading_indicator').hide();
                
                showStatus('error', 'Error de conexión: ' + error);
            }
        });
    });
    
    console.log('Eventos inicializados correctamente');
});