<?php
/**
 * DIAN ERP AE - Panel de Configuración
 * Interfaz de administración modernizada con diseño intuitivo
 */

// Añade página al menú administrador
function api_config_menu() {
    add_submenu_page(
        'erp-ae',
        'Ajustes API DIAN',
        'Facturas DIAN',
        'administrator',
        'facturaloperu-api-config-settings',
        'facturaloperu_api_config_page_settings'
    );
}
add_action('admin_menu', 'api_config_menu');

// Crear menú principal si no existe
function add_admin_page() {
    add_menu_page(
        'Ajustes API DIAN',
        'Facturas DIAN', 
        'manage_options',
        'facturaloperu-api',
        'facturaloperu_api_config_page_settings',
        'dashicons-money-alt',
        58
    );
}

// Enqueue scripts and styles for the admin page
function facturaloperu_api_admin_scripts($hook) {
    // Only load on our plugin pages
    if (strpos($hook, 'facturaloperu-api') === false) {
        return;
    }
    
    // Register and enqueue our custom CSS
    wp_register_style('dian-admin-styles', plugin_dir_url(__FILE__) . '../../assets/css/admin-styles.css', [], '1.0.0');
    wp_enqueue_style('dian-admin-styles');
    
    // Register and enqueue our custom JS
    wp_register_script('dian-admin-script', plugin_dir_url(__FILE__) . '../../assets/js/admin-script.js', ['jquery'], '1.0.0', true);
    
    // Pass data to our script
    wp_localize_script('dian-admin-script', 'dianAdminData', [
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('dian_admin_nonce'),
        'apiUrl' => get_option('facturaloperu_api_config_url', ''),
        'apiConnected' => !empty(get_option('facturaloperu_api_config_token', '')),
    ]);
    
    wp_enqueue_script('dian-admin-script');
}
add_action('admin_enqueue_scripts', 'facturaloperu_api_admin_scripts');

