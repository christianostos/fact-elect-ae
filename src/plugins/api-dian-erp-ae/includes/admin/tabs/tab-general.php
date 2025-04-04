<?php
/**
 * Pestaña General
 * Este archivo contiene el formulario para la configuración general del plugin
 */

// Determinar el progreso de configuración
$total_steps = 5;
$completed_steps = 0;

if (!empty(get_option('facturaloperu_api_config_url'))) $completed_steps++;
if (!empty(get_option('facturaloperu_api_config_response'))) $completed_steps++;
if (!empty(get_option('facturaloperu_api_software_response'))) $completed_steps++;
if (!empty(get_option('facturaloperu_api_certificate_response'))) $completed_steps++;
if (!empty(get_option('facturaloperu_api_resolution_response'))) $completed_steps++;

$progress_percentage = ($completed_steps / $total_steps) * 100;
?>

<div class="dian-card">
    <div class="dian-card-header">
        <h2 class="dian-card-title">Progreso de Configuración</h2>
    </div>
    <div class="dian-card-body">
        <div class="dian-progress">
            <div class="dian-progress-bar" style="width: <?php echo $progress_percentage; ?>%;"></div>
        </div>
        <p><?php echo $completed_steps; ?> de <?php echo $total_steps; ?> pasos completados</p>
        
        <?php if ($completed_steps < $total_steps): ?>
            <div class="dian-notice info">
                <div class="dian-notice-icon"><span class="dashicons dashicons-info"></span></div>
                <div class="dian-notice-content">
                    <h4>Configuración incompleta</h4>
                    <p>Su configuración de DIAN aún no está completa. Por favor, siga los pasos pendientes.</p>
                </div>
            </div>
        <?php else: ?>
            <div class="dian-notice success">
                <div class="dian-notice-icon"><span class="dashicons dashicons-yes-alt"></span></div>
                <div class="dian-notice-content">
                    <h4>Configuración completa</h4>
                    <p>¡Felicidades! Su configuración de DIAN está completa y lista para generar facturas electrónicas.</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

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
                        <label>Modo de Operación</label>
                    </th>
                    <td>
                        <fieldset>
                            <label class="dian-radio">
                                <input type="radio" name="facturaloperu_api_config_production" value="true"
                                    <?php checked('true', get_option('facturaloperu_api_config_production')); ?>>
                                <span>Producción</span>
                            </label>
                            <br>
                            <label class="dian-radio">
                                <input type="radio" name="facturaloperu_api_config_production" value="false"
                                    <?php checked('false', get_option('facturaloperu_api_config_production')); ?>>
                                <span>Pruebas</span>
                            </label>
                            <p class="description">Configure el entorno en el que operará el sistema de facturación.</p>
                        </fieldset>
                    </td>
                </tr>
                
                <tr>
                    <th>
                        <label>Envío de Correo Electrónico</label>
                    </th>
                    <td>
                        <fieldset>
                            <label class="dian-radio">
                                <input type="radio" name="facturaloperu_api_config_send_email" value="true"
                                    <?php checked('true', get_option('facturaloperu_api_config_send_email')); ?>>
                                <span>Activado</span>
                            </label>
                            <br>
                            <label class="dian-radio">
                                <input type="radio" name="facturaloperu_api_config_send_email" value="false"
                                    <?php checked('false', get_option('facturaloperu_api_config_send_email')); ?>>
                                <span>Desactivado</span>
                            </label>
                            <p class="description">Decida si desea enviar automáticamente un correo electrónico a los clientes con su factura.</p>
                        </fieldset>
                    </td>
                </tr>
                
                <tr>
                    <th>
                        <label for="facturaloperu_api_config_testsetid">TestSetId</label>
                    </th>
                    <td>
                        <input type="text" name="facturaloperu_api_config_testsetid" id="facturaloperu_api_config_testsetid" 
                            value="<?php echo esc_attr(get_option('facturaloperu_api_config_testsetid')); ?>" 
                            class="regular-text">
                        <p class="description">Identificador de conjunto de pruebas para el entorno de pruebas.</p>
                    </td>
                </tr>
            </table>
            
            <div class="dian-form-actions">
                <button type="submit" class="dian-button">
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
        <table class="widefat" style="border-collapse: collapse; width: 100%;">
            <thead>
                <tr>
                    <th style="padding: 10px; border-bottom: 1px solid #e1e1e1;">Sección</th>
                    <th style="padding: 10px; border-bottom: 1px solid #e1e1e1;">Estado</th>
                    <th style="padding: 10px; border-bottom: 1px solid #e1e1e1;">Acción</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="padding: 12px; border-bottom: 1px solid #f1f1f1;"><strong>Conexión API</strong></td>
                    <td style="padding: 12px; border-bottom: 1px solid #f1f1f1;">
                        <?php if (!empty(get_option('facturaloperu_api_config_url'))): ?>
                            <span class="dashicons dashicons-yes-alt" style="color: #00a32a;"></span> Configurado
                        <?php else: ?>
                            <span class="dashicons dashicons-warning" style="color: #dba617;"></span> Pendiente
                        <?php endif; ?>
                    </td>
                    <td style="padding: 12px; border-bottom: 1px solid #f1f1f1;">
                        <a href="?page=facturaloperu-api-config-settings&tab=conection" class="dian-button dian-button-small">
                            <?php echo !empty(get_option('facturaloperu_api_config_url')) ? 'Editar' : 'Configurar'; ?>
                        </a>
                    </td>
                </tr>
                
                <tr>
                    <td style="padding: 12px; border-bottom: 1px solid #f1f1f1;"><strong>Datos de Empresa</strong></td>
                    <td style="padding: 12px; border-bottom: 1px solid #f1f1f1;">
                        <?php if (!empty(get_option('facturaloperu_api_config_response'))): ?>
                            <span class="dashicons dashicons-yes-alt" style="color: #00a32a;"></span> Configurado
                        <?php else: ?>
                            <span class="dashicons dashicons-warning" style="color: #dba617;"></span> Pendiente
                        <?php endif; ?>
                    </td>
                    <td style="padding: 12px; border-bottom: 1px solid #f1f1f1;">
                        <a href="?page=facturaloperu-api-config-settings&tab=dian-config-company" class="dian-button dian-button-small">
                            <?php echo !empty(get_option('facturaloperu_api_config_response')) ? 'Editar' : 'Configurar'; ?>
                        </a>
                    </td>
                </tr>
                
                <tr>
                    <td style="padding: 12px; border-bottom: 1px solid #f1f1f1;"><strong>Software</strong></td>
                    <td style="padding: 12px; border-bottom: 1px solid #f1f1f1;">
                        <?php if (!empty(get_option('facturaloperu_api_software_response'))): ?>
                            <span class="dashicons dashicons-yes-alt" style="color: #00a32a;"></span> Configurado
                        <?php else: ?>
                            <span class="dashicons dashicons-warning" style="color: #dba617;"></span> Pendiente
                        <?php endif; ?>
                    </td>
                    <td style="padding: 12px; border-bottom: 1px solid #f1f1f1;">
                        <a href="?page=facturaloperu-api-config-settings&tab=dian-config-software" class="dian-button dian-button-small">
                            <?php echo !empty(get_option('facturaloperu_api_software_response')) ? 'Editar' : 'Configurar'; ?>
                        </a>
                    </td>
                </tr>
                
                <tr>
                    <td style="padding: 12px; border-bottom: 1px solid #f1f1f1;"><strong>Certificado</strong></td>
                    <td style="padding: 12px; border-bottom: 1px solid #f1f1f1;">
                        <?php if (!empty(get_option('facturaloperu_api_certificate_response'))): ?>
                            <span class="dashicons dashicons-yes-alt" style="color: #00a32a;"></span> Configurado
                        <?php else: ?>
                            <span class="dashicons dashicons-warning" style="color: #dba617;"></span> Pendiente
                        <?php endif; ?>
                    </td>
                    <td style="padding: 12px; border-bottom: 1px solid #f1f1f1;">
                        <a href="?page=facturaloperu-api-config-settings&tab=dian-config-certificate" class="dian-button dian-button-small">
                            <?php echo !empty(get_option('facturaloperu_api_certificate_response')) ? 'Editar' : 'Configurar'; ?>
                        </a>
                    </td>
                </tr>
                
                <tr>
                    <td style="padding: 12px; border-bottom: 1px solid #f1f1f1;"><strong>Resolución</strong></td>
                    <td style="padding: 12px; border-bottom: 1px solid #f1f1f1;">
                        <?php if (!empty(get_option('facturaloperu_api_resolution_response'))): ?>
                            <span class="dashicons dashicons-yes-alt" style="color: #00a32a;"></span> Configurado
                        <?php else: ?>
                            <span class="dashicons dashicons-warning" style="color: #dba617;"></span> Pendiente
                        <?php endif; ?>
                    </td>
                    <td style="padding: 12px; border-bottom: 1px solid #f1f1f1;">
                        <a href="?page=facturaloperu-api-config-settings&tab=dian-config-resolution" class="dian-button dian-button-small">
                            <?php echo !empty(get_option('facturaloperu_api_resolution_response')) ? 'Editar' : 'Configurar'; ?>
                        </a>
                    </td>
                </tr>
                
                <tr>
                    <td style="padding: 12px;"><strong>Inicialización</strong></td>
                    <td style="padding: 12px;">
                        <?php if (!empty(get_option('facturaloperu_api_initial_response'))): ?>
                            <span class="dashicons dashicons-yes-alt" style="color: #00a32a;"></span> Completado
                        <?php else: ?>
                            <span class="dashicons dashicons-warning" style="color: #dba617;"></span> Pendiente
                        <?php endif; ?>
                    </td>
                    <td style="padding: 12px;">
                        <a href="?page=facturaloperu-api-config-settings&tab=dian-config-initial-response" class="dian-button dian-button-small">
                            <?php echo !empty(get_option('facturaloperu_api_initial_response')) ? 'Ver Detalles' : 'Inicializar'; ?>
                        </a>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<?php if ($completed_steps == $total_steps): ?>
