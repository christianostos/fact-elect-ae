<h1>Cambio de entorno</h1>
<!-- <?php //if(get_option('facturaloperu_api_environment_response') == ''){ ?> -->
    <div>
        <button type="button" name="config_environment" id="config_environment" class="button button-primary">Enviar a API</button>
    </div>
<!-- <?php //} ?> -->
<form method="POST" action="options.php" style="display: block;">
    <?php
        settings_fields('facturaloperu-api-config-environment-group');
        do_settings_sections('facturaloperu-api-config-environment-group');
    ?>
    <h2>Resultado</h2>

    <textarea name="facturaloperu_api_environment_response" id="facturaloperu_api_environment_response" style="min-width: 700px;min-height: 250px;" readonly class="input-text regular-input"><?php echo get_option('facturaloperu_api_environment_response'); ?></textarea>

    <?php submit_button(); ?>
</form>