// Página principal con formulario de opciones modernizado
function facturaloperu_api_config_page_settings() {
    $default_tab = null;
    $tab = isset($_GET['tab']) ? $_GET['tab'] : $default_tab;
    $is_production = get_option('facturaloperu_api_config_production') === 'true';
    
    // Comprobar estado de conexión
    $token = get_option('facturaloperu_api_config_token');
    $is_connected = !empty($token);
    $connection_status = $is_connected ? 
        '<span class="api-status-badge connected"><span class="dashicons dashicons-yes-alt"></span>Conectado</span>' : 
        '<span class="api-status-badge disconnected"><span class="dashicons dashicons-warning"></span>Desconectado</span>';
    
    ?>
    <div class="wrap dian-admin-container">
        <div class="dian-header">
            <h1>Configuración de API DIAN <?php echo $connection_status; ?></h1>
            <p>Configure la conexión y parámetros para la facturación electrónica DIAN.</p>
        </div>
        
        <nav class="dian-tabs-navigation">
            <a href="?page=facturaloperu-api-config-settings" class="dian-tab <?php echo ($tab === null) ? 'active' : ''; ?>" data-tab="general">
                <span class="dashicons dashicons-admin-settings"></span> General
            </a>
            <a href="?page=facturaloperu-api-config-settings&tab=conection" class="dian-tab <?php echo ($tab === 'conection') ? 'active' : ''; ?>" data-tab="conection">
                <span class="dashicons dashicons-admin-links"></span> Conexión
            </a>
            <a href="?page=facturaloperu-api-config-settings&tab=dian-config-company" class="dian-tab <?php echo ($tab === 'dian-config-company') ? 'active' : ''; ?>" data-tab="company">
                <span class="dashicons dashicons-building"></span> Empresa
            </a>
            <a href="?page=facturaloperu-api-config-settings&tab=dian-config-company-response" class="dian-tab <?php echo ($tab === 'dian-config-company-response') ? 'active' : ''; ?>" data-tab="company-response">
                <span class="dashicons dashicons-feedback"></span> Empresa <small>(respuesta)</small>
            </a>
            <a href="?page=facturaloperu-api-config-settings&tab=dian-config-software" class="dian-tab <?php echo ($tab === 'dian-config-software') ? 'active' : ''; ?>" data-tab="software">
                <span class="dashicons dashicons-admin-tools"></span> Software
            </a>
            <a href="?page=facturaloperu-api-config-settings&tab=dian-config-software-response" class="dian-tab <?php echo ($tab === 'dian-config-software-response') ? 'active' : ''; ?>" data-tab="software-response">
                <span class="dashicons dashicons-feedback"></span> Software <small>(respuesta)</small>
            </a>
            <a href="?page=facturaloperu-api-config-settings&tab=dian-config-certificate" class="dian-tab <?php echo ($tab === 'dian-config-certificate') ? 'active' : ''; ?>" data-tab="certificate">
                <span class="dashicons dashicons-lock"></span> Certificado
            </a>
            <a href="?page=facturaloperu-api-config-settings&tab=dian-config-certificate-response" class="dian-tab <?php echo ($tab === 'dian-config-certificate-response') ? 'active' : ''; ?>" data-tab="certificate-response">
                <span class="dashicons dashicons-feedback"></span> Certificado <small>(respuesta)</small>
            </a>
            <a href="?page=facturaloperu-api-config-settings&tab=dian-config-resolution" class="dian-tab <?php echo ($tab === 'dian-config-resolution') ? 'active' : ''; ?>" data-tab="resolution">
                <span class="dashicons dashicons-clipboard"></span> Resolución
            </a>
            <a href="?page=facturaloperu-api-config-settings&tab=dian-config-resolution-response" class="dian-tab <?php echo ($tab === 'dian-config-resolution-response') ? 'active' : ''; ?>" data-tab="resolution-response">
                <span class="dashicons dashicons-feedback"></span> Resolución <small>(respuesta)</small>
            </a>
            <a href="?page=facturaloperu-api-config-settings&tab=dian-config-initial-response" class="dian-tab <?php echo ($tab === 'dian-config-initial-response') ? 'active' : ''; ?>" data-tab="initial-response">
                <span class="dashicons dashicons-update"></span> Inicializar
            </a>
            <?php if ($is_production): ?>
            <a href="?page=facturaloperu-api-config-settings&tab=dian-config-environment-response" class="dian-tab <?php echo ($tab === 'dian-config-environment-response') ? 'active' : ''; ?>" data-tab="environment-response">
                <span class="dashicons dashicons-admin-site"></span> Entorno
            </a>
            <a href="?page=facturaloperu-api-config-settings&tab=dian-numbering-range-response" class="dian-tab <?php echo ($tab === 'dian-numbering-range-response') ? 'active' : ''; ?>" data-tab="numbering-range-response">
                <span class="dashicons dashicons-editor-ol"></span> Resolución <small>(obtener)</small>
            </a>
            <?php endif; ?>
        </nav>

        <div class="dian-tab-content">
            <?php
            switch ($tab) {
                case 'conection':
                    include_once('tabs/tab-connection.php');
                    break;
                    
                case 'dian-config-company':
                    include_once('forms/config-company.php');
                    break;
                    
                case 'dian-config-company-response':
                    include_once('api/company-response.php');
                    break;
                    
                case 'dian-config-software':
                    include_once('forms/config-software.php');
                    break;
                    
                case 'dian-config-software-response':
                    include_once('api/software-response.php');
                    break;
                    
                case 'dian-config-certificate':
                    include_once('forms/certificate.php');
                    break;
                    
                case 'dian-config-certificate-response':
                    include_once('api/certificate.php');
                    break;
                    
                case 'dian-config-resolution':
                    include_once('forms/resolution.php');
                    break;
                    
                case 'dian-config-resolution-response':
                    include_once('api/resolution-response.php');
                    break;
                    
                case 'dian-config-initial-response':
                    include_once('api/initial-response.php');
                    break;
                    
                case 'dian-config-environment-response':
                    include_once('api/environment.php');
                    break;
                    
                case 'dian-numbering-range-response':
                    include_once('api/numbering-ranges.php');
                    break;
                    
                default:
                    include_once('tabs/tab-general.php');
                    break;
            }
            ?>
        </div>
    </div>
    <?php
}

/**
 * Contenido de pestaña de Conexión
 * Esta función genera el HTML para la pestaña de conexión
 */
function dian_render_connection_tab() {
    ?>
    <div class="dian-card">
        <div class="dian-card-header">
            <h2 class="dian-card-title">Configuración de Conexión API</h2>
        </div>
        <div class="dian-card-body">
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
                                required>
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
                                placeholder="El token se obtendrá al configurar la empresa">
                            <p class="description">Este campo se actualizará automáticamente al configurar la empresa.</p>
                        </td>
                    </tr>
                </table>
                
                <div class="dian-form-actions">
                    <button type="submit" class="button button-primary dian-button">
                        <span class="dashicons dashicons-saved"></span> Guardar Configuración
                    </button>
                    <button type="button" class="button dian-button-secondary" id="test-connection" data-api-action="api_test_connection">
                        <span class="dashicons dashicons-yes-alt"></span> Probar Conexión
                    </button>
                </div>
            </form>
        </div>
    </div>
    <?php
}

/**
 * Contenido de pestaña General
 * Esta función genera el HTML para la pestaña general
 */