<div class="dian-card">
    <div class="dian-card-header">
        <h2 class="dian-card-title">¿Qué sigue?</h2>
    </div>
    <div class="dian-card-body">
        <p>Su sistema está completamente configurado y listo para generar facturas electrónicas. A continuación puede:</p>
        
        <ul style="margin-left: 20px; list-style-type: disc;">
            <li>Crear una orden en WooCommerce y completarla para generar automáticamente una factura electrónica.</li>
            <li>Acceder a las órdenes existentes y enviar facturas manualmente desde la sección de detalles de la orden.</li>
            <li>Revisar la documentación para entender cómo integrar la facturación electrónica con su flujo de trabajo.</li>
        </ul>
        
        <p style="margin-top: 15px;">Si necesita más información, consulte la <a href="https://docs.google.com/document/d/1JqHw1VQKMDwWZvVcvfPEnmL0sWw7DjHyny2PYaXUYdQ/edit?usp=sharing" target="_blank">documentación oficial</a>.</p>
    </div>
</div>
<?php endif; ?>

<?php
// Mostrar guía rápida para usuarios nuevos
$plugin_first_activation = get_option('facturaloperu_api_first_activation', false);
if (!$plugin_first_activation):
    // Marcar que el plugin ya ha sido visto
    update_option('facturaloperu_api_first_activation', true);
