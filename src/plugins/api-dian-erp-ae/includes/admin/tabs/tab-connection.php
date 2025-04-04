<?php
/**
 * Pestaña de Conexión API
 * Este archivo contiene el formulario para configurar la conexión a la API DIAN
 */

// Verificar si hay conexión
$api_url = get_option('facturaloperu_api_config_url');
$api_token = get_option('facturaloperu_api_config_token');
$is_connected = !empty($api_url) && !empty($api_token);

// Mostrar estado de conexión
if ($is_connected) {
    echo '<div class="dian-notice success">';
    echo '<div class="dian-notice-icon"><span class="dashicons dashicons-yes-alt"></span></div>';
    echo '<div class="dian-notice-content">';
    echo '<h4>Conexión establecida</h4>';
    echo '<p>La conexión con la API DIAN está correctamente configurada.</p>';
    echo '</div>';
    echo '</div>';
} elseif (!empty($api_url) && empty($api_token)) {
    echo '<div class="dian-notice warning">';
    echo '<div class="dian-notice-icon"><span class="dashicons dashicons-warning"></span></div>';
    echo '<div class="dian-notice-content">';
    echo '<h4>Conexión parcial</h4>';
    echo '<p>La URL de la API está configurada, pero falta el token de autorización. Configure la empresa para obtener un token.</p>';
    echo '</div>';
    echo '</div>';
} else {
    echo '<div class="dian-notice info">';
    echo '<div class="dian-notice-icon"><span class="dashicons dashicons-info"></span></div>';
    echo '<div class="dian-notice-content">';
    echo '<h4>Conexión no configurada</h4>';
    echo '<p>Configure la URL de la API para comenzar a utilizar la facturación electrónica.</p>';
    echo '</div>';
    echo '</div>';
}
?>

<div class="dian-card">
    <div class="dian-card-header">
        <h2 class="dian-card-title">Configuración de Conexión API</h2>
    </div>
    <div class="dian-card-body">
        <p>En esta sección debe configurar los parámetros necesarios para conectarse con el servicio de la DIAN.</p>
        
        <form method="POST" action="options.php" class="dian-form">
            <?php
                settings_fields('facturaloperu-api-config-settings-group');
                do_settings_sections('facturaloperu-api-config-settings-group');
            ?>
            
            <table class="dian-form-table">
                <tr>
                    <th>
                        <label for="facturaloperu_api_config_url">URL de API <span class="required">*</span></label>
                    </th>
                    <td>
                        <input type="text" name="facturaloperu_api_config_url" id="facturaloperu_api_config_url" 
                            value="<?php echo esc_attr(get_option('facturaloperu_api_config_url')); ?>" 
                            class="regular-text" required>
                        <p class="description">Ejemplo: https://co-apidian2023.example.com/</p>
                    </td>
                </tr>
                
                <tr>
                    <th>
                        <label for="facturaloperu_api_config_token">Token de API</label>
                    </th>
                    <td>
                        <input type="text" name="facturaloperu_api_config_token" id="facturaloperu_api_config_token" 
                            value="<?php echo esc_attr(get_option('facturaloperu_api_config_token')); ?>" 
                            class="regular-text" <?php echo empty($api_token) ? 'readonly' : ''; ?>>
                        <?php if (empty($api_token)): ?>
                            <p class="description">El token se generará automáticamente al configurar la empresa.</p>
                        <?php else: ?>
                            <p class="description">Este es su token de autorización para acceder a la API.</p>
                        <?php endif; ?>
                    </td>
                </tr>
            </table>
            
            <div class="dian-form-actions">
                <button type="submit" class="dian-button">
                    <span class="dashicons dashicons-saved"></span> Guardar Configuración
                </button>
                
                <button type="button" id="test-connection" class="dian-button dian-button-secondary" 
                    data-api-action="api_test_connection" <?php echo empty($api_url) ? 'disabled' : ''; ?>>
                    <span class="dashicons dashicons-dashboard"></span> Probar Conexión
                </button>
            </div>
        </form>
    </div>
</div>

<?php if ($is_connected): ?>
<div class="dian-card">
    <div class="dian-card-header">
        <h2 class="dian-card-title">Pasos Siguientes</h2>
    </div>
    <div class="dian-card-body">
        <p>La conexión está configurada correctamente. A continuación, debe configurar los siguientes parámetros en este orden:</p>
        
        <ol class="dian-steps">
            <li>
                <strong>Configuración de Empresa</strong>
                <p>Configure los datos de su empresa para la facturación electrónica.</p>
                <a href="?page=facturaloperu-api-config-settings&tab=dian-config-company" class="button">Configurar Empresa</a>
            </li>
            <li>
                <strong>Configuración de Software</strong>
                <p>Configure los datos del software proporcionados por la DIAN.</p>
                <a href="?page=facturaloperu-api-config-settings&tab=dian-config-software" class="button">Configurar Software</a>
            </li>
            <li>
                <strong>Configuración de Certificado</strong>
                <p>Suba su certificado digital para la firma electrónica.</p>
                <a href="?page=facturaloperu-api-config-settings&tab=dian-config-certificate" class="button">Configurar Certificado</a>
            </li>
            <li>
                <strong>Configuración de Resolución</strong>
                <p>Configure los datos de la resolución emitida por la DIAN.</p>
                <a href="?page=facturaloperu-api-config-settings&tab=dian-config-resolution" class="button">Configurar Resolución</a>
            </li>
            <li>
                <strong>Inicialización</strong>
                <p>Finalice la configuración inicializando el sistema.</p>
                <a href="?page=facturaloperu-api-config-settings&tab=dian-config-initial-response" class="button">Inicializar</a>
            </li>
        </ol>
    </div>
</div>
<?php endif; ?>