<?php
// Este archivo debe reemplazar el formulario actual del certificado

// Añadir estilos específicos para este formulario
?>

<h1>Envío de certificado</h1>

<div class="certificate-upload-container">
    <div class="certificate-upload-section">
        <h3>Opción 1: Cargar certificado .p12</h3>
        <p class="description">Seleccione el archivo de certificado .p12 para cargarlo automáticamente</p>
        
        <div class="file-upload-wrapper">
            <input type="file" id="certificate_file" name="certificate_file" accept=".p12" class="certificate-file-input" />
            <label for="certificate_file" class="file-upload-label">
                <span class="dashicons dashicons-upload"></span> Seleccionar archivo .p12
            </label>
            <div id="file_name_display" class="file-name-display"></div>
        </div>
        
        <div id="upload_status" class="upload-status" style="display: none;"></div>
    </div>

    <div class="certificate-manual-section">
        <h3>Opción 2: Ingresar certificado manualmente</h3>
        <form method="POST" action="options.php">
            <?php
                settings_fields('facturaloperu-api-config-certificate-group');
                do_settings_sections('facturaloperu-api-config-certificate-group');
            ?>
            <table class="form-table">
                <tr>
                    <th><label for="facturaloperu_api_certificate">Certificado (Base64)</label></th>
                    <td>
                        <textarea name="facturaloperu_api_certificate" id="facturaloperu_api_certificate" rows="5" style="width: 100%"><?php echo get_option('facturaloperu_api_certificate'); ?></textarea>
                        <p class="description">Certificado en formato Base64.</p>
                    </td>
                </tr>
                <tr>
                    <th><label for="facturaloperu_api_certificate_password">Contraseña</label></th>
                    <td>
                        <input type="password" name="facturaloperu_api_certificate_password" id="facturaloperu_api_certificate_password" value="<?php echo get_option('facturaloperu_api_certificate_password'); ?>" class="regular-text">
                        <p class="description">Contraseña del certificado.</p>
                    </td>
                </tr>
            </table>
            <?php submit_button('Guardar configuración'); ?>
        </form>
    </div>
</div>

<div class="certificate-action-section">
    <hr>
    <h3>Enviar configuración a la API</h3>
    <p class="description">Una vez que haya configurado el certificado y la contraseña, puede enviarlo a la API.</p>
    <button type="button" name="config_certificate" id="config_certificate" class="button button-primary">Enviar a API</button>
</div>

<div class="certificate-response-section">
    <form method="POST" action="options.php" style="display: block;">
        <?php
            settings_fields('facturaloperu-api-config-certificate-response-group');
            do_settings_sections('facturaloperu-api-config-certificate-response-group');
        ?>
        <h2>Resultado</h2>
        <textarea name="facturaloperu_api_certificate_response" id="facturaloperu_api_certificate_response" style="min-width: 700px; min-height: 250px;" readonly class="input-text regular-input"><?php echo get_option('facturaloperu_api_certificate_response'); ?></textarea>

        <input type="hidden" name="facturaloperu_api_config_token" id="facturaloperu_api_config_token" value="<?php echo get_option('facturaloperu_api_config_token'); ?>">

        <?php submit_button('Guardar respuesta'); ?>
    </form>
</div>

<style>
    .certificate-upload-container {
        display: flex;
        flex-wrap: wrap;
        gap: 30px;
        margin-bottom: 20px;
    }
    
    .certificate-upload-section, 
    .certificate-manual-section {
        flex: 1;
        min-width: 300px;
        padding: 20px;
        background: #fff;
        border: 1px solid #ddd;
        border-radius: 4px;
    }
    
    .file-upload-wrapper {
        margin: 15px 0;
    }
    
    .certificate-file-input {
        width: 0.1px;
        height: 0.1px;
        opacity: 0;
        overflow: hidden;
        position: absolute;
        z-index: -1;
    }
    
    .file-upload-label {
        display: inline-block;
        padding: 8px 16px;
        background-color: #0073aa;
        color: white;
        border-radius: 4px;
        cursor: pointer;
        transition: background-color 0.3s;
    }
    
    .file-upload-label:hover {
        background-color: #005a87;
    }
    
    .file-name-display {
        margin-top: 10px;
        font-style: italic;
        word-break: break-all;
    }
    
    .upload-status {
        margin-top: 10px;
        padding: 10px;
        border-radius: 4px;
    }
    
    .upload-status.success {
        background-color: #dff0d8;
        color: #3c763d;
    }
    
    .upload-status.error {
        background-color: #f2dede;
        color: #a94442;
    }
    
    .certificate-action-section,
    .certificate-response-section {
        margin-top: 20px;
        padding: 20px;
        background: #fff;
        border: 1px solid #ddd;
        border-radius: 4px;
    }
</style>