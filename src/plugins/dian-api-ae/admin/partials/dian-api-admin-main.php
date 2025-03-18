<?php
/**
 * Página principal del panel de administración
 *
 * @link       https://accioneficaz.com
 * @since      1.0.0
 *
 * @package    DIAN_API
 * @subpackage DIAN_API/admin/partials
 */

// Si este archivo es llamado directamente, abortar.
if (!defined('WPINC')) {
    die;
}
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <div class="dian-api-dashboard">
        <div class="dian-api-card">
            <h2><span class="dashicons dashicons-admin-settings"></span> Estado del Sistema</h2>
            <div class="dian-api-card-content">
                <?php
                $mpdf_available = class_exists('\Mpdf\Mpdf');
                $qrcode_available = class_exists('QRcode');
                
                $db = new DIAN_API_DB();
                $clientes = $db->listar_clientes();
                $total_clientes = count($clientes);
                
                // Contar documentos totales y por estado
                $total_documentos = 0;
                $documentos_enviados = 0;
                $documentos_aceptados = 0;
                $documentos_rechazados = 0;
                
                foreach ($clientes as $cliente) {
                    $documentos = $db->listar_documentos($cliente['cliente_id'], array(), 1000, 0);
                    $total_documentos += count($documentos);
                    
                    foreach ($documentos as $documento) {
                        if ($documento['estado'] == 'enviado') {
                            $documentos_enviados++;
                        } elseif ($documento['estado'] == 'aceptado') {
                            $documentos_aceptados++;
                        } elseif ($documento['estado'] == 'rechazado') {
                            $documentos_rechazados++;
                        }
                    }
                }
                ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">Dependencias</th>
                        <td>
                            <div class="dian-api-dependency-status">
                                <span class="dian-api-status <?php echo $qrcode_available ? 'dian-api-status-ok' : 'dian-api-status-error'; ?>">
                                    <span class="dashicons <?php echo $qrcode_available ? 'dashicons-yes' : 'dashicons-no'; ?>"></span>
                                    Biblioteca PHP QR Code
                                </span>
                                
                                <span class="dian-api-status <?php echo $mpdf_available ? 'dian-api-status-ok' : 'dian-api-status-warning'; ?>">
                                    <span class="dashicons <?php echo $mpdf_available ? 'dashicons-yes' : 'dashicons-warning'; ?>"></span>
                                    Biblioteca mPDF
                                    <?php if (!$mpdf_available): ?>
                                        <small>(Generación de PDF deshabilitada)</small>
                                    <?php endif; ?>
                                </span>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Estadísticas</th>
                        <td>
                            <ul>
                                <li><strong><?php echo $total_clientes; ?></strong> cliente(s) configurado(s)</li>
                                <li><strong><?php echo $total_documentos; ?></strong> documento(s) total(es)</li>
                                <li><strong><?php echo $documentos_enviados; ?></strong> documento(s) enviado(s)</li>
                                <li><strong><?php echo $documentos_aceptados; ?></strong> documento(s) aceptado(s)</li>
                                <li><strong><?php echo $documentos_rechazados; ?></strong> documento(s) rechazado(s)</li>
                            </ul>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
        
        <div class="dian-api-card">
            <h2><span class="dashicons dashicons-admin-tools"></span> Acciones Rápidas</h2>
            <div class="dian-api-card-content">
                <div class="dian-api-quick-actions">
                    <a href="<?php echo admin_url('admin.php?page=dian-api-config'); ?>" class="button button-primary">
                        <span class="dashicons dashicons-admin-settings"></span>
                        Configurar Cliente
                    </a>
                    
                    <a href="<?php echo admin_url('admin.php?page=dian-api-resoluciones'); ?>" class="button button-primary">
                        <span class="dashicons dashicons-media-text"></span>
                        Gestionar Resoluciones
                    </a>
                    
                    <a href="<?php echo admin_url('admin.php?page=dian-api-documentos'); ?>" class="button button-primary">
                        <span class="dashicons dashicons-media-spreadsheet"></span>
                        Ver Documentos
                    </a>
                    
                    <a href="<?php echo admin_url('admin.php?page=dian-api-api-keys'); ?>" class="button button-primary">
                        <span class="dashicons dashicons-admin-network"></span>
                        Gestionar API Keys
                    </a>
                    
                    <a href="<?php echo admin_url('admin.php?page=dian-api-logs'); ?>" class="button button-primary">
                        <span class="dashicons dashicons-list-view"></span>
                        Ver Logs
                    </a>
                </div>
            </div>
        </div>
        
        <div class="dian-api-card">
            <h2><span class="dashicons dashicons-book"></span> Documentación</h2>
            <div class="dian-api-card-content">
                <p>Bienvenido al plugin de <strong>API de Facturación Electrónica DIAN</strong>. Este plugin le permite generar documentos electrónicos válidos ante la DIAN en Colombia.</p>
                
                <h3>Pasos para empezar:</h3>
                
                <ol>
                    <li>Configure los datos de su empresa en la pestaña <a href="<?php echo admin_url('admin.php?page=dian-api-config'); ?>">Configuración</a>.</li>
                    <li>Cree al menos una <a href="<?php echo admin_url('admin.php?page=dian-api-resoluciones'); ?>">Resolución de numeración</a>.</li>
                    <li>Cree una <a href="<?php echo admin_url('admin.php?page=dian-api-api-keys'); ?>">API Key</a> para integrar con otros sistemas.</li>
                    <li>Haga pruebas en el ambiente de habilitación antes de pasar a producción.</li>
                </ol>
                
                <p>Para más información sobre la API REST y cómo integrarla con otros sistemas, consulte la <a href="<?php echo plugins_url('docs/API_REST.md', dirname(dirname(__FILE__))); ?>" target="_blank">documentación</a>.</p>
                
                <h3>Proceso de habilitación ante la DIAN:</h3>
                
                <ol>
                    <li>Registre su software en el portal de la DIAN.</li>
                    <li>Configure las credenciales proporcionadas por la DIAN en este plugin.</li>
                    <li>Realice las pruebas requeridas en el ambiente de habilitación.</li>
                    <li>Una vez aprobado, cambie el modo de operación a producción.</li>
                </ol>
            </div>
        </div>
    </div>
</div>