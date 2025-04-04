<h1>Configuración Software</h1>

<form method="POST" action="options.php">
    <?php
        settings_fields('facturaloperu-api-config-software-group');
        do_settings_sections('facturaloperu-api-config-software-group');
    ?>
    <table class="form-table">
        <tr valign="top">
            <th class="titledesc">
                <label>ID</label>
            </th>
            <td class="forminp forminp-text">
                <input type="text" name="facturaloperu_api_software_id" id="facturaloperu_api_software_id" value="<?php echo get_option('facturaloperu_api_software_id'); ?>" style="min-width: 400px" class="input-text regular-input">
            </td>
        </tr>
        <tr>
            <th class="titledesc">
                <label>PIN</label>
            </th>
            <td class="forminp forminp-text">
                <input type="text" name="facturaloperu_api_software_pin" id="facturaloperu_api_software_pin" value="<?php echo get_option('facturaloperu_api_software_pin'); ?>" style="min-width: 400px" class="input-text regular-input">
            </td>
        </tr>
    </table>

    <?php submit_button(); ?>

</form>