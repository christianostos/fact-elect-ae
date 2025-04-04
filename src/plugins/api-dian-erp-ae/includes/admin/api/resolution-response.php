<h1>Envío de configuración de resolución</h1>
<div class="api-resolution-container">
    <div>
        <button type="button" name="config_resolution" id="config_resolution" class="button button-primary">Enviar a API</button>
        <span id="loading_indicator" style="display:none; margin-left: 10px;">
            <img src="<?php echo admin_url('images/spinner.gif'); ?>" alt="Cargando..." style="vertical-align: middle;">
            Procesando solicitud...
        </span>
    </div>
    
    <div id="api_error_message" class="notice notice-error" style="display:none; margin-top: 10px; padding: 10px;"></div>
    <div id="api_success_message" class="notice notice-success" style="display:none; margin-top: 10px; padding: 10px;"></div>

    <form method="POST" action="options.php" style="display: block; margin-top: 20px;">
        <?php
            settings_fields('facturaloperu-api-config-resolution-response-group');
            do_settings_sections('facturaloperu-api-config-resolution-response-group');
        ?>
        <h2>Resultado</h2>
        <textarea name="facturaloperu_api_resolution_response" id="facturaloperu_api_resolution_response" style="min-width: 700px;min-height: 300px;" readonly class="input-text regular-input"><?php echo esc_textarea(get_option('facturaloperu_api_resolution_response')); ?></textarea>

        <?php submit_button('Guardar Respuesta'); ?>
    </form>
</div>

<!-- Script para mejorar la interacción -->
<script type="text/javascript">
jQuery(document).ready(function($) {
    // Este script se ejecuta como complemento al archivo principal de api-resolution.js
    
    // Mostrar indicador de carga
    $('#config_resolution').on('click', function() {
        $('#loading_indicator').show();
        $('#api_error_message, #api_success_message').hide();
    });
    
    // Interceptar eventos ajax para mejorar la experiencia
    $(document).ajaxSuccess(function(event, xhr, settings) {
        if (settings.data && settings.data.indexOf('action=api_service_config_resolution') !== -1) {
            $('#loading_indicator').hide();
            
            try {
                // Analizar la respuesta
                var response = JSON.parse(xhr.responseText);
                
                if (response.success) {
                    $('#api_success_message').html('<p>' + (response.message || '¡Operación completada con éxito!') + '</p>').show();
                } else {
                    $('#api_error_message').html('<p>Error: ' + (response.message || 'Ocurrió un error al procesar la solicitud') + '</p>').show();
                }
            } catch (e) {
                $('#api_error_message').html('<p>Error al procesar la respuesta</p>').show();
            }
        }
    });
    
    // Manejar errores ajax
    $(document).ajaxError(function(event, xhr, settings) {
        if (settings.data && settings.data.indexOf('action=api_service_config_resolution') !== -1) {
            $('#loading_indicator').hide();
            $('#api_error_message').html('<p>Error de conexión: No se pudo completar la solicitud</p>').show();
        }
    });
    
    // Verificación de depuración
    console.log('Script de resolution-response.php inicializado correctamente');
});
</script>