?>
<div class="dian-card">
    <div class="dian-card-header">
        <h2 class="dian-card-title">Guía Rápida de Configuración</h2>
    </div>
    <div class="dian-card-body">
        <div class="dian-welcome-message">
            <p><strong>¡Bienvenido al sistema de Facturación Electrónica DIAN!</strong></p>
            <p>Para comenzar a usar el sistema, siga estos pasos en orden:</p>
        </div>
        
        <ol class="dian-steps-guide">
            <li>
                <h4><span class="dashicons dashicons-admin-links"></span> Configure la conexión a la API</h4>
                <p>Establezca la URL de la API DIAN para habilitar la comunicación con los servicios de facturación.</p>
                <a href="?page=facturaloperu-api-config-settings&tab=conection" class="dian-button dian-button-small">Ir a Conexión API</a>
            </li>
            
            <li>
                <h4><span class="dashicons dashicons-building"></span> Configure los datos de su empresa</h4>
                <p>Ingrese los datos fiscales y de contacto de su empresa para la facturación electrónica.</p>
                <a href="?page=facturaloperu-api-config-settings&tab=dian-config-company" class="dian-button dian-button-small">Ir a Empresa</a>
            </li>
            
            <li>
                <h4><span class="dashicons dashicons-admin-tools"></span> Configure los datos del software</h4>
                <p>Configure el ID y PIN del software proporcionados por la DIAN.</p>
                <a href="?page=facturaloperu-api-config-settings&tab=dian-config-software" class="dian-button dian-button-small">Ir a Software</a>
            </li>
            
            <li>
                <h4><span class="dashicons dashicons-lock"></span> Configure el certificado digital</span></h4>
                <p>Suba el certificado digital (.p12) para la firma electrónica de las facturas.</p>
                <a href="?page=facturaloperu-api-config-settings&tab=dian-config-certificate" class="dian-button dian-button-small">Ir a Certificado</a>
            </li>
            
            <li>
                <h4><span class="dashicons dashicons-clipboard"></span> Configure la resolución</h4>
                <p>Configure los datos de la resolución emitida por la DIAN para la facturación electrónica.</p>
                <a href="?page=facturaloperu-api-config-settings&tab=dian-config-resolution" class="dian-button dian-button-small">Ir a Resolución</a>
            </li>
            
            <li>
                <h4><span class="dashicons dashicons-update"></span> Inicialice el sistema</h4>
                <p>Finalice la configuración inicializando el sistema para comenzar a facturar.</p>
                <a href="?page=facturaloperu-api-config-settings&tab=dian-config-initial-response" class="dian-button dian-button-small">Ir a Inicialización</a>
            </li>
        </ol>
        
        <div class="dian-notice info" style="margin-top: 20px;">
            <div class="dian-notice-icon"><span class="dashicons dashicons-info"></span></div>
            <div class="dian-notice-content">
                <h4>Documentación Completa</h4>
                <p>Para obtener información detallada sobre cómo configurar y utilizar la facturación electrónica, consulte la <a href="https://docs.google.com/document/d/1JqHw1VQKMDwWZvVcvfPEnmL0sWw7DjHyny2PYaXUYdQ/edit?usp=sharing" target="_blank">documentación oficial</a>.</p>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>