<?php
/**
 * Página de logs del panel de administración
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

// Obtener clientes configurados
$db = new DIAN_API_DB();
$clientes = $db->listar_clientes();

// Obtener cliente seleccionado o usar el primero disponible
$cliente_seleccionado = isset($_GET['cliente_id']) ? sanitize_text_field($_GET['cliente_id']) : '';
if (empty($cliente_seleccionado) && !empty($clientes)) {
    $cliente_seleccionado = $clientes[0]['cliente_id'];
}

// Obtener registros de log
$logs = array();
if (!empty($cliente_seleccionado)) {
    $logs = $db->obtener_logs($cliente_seleccionado, 50, 0);
}
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <div class="dian-api-filter-bar">
        <form method="get" action="">
            <input type="hidden" name="page" value="dian-api-logs">
            
            <select name="cliente_id">
                <option value="">Seleccionar Cliente</option>
                <?php foreach ($clientes as $cliente): ?>
                    <option value="<?php echo esc_attr($cliente['cliente_id']); ?>" <?php selected($cliente_seleccionado, $cliente['cliente_id']); ?>>
                        <?php echo esc_html($cliente['cliente_id']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            
            <button type="submit" class="button">Filtrar</button>
        </form>
    </div>
    
    <div class="dian-api-section">
        <div class="dian-api-section-header">
            <h3>Registros de Comunicación con DIAN</h3>
        </div>
        
        <div class="dian-api-table-container">
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Acción</th>
                        <th>Código HTTP</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($logs)): ?>
                        <tr>
                            <td colspan="4">No hay registros de log disponibles.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($logs as $log): ?>
                            <tr>
                                <td><?php echo esc_html(date('d/m/Y H:i:s', strtotime($log['fecha_creacion']))); ?></td>
                                <td><?php echo esc_html($log['accion']); ?></td>
                                <td>
                                    <?php 
                                    $code_class = '';
                                    if ($log['codigo_http'] >= 200 && $log['codigo_http'] < 300) {
                                        $code_class = 'dian-api-status-ok';
                                    } elseif ($log['codigo_http'] >= 400) {
                                        $code_class = 'dian-api-status-error';
                                    } else {
                                        $code_class = 'dian-api-status-warning';
                                    }
                                    ?>
                                    <span class="dian-api-status <?php echo $code_class; ?>">
                                        <?php echo esc_html($log['codigo_http']); ?>
                                    </span>
                                </td>
                                <td>
                                    <button type="button" class="button button-small view-log" data-id="<?php echo esc_attr($log['id']); ?>">
                                        Ver Detalles
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div id="log-modal" class="dian-api-modal" style="display: none;">
    <div class="dian-api-modal-content">
        <span class="dian-api-modal-close">&times;</span>
        <h2>Detalles del Log</h2>
        <div class="dian-api-tabs">
            <div class="dian-api-tab-navigation">
                <a href="#" class="dian-api-tab-link active" data-tab="peticion">Petición</a>
                <a href="#" class="dian-api-tab-link" data-tab="respuesta">Respuesta</a>
            </div>
            
            <div class="dian-api-tab-content active" id="tab-peticion">
                <pre id="log-peticion"></pre>
            </div>
            
            <div class="dian-api-tab-content" id="tab-respuesta">
                <pre id="log-respuesta"></pre>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
    // Ver detalles de log
    $('.view-log').on('click', function() {
        // Implementación pendiente
        alert('Funcionalidad en desarrollo');
    });
    
    // Manejador de tabs en modal
    $('#log-modal .dian-api-tab-link').on('click', function(e) {
        e.preventDefault();
        var tabId = $(this).data('tab');
        
        // Activar tab
        $('#log-modal .dian-api-tab-link').removeClass('active');
        $(this).addClass('active');
        
        // Mostrar contenido
        $('#log-modal .dian-api-tab-content').removeClass('active');
        $('#log-modal #tab-' + tabId).addClass('active');
    });
    
    // Cerrar modal
    $('#log-modal .dian-api-modal-close').on('click', function() {
        $('#log-modal').hide();
    });
});
</script>