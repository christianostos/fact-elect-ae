jQuery(document).ready(function($) {
    console.log('Script action_button_send cargado');
    
    // Verificar que el botón existe
    if ($('#on_send_json').length) {
        console.log('Botón de reenviar encontrado');
    } else {
        console.log('ADVERTENCIA: Botón de reenviar no encontrado');
    }
    
    $('#on_send_json').on('click', function() {
        console.log('Botón de reenviar presionado');
        
        var postId = $(this).data('post-id');
        if (!postId) {
            console.error('Error: No se encontró el ID del post');
            alert('Error: No se pudo determinar el ID del pedido');
            return;
        }
        
        console.log('Enviando pedido ID: ' + postId);
        
        // Mostrar indicador de carga
        $(this).prop('disabled', true).html('Enviando...');
        
        $.ajax({
            url: ajaxurl, // Debe estar definido por WordPress
            type: 'POST',
            data: {
                action: 'send_json_to_api',
                post_id: postId,
            },
            success: function(response) {
                console.log('Respuesta recibida:', response);
                
                // Habilitar el botón
                $('#on_send_json').prop('disabled', false).html('Reenviar');
                
                if (response.success) {
                    // Extraer mensaje amigable
                    var mensaje = 'Operación completada';
                    if (response.data && response.data.message) {
                        mensaje = response.data.message;
                    }
                    alert('Éxito: ' + mensaje);
                    
                    // Opcional: recargar la página para mostrar la nueva respuesta
                    location.reload();
                } else {
                    var errorMsg = 'Error desconocido';
                    if (response.data && response.data.message) {
                        errorMsg = response.data.message;
                    }
                    alert('Error: ' + errorMsg);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error en la solicitud:', status, error);
                
                // Habilitar el botón
                $('#on_send_json').prop('disabled', false).html('Reenviar');
                
                // Intentar extraer un mensaje de error
                var errorMsg = 'Error de conexión';
                try {
                    var response = JSON.parse(xhr.responseText);
                    if (response && response.message) {
                        errorMsg = response.message;
                    }
                } catch(e) {
                    // Si no podemos analizar la respuesta, usamos el error genérico
                }
                
                alert('Error: ' + errorMsg);
            }
        });
    });
});