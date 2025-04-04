<!-- Página de inicialización con depuración incorporada -->
<h1>Envío de datos de inicialización</h1>

<div class="wrap api-debug-container">
    <!-- Control panel para mejor depuración -->
    <div class="control-panel" style="margin-bottom: 20px; padding: 15px; background: #f8f8f8; border: 1px solid #ddd; border-radius: 4px;">
        <h3>Panel de control</h3>
        <button type="button" id="config_initial" class="button button-primary">Enviar a API</button>
        <button type="button" id="ver_parametros" class="button button-secondary" style="margin-left: 10px;">Ver parámetros</button>
        <div id="loading_indicator" style="display:none; margin-left: 10px; margin-top: 10px;">
            Procesando solicitud... <img src="<?php echo admin_url('images/spinner.gif'); ?>" alt="Cargando..." style="vertical-align: middle;">
        </div>
    </div>

    <!-- Panel de depuración -->
    <div id="debug_panel" style="margin-bottom: 20px; display:none; padding: 15px; background: #f0f0f0; border: 1px solid #ccc; border-radius: 4px;">
        <h3>Información de depuración</h3>
        <pre id="debug_info" style="background: #fff; padding: 10px; overflow: auto; max-height: 200px; font-family: monospace;"></pre>
    </div>

    <!-- Mensajes de estado -->
    <div id="response_messages" style="margin-bottom: 20px;">
        <div id="api_error_message" class="notice notice-error" style="display:none; padding: 10px;"></div>
        <div id="api_success_message" class="notice notice-success" style="display:none; padding: 10px;"></div>
    </div>

    <!-- Formulario de resultados -->
    <form method="POST" action="options.php" style="display: block;">
        <?php
            settings_fields('facturaloperu-api-config-initial-response-group');
            do_settings_sections('facturaloperu-api-config-initial-response-group');
        ?>
        <h2>Resultado</h2>
        <textarea name="facturaloperu_api_initial_response" id="facturaloperu_api_initial_response" style="width: 100%; min-height: 300px;" readonly class="input-text regular-input"><?php echo esc_textarea(get_option('facturaloperu_api_initial_response')); ?></textarea>

        <?php submit_button('Guardar Respuesta'); ?>
    </form>
</div>

<!-- Implementación directa del JS para evitar problemas de carga -->
<script type="text/javascript">
jQuery(document).ready(function($) {
    // Debug Function
    function debugLog(message, data = null) {
        console.log(message, data);
        
        // Actualizar el panel de depuración
        var debugInfo = $('#debug_info');
        var timestamp = new Date().toLocaleTimeString();
        var logMessage = timestamp + ': ' + message;
        
        if (data) {
            if (typeof data === 'object') {
                logMessage += '\n' + JSON.stringify(data, null, 2);
            } else {
                logMessage += '\n' + data;
            }
        }
        
        debugInfo.prepend(logMessage + '\n\n');
        $('#debug_panel').show();
    }

    // Debug: Verificar que jQuery está funcionando
    debugLog('jQuery inicializado correctamente');
    
    // Comprobar si el botón existe
    if ($('#config_initial').length) {
        debugLog('Botón "Enviar a API" encontrado');
    } else {
        debugLog('ERROR: Botón "Enviar a API" NO encontrado');
    }

    // Mostrar parámetros
    $('#ver_parametros').on('click', function() {
        debugLog('Verificando parámetros de configuración...');
        
        // Realizar una solicitud AJAX para obtener los parámetros configurados
        $.ajax({
            type: "POST",
            url: ajaxurl,
            data: {
                'action': 'verificar_parametros_api_initial'
            },
            success: function(response) {
                debugLog('Parámetros obtenidos:', response);
            },
            error: function(xhr, status, error) {
                debugLog('Error al obtener parámetros: ' + error);
            }
        });
    });

    // Registrar el evento click en el botón principal
    $('#config_initial').on('click', function() {
        debugLog('Botón "Enviar a API" pulsado');
        
        // Limpiar mensajes anteriores
        $('#api_error_message').hide();
        $('#api_success_message').hide();
        $('#loading_indicator').show();
        
        // Obtener la URL de admin-ajax.php
        var ajaxUrl = '<?php echo admin_url('admin-ajax.php'); ?>';
        debugLog('URL de AJAX: ' + ajaxUrl);
        
        // Realizar la solicitud AJAX
        debugLog('Enviando solicitud AJAX...');
        
        $.ajax({
            type: "POST",
            url: ajaxUrl,
            data: {
                'action': 'api_service_config_initial',
                'type': 'initial',
                '_wpnonce': '<?php echo wp_create_nonce('api_initial_nonce'); ?>'
            },
            success: function(response) {
                $('#loading_indicator').hide();
                debugLog('Respuesta recibida', response);
                
                try {
                    // Si la respuesta ya es un objeto (WordPress la convierte automáticamente)
                    if (typeof response === 'object') {
                        var data = response;
                    } else {
                        // Intentar analizar la respuesta como JSON
                        var data = JSON.parse(response);
                    }
                    
                    if (data.success) {
                        $('#api_success_message').html('<p>¡La operación se completó con éxito!</p>').show();
                        $('#facturaloperu_api_initial_response').val(JSON.stringify(data, null, 4));
                    } else {
                        var errorMsg = data.message || 'Error desconocido en la respuesta';
                        $('#api_error_message').html('<p>Error: ' + errorMsg + '</p>').show();
                        $('#facturaloperu_api_initial_response').val(JSON.stringify(data, null, 4));
                    }
                } catch (e) {
                    debugLog('Error al procesar respuesta JSON: ' + e.message);
                    $('#api_error_message').html('<p>Error al procesar la respuesta: ' + e.message + '</p>').show();
                    $('#facturaloperu_api_initial_response').val(response);
                }
            },
            error: function(xhr, status, error) {
                $('#loading_indicator').hide();
                debugLog('Error en la solicitud AJAX', {
                    status: status,
                    error: error,
                    response: xhr.responseText
                });
                
                $('#api_error_message').html('<p>Error al realizar la solicitud: ' + error + '</p>').show();
            }
        });
    });
    
    // Inicialización completa
    debugLog('Script inicializado completamente');
});
</script>

<?php
// Añadir esta función a tu archivo de funciones o plugin
function verificar_parametros_api_initial() {
    $parameters = array(
        'api_url' => get_option('facturaloperu_api_config_url'),
        'api_token' => get_option('facturaloperu_api_config_token') ? 'configurado' : 'no configurado',
        'identification_number' => get_option('facturaloperu_api_config_document'),
        'type_document_id' => get_option('facturaloperu_api_resolution_document_type'),
        'prefix' => get_option('facturaloperu_api_resolution_prefix'),
        'number' => get_option('facturaloperu_api_initial_docs')
    );
    
    wp_send_json($parameters);
}
add_action('wp_ajax_verificar_parametros_api_initial', 'verificar_parametros_api_initial');
?>