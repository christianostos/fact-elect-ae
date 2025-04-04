<h1>Envío de datos de Empresa</h1>
<!-- <?php //if(get_option('facturaloperu_api_config_response') == ''){ ?> -->
    <div>
        <button type="button" name="config_company" id="config_company" class="button button-primary">Enviar a API</button>
    </div>
<!-- <?php //} ?> -->
<form method="POST" action="options.php" style="display: block;">
    <?php
        settings_fields('facturaloperu-api-config-company-response-group');
        do_settings_sections('facturaloperu-api-config-company-response-group');
    ?>
    <h2>Resultado</h2>
    <textarea name="facturaloperu_api_config_response" id="facturaloperu_api_config_response" style="min-width: 700px;min-height: 400px;" readonly class="input-text regular-input"><?php echo get_option('facturaloperu_api_config_response'); ?></textarea>

    <!-- <input type="text" name="facturaloperu_api_config_token" readonly hidden id="facturaloperu_api_config_token" value="<?php //echo get_option('facturaloperu_api_config_token'); ?>" style="min-width: 400px" class="input-text regular-input"> -->

    <?php submit_button(); ?>
</form>