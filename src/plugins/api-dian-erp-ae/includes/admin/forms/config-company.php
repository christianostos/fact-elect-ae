<h1>Configuración Empresa</h1>

<form method="POST" action="options.php">
    <?php
        settings_fields('facturaloperu-api-config-company-group');
        do_settings_sections('facturaloperu-api-config-company-group');
    ?>
    <table class="form-table">
        <tr valign="top">
            <th class="titledesc">
                <label>Tipo de documento</label>
            </th>
            <td class="forminp forminp-text">
                <select name="facturaloperu_api_config_document_type" id="facturaloperu_api_config_document_type" style="min-width: 400px;" class="input-text regular-input">
                    <option value="" disabled selected></option>
                    <option value="3" <?php echo get_option('facturaloperu_api_config_document_type') == '3' ? 'selected' : ''; ?> >Cedula de ciudadania</option>
                    <option value="5" <?php echo get_option('facturaloperu_api_config_document_type') == '5' ? 'selected' : ''; ?> >Tarjeta de extranjeria</option>
                    <option value="6" <?php echo get_option('facturaloperu_api_config_document_type') == '6' ? 'selected' : ''; ?> >NIT</option>
                    <option value="7" <?php echo get_option('facturaloperu_api_config_document_type') == '7' ? 'selected' : ''; ?> >Pasaporte</option>
                </select>
            </td>
        </tr>
        <tr>
            <th class="titledesc">
                <label>Numero de documento</label>
            </th>
            <td class="forminp forminp-text">
                <input type="text" name="facturaloperu_api_config_document" id="facturaloperu_api_config_document" value="<?php echo get_option('facturaloperu_api_config_document'); ?>" style="min-width: 400px" class="input-text regular-input">
            </td>
        </tr>
        <tr>
            <th class="titledesc">
                <label>DV</label>
            </th>
            <td class="forminp forminp-text">
                <input type="text" name="facturaloperu_api_config_dv" id="facturaloperu_api_config_dv" value="<?php echo get_option('facturaloperu_api_config_dv'); ?>" style="min-width: 400px" class="input-text regular-input">
            </td>
        </tr>
        <tr>
            <th class="titledesc">
                <label>Tipo de organización</label>
            </th>
            <td class="forminp forminp-text">
                <select name="facturaloperu_api_config_organization_type" id="facturaloperu_api_config_organization_type" style="min-width: 400px" class="input-text regular-input">
                    <option value="" disabled selected></option>
                    <option value="1" <?php echo get_option('facturaloperu_api_config_organization_type') == '1' ? 'selected' : ''; ?> >Persona Juridica</option>
                    <option value="2" <?php echo get_option('facturaloperu_api_config_organization_type') == '2' ? 'selected' : ''; ?> >Persona Natural</option>
                </select>
            </td>
        </tr>
        <tr>
            <th class="titledesc">
                <label>Regimen</label>
            </th>
            <td class="forminp forminp-text">
                <select name="facturaloperu_api_config_regime_type" id="facturaloperu_api_config_regime_type" style="min-width: 400px" class="input-text regular-input">
                    <option value="" disabled selected></option>
                    <option value="1" <?php echo get_option('facturaloperu_api_config_regime_type') == '1' ? 'selected' : ''; ?> >Responsable de IVA</option>
                    <option value="2" <?php echo get_option('facturaloperu_api_config_regime_type') == '2' ? 'selected' : ''; ?> >No Responsable de IVA</option>
                </select>
            </td>
        </tr>
        <tr>
            <th class="titledesc">
                <label>Tipo de Responsabilidad</label>
            </th>
            <td class="forminp forminp-text">
                <select name="facturaloperu_api_config_liability_type" id="facturaloperu_api_config_liability_type" style="min-width: 400px" class="input-text regular-input">
                    <option value="" disabled selected></option>
                    <option value="7" <?php echo get_option('facturaloperu_api_config_liability_type') == '7' ? 'selected' : ''; ?> >Gran contribuyente</option>
                    <option value="9" <?php echo get_option('facturaloperu_api_config_liability_type') == '9' ? 'selected' : ''; ?> >Autorretenedor</option>
                    <option value="14" <?php echo get_option('facturaloperu_api_config_liability_type') == '14' ? 'selected' : ''; ?> >Agente de retención</option>
                    <option value="112" <?php echo get_option('facturaloperu_api_config_liability_type') == '112' ? 'selected' : ''; ?> >Regimen simple</option>
                    <option value="117" <?php echo get_option('facturaloperu_api_config_liability_type') == '117' ? 'selected' : ''; ?> >No responsable</option>
                </select>
            </td>
        </tr>
        <tr>
            <th class="titledesc">
                <label>Nombre</label>
            </th>
            <td class="forminp forminp-text">
                <input type="text" name="facturaloperu_api_config_business_name" id="facturaloperu_api_config_business_name" value="<?php echo get_option('facturaloperu_api_config_business_name'); ?>" style="min-width: 400px" class="input-text regular-input">
            </td>
        </tr>
        <tr>
            <th class="titledesc">
                <label>Registro Mercantil</label>
            </th>
            <td class="forminp forminp-text">
                <input type="text" name="facturaloperu_api_config_merchant_registration" id="facturaloperu_api_config_merchant_registration" value="<?php echo get_option('facturaloperu_api_config_merchant_registration'); ?>" style="min-width: 400px" class="input-text regular-input">
            </td>
        </tr>
        <tr>
            <th class="titledesc">
                <label>Municipalidad</label>
            </th>
            <td class="forminp forminp-text">
                <select name="facturaloperu_api_config_municipality" id="facturaloperu_api_config_municipality" style="min-width: 400px">
                    <?php
                        include('location_options.php');
                    ?>
                </select>
            </td>
        </tr>
        <tr>
            <th class="titledesc">
                <label>Dirección</label>
            </th>
            <td class="forminp forminp-text">
                <input type="text" name="facturaloperu_api_config_business_address" id="facturaloperu_api_config_business_address" value="<?php echo get_option('facturaloperu_api_config_business_address'); ?>" style="min-width: 400px" class="input-text regular-input">
            </td>
        </tr>
        <tr>
            <th class="titledesc">
                <label>Telefono</label>
            </th>
            <td class="forminp forminp-text">
                <input type="text" name="facturaloperu_api_config_business_phone" id="facturaloperu_api_config_business_phone" value="<?php echo get_option('facturaloperu_api_config_business_phone'); ?>" style="min-width: 400px" class="input-text regular-input">
            </td>
        </tr>
        <tr>
            <th class="titledesc">
                <label>Email</label>
            </th>
            <td class="forminp forminp-text">
                <input type="text" name="facturaloperu_api_config_business_email" id="facturaloperu_api_config_business_email" value="<?php echo get_option('facturaloperu_api_config_business_email'); ?>" style="min-width: 400px" class="input-text regular-input">
            </td>
        </tr>
    </table>

    <?php submit_button(); ?>

</form>