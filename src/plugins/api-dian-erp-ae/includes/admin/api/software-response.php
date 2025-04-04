<h1>Envio de datos de Software</h1>
<!-- <?php //if(get_option('facturaloperu_api_software_response') == ''){ ?> -->
    <div>
        <button type="button" name="config_software" id="config_software" class="button button-primary">Enviar a API</button>
    </div>
<!-- <?php //} ?> -->
<form method="POST" action="options.php" style="display: block;">
    <?php
        settings_fields('facturaloperu-api-config-software-response-group');
        do_settings_sections('facturaloperu-api-config-software-response-group');
    ?>
    <h2>Resultado</h2>
    <textarea name="facturaloperu_api_software_response" id="facturaloperu_api_software_response" style="min-width: 700px;min-height: 250px;" readonly class="input-text regular-input"><?php echo get_option('facturaloperu_api_software_response'); ?></textarea>

    <?php submit_button(); ?>
</form>