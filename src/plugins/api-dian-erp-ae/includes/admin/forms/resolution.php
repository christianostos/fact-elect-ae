<h1>Configuración de Resolución</h1>

<form method="POST" action="options.php">
    <?php
        settings_fields('facturaloperu-api-config-resolution-group');
        do_settings_sections('facturaloperu-api-config-resolution-group');
    ?>
    <input type="text" name="facturaloperu_api_resolution_document_type" id="facturaloperu_api_resolution_document_type" value="1" style="min-width: 400px" hidden>
    <table class="form-table">
        <tr valign="top">
            <th class="titledesc">
                <label>Resolucion</label>
            </th>
            <td class="forminp forminp-text">
                <input type="text" name="facturaloperu_api_resolution" id="facturaloperu_api_resolution" value="<?php echo get_option('facturaloperu_api_resolution'); ?>" style="min-width: 400px" class="input-text regular-input">
            </td>
        </tr>
        <tr>
            <th class="titledesc">
                <label>Prefijo</label>
            </th>
            <td class="forminp forminp-text">
                <input type="text" name="facturaloperu_api_resolution_prefix" id="facturaloperu_api_resolution_prefix" value="<?php echo get_option('facturaloperu_api_resolution_prefix'); ?>" style="min-width: 400px" class="input-text regular-input">
            </td>
        </tr>
        <tr>
            <th class="titledesc">
                <label>Fecha de Resolución (YYYY-mm-dd)</label>
            </th>
            <td class="forminp forminp-text">
                <input type="text" name="facturaloperu_api_resolution_date" id="facturaloperu_api_resolution_date" 
                       value="<?php echo get_option('facturaloperu_api_resolution_date'); ?>" 
                       style="min-width: 400px" class="input-text regular-input">
                <p class="description">Debe ser una fecha válida en formato YYYY-mm-dd. Por ejemplo: 2019-01-19</p>
            </td>
        </tr>
        <tr>
            <th class="titledesc">
                <label>Llave tecnica</label>
            </th>
            <td class="forminp forminp-text">
                <input type="text" name="facturaloperu_api_resolution_technical_key" id="facturaloperu_api_resolution_technical_key" value="<?php echo get_option('facturaloperu_api_resolution_technical_key'); ?>" style="min-width: 400px" class="input-text regular-input">
            </td>
        </tr>
        <tr>
            <th class="titledesc">
                <label>Correlativo de inicio</label>
            </th>
            <td class="forminp forminp-text">
                <input type="text" name="facturaloperu_api_resolution_number_from" id="facturaloperu_api_resolution_number_from" value="<?php echo get_option('facturaloperu_api_resolution_number_from'); ?>" style="min-width: 400px" class="input-text regular-input">
            </td>
        </tr>
        <tr>
            <th class="titledesc">
                <label>Correlativo final</label>
            </th>
            <td class="forminp forminp-text">
                <input type="text" name="facturaloperu_api_resolution_number_to" id="facturaloperu_api_resolution_number_to" value="<?php echo get_option('facturaloperu_api_resolution_number_to'); ?>" style="min-width: 400px" class="input-text regular-input">
            </td>
        </tr>
        <tr>
            <th class="titledesc">
                <label>Generado hasta</label>
            </th>
            <td class="forminp forminp-text">
                <input type="text" name="facturaloperu_api_resolution_generated_date" id="facturaloperu_api_resolution_generated_date" value="<?php echo get_option('facturaloperu_api_resolution_generated_date'); ?>" style="min-width: 400px" class="input-text regular-input">
            </td>
        </tr>
        <tr>
            <th class="titledesc">
                <label>Fecha de inicio (YYYY-mm-dd)</label>
            </th>
            <td class="forminp forminp-text">
                <input type="text" name="facturaloperu_api_resolution_date_start" id="facturaloperu_api_resolution_date_start" value="<?php echo get_option('facturaloperu_api_resolution_date_start'); ?>" style="min-width: 400px" class="input-text regular-input">
            </td>
        </tr>
        <tr>
            <th class="titledesc">
                <label>Fecha final (YYYY-mm-dd)</label>
            </th>
            <td class="forminp forminp-text">
                <input type="text" name="facturaloperu_api_resolution_date_stop" id="facturaloperu_api_resolution_date_stop" value="<?php echo get_option('facturaloperu_api_resolution_date_stop'); ?>" style="min-width: 400px" class="input-text regular-input">
            </td>
        </tr>
        <tr>
            <th class="titledesc">
                <label>Documentos generados</label>
            </th>
            <td class="forminp forminp-text">
                <input type="text" name="facturaloperu_api_initial_docs" id="facturaloperu_api_initial_docs" value="<?php echo get_option('facturaloperu_api_initial_docs'); ?>" style="min-width: 400px" class="input-text regular-input">
            </td>
        </tr>
        <?php
            // if(get_option('facturaloperu_api_resolution_number_current') != ''){
        ?>
            <tr>
                <th class="titledesc">
                    <label>Numero de resoluión actual</label>
                </th>
                <td class="forminp forminp-text">
                    <input type="text" name="facturaloperu_api_resolution_number_current" id="facturaloperu_api_resolution_number_current" value="<?php echo get_option('facturaloperu_api_resolution_number_current'); ?>" style="min-width: 400px" class="input-text regular-input">
                </td>
            </tr>
        <?php
        // }
        ?>
    </table>

    <?php submit_button(); ?>

</form>