<h1>Configuración Certificado</h1>

<form method="POST" action="options.php">
    <?php
        settings_fields('facturaloperu-api-config-certificate-group');
        do_settings_sections('facturaloperu-api-config-certificate-group');
    ?>
    <table class="form-table">
        <tr valign="top">
            <th class="titledesc">
                <label>Certificado (Base 64)</label>
            </th>
            <td class="forminp forminp-text">
                <textarea type="text" name="facturaloperu_api_certificate" id="facturaloperu_api_certificate" style="min-width: 400px; min-height: 250px" class="input-text regular-input"><?php echo get_option('facturaloperu_api_certificate'); ?></textarea>
            </td>
        </tr>
        <tr>
            <th class="titledesc">
                <label>Contraseña</label>
            </th>
            <td class="forminp forminp-text">
                <input type="text" name="facturaloperu_api_certificate_password" id="facturaloperu_api_certificate_software" value="<?php echo get_option('facturaloperu_api_certificate_password'); ?>" style="min-width: 400px" class="input-text regular-input">
            </td>
        </tr>
    </table>

    <?php submit_button(); ?>

</form>