function dian_render_general_tab() {
    ?>
    <div class="dian-card">
        <div class="dian-card-header">
            <h2 class="dian-card-title">Configuración General</h2>
        </div>
        <div class="dian-card-body">
            <form method="POST" action="options.php" class="dian-form">
                <?php
                    settings_fields('facturaloperu-api-config-generals-group');
                    do_settings_sections('facturaloperu-api-config-generals-group');
                ?>
                <table class="dian-form-table">
                    <tr>
                        <th>
                            <label>Modo de Funcionamiento</label>
                        </th>
                        <td>
                            <fieldset>
                                <label>
                                    <input type="radio" name="facturaloperu_api_config_production" value="true"
                                        <?php checked('true', get_option('facturaloperu_api_config_production')); ?>>
                                    <span class="dian-radio-label">Producción</span>
                                </label>
                                <br>
                                <label>
                                    <input type="radio" name="facturaloperu_api_config_production" value="false"
                                        <?php checked('false', get_option('facturaloperu_api_config_production')); ?>>
                                    <span class="dian-radio-label">Pruebas</span>
                                </label>
                                <p class="description">Seleccione el entorno en el que operará la facturación electrónica.</p>
                            </fieldset>
                        </td>
                    </tr>
                    <tr>
                        <th>
                            <label>Envío de Correo Electrónico</label>
                        </th>
                        <td>
                            <fieldset>
                                <label>
                                    <input type="radio" name="facturaloperu_api_config_send_email" value="true"
                                        <?php checked('true', get_option('facturaloperu_api_config_send_email')); ?>>
                                    <span class="dian-radio-label">Activado</span>
                                </label>
                                <br>
                                <label>
                                    <input type="radio" name="facturaloperu_api_config_send_email" value="false"
                                        <?php checked('false', get_option('facturaloperu_api_config_send_email')); ?>>
                                    <span class="dian-radio-label">Desactivado</span>
                                </label>
                                <p class="description">¿Desea enviar automáticamente las facturas por correo electrónico a los clientes?</p>
                            </fieldset>
                        </td>
                    </tr>
                    <tr>
                        <th>
                            <label for="facturaloperu_api_config_testsetid">TestSetId</label>
                        </th>
                        <td>
                            <input type="text" name="facturaloperu_api_config_testsetid" id="facturaloperu_api_config_testsetid" 
                                value="<?php echo esc_attr(get_option('facturaloperu_api_config_testsetid')); ?>">
                            <p class="description">Identificador de conjunto de pruebas para el entorno de pruebas.</p>
                        </td>
                    </tr>
                </table>
                
                <div class="dian-form-actions">
                    <button type="submit" class="button button-primary dian-button">
                        <span class="dashicons dashicons-saved"></span> Guardar Configuración
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <div class="dian-card">
        <div class="dian-card-header">
            <h2 class="dian-card-title">Estado de Configuración</h2>
        </div>
        <div class="dian-card-body">
            <table class="widefat" style="margin-top: 10px;">
                <thead>
                    <tr>
                        <th>Sección</th>
                        <th>Estado</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>Conexión API</strong></td>
                        <td>
                            <?php if (get_option('facturaloperu_api_config_url') && get_option('facturaloperu_api_config_token')): ?>
                                <span class="dashicons dashicons-yes-alt" style="color: green;"></span> Configurado
                            <?php else: ?>
                                <span class="dashicons dashicons-warning" style="color: orange;"></span> Pendiente
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="?page=facturaloperu-api-config-settings&tab=conection" class="button button-small">Configurar</a>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Empresa</strong></td>
                        <td>
                            <?php if (get_option('facturaloperu_api_config_response')): ?>
                                <span class="dashicons dashicons-yes-alt" style="color: green;"></span> Configurado
                            <?php else: ?>
                                <span class="dashicons dashicons-warning" style="color: orange;"></span> Pendiente
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="?page=facturaloperu-api-config-settings&tab=dian-config-company" class="button button-small">Configurar</a>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Software</strong></td>
                        <td>
                            <?php if (get_option('facturaloperu_api_software_response')): ?>
                                <span class="dashicons dashicons-yes-alt" style="color: green;"></span> Configurado
                            <?php else: ?>
                                <span class="dashicons dashicons-warning" style="color: orange;"></span> Pendiente
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="?page=facturaloperu-api-config-settings&tab=dian-config-software" class="button button-small">Configurar</a>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Certificado</strong></td>
                        <td>
                            <?php if (get_option('facturaloperu_api_certificate_response')): ?>
                                <span class="dashicons dashicons-yes-alt" style="color: green;"></span> Configurado
                            <?php else: ?>
                                <span class="dashicons dashicons-warning" style="color: orange;"></span> Pendiente
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="?page=facturaloperu-api-config-settings&tab=dian-config-certificate" class="button button-small">Configurar</a>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Resolución</strong></td>
                        <td>
                            <?php if (get_option('facturaloperu_api_resolution_response')): ?>
                                <span class="dashicons dashicons-yes-alt" style="color: green;"></span> Configurado
                            <?php else: ?>
                                <span class="dashicons dashicons-warning" style="color: orange;"></span> Pendiente
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="?page=facturaloperu-api-config-settings&tab=dian-config-resolution" class="button button-small">Configurar</a>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Inicialización</strong></td>
                        <td>
                            <?php if (get_option('facturaloperu_api_initial_response')): ?>
                                <span class="dashicons dashicons-yes-alt" style="color: green;"></span> Completado
                            <?php else: ?>
                                <span class="dashicons dashicons-warning" style="color: orange;"></span> Pendiente
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="?page=facturaloperu-api-config-settings&tab=dian-config-initial-response" class="button button-small">Inicializar</a>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <?php
}

// Incluir archivo de registro de configuraciones
include('register-settings.php');

// Incluir archivo de acciones
include('actions.php');