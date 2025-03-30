<?php
/**
 * Página de documentos del panel de administración
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

// Obtener tipo de documento
$tipo_documento = isset($_GET['tipo_documento']) ? sanitize_text_field($_GET['tipo_documento']) : 'factura';

// Obtener documentos
$documentos = array();
if (!empty($cliente_seleccionado)) {
    $filtros = array(
        'tipo_documento' => $tipo_documento
    );
    $documentos = $db->listar_documentos($cliente_seleccionado, $filtros, 50, 0);
}
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <div class="dian-api-filter-bar">
        <form method="get" action="">
            <input type="hidden" name="page" value="dian-api-documentos">
            
            <select name="cliente_id">
                <option value="">Seleccionar Cliente</option>
                <?php foreach ($clientes as $cliente): ?>
                    <option value="<?php echo esc_attr($cliente['cliente_id']); ?>" <?php selected($cliente_seleccionado, $cliente['cliente_id']); ?>>
                        <?php echo esc_html($cliente['cliente_id']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            
            <select name="tipo_documento">
                <option value="factura" <?php selected($tipo_documento, 'factura'); ?>>Factura</option>
                <option value="nota_credito" <?php selected($tipo_documento, 'nota_credito'); ?>>Nota Crédito</option>
                <option value="nota_debito" <?php selected($tipo_documento, 'nota_debito'); ?>>Nota Débito</option>
            </select>
            
            <button type="submit" class="button">Filtrar</button>
        </form>
    </div>
    
    <div class="dian-api-section">
        <div class="dian-api-section-header">
            <h3>Documentos</h3>
        </div>
        
        <div class="dian-api-table-container">
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>Prefijo</th>
                        <th>Número</th>
                        <th>Cliente</th>
                        <th>Receptor</th>
                        <th>Fecha Emisión</th>
                        <th>Valor Total</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($documentos)): ?>
                        <tr>
                            <td colspan="8">No hay documentos disponibles.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($documentos as $documento): ?>
                            <tr>
                                <td><?php echo esc_html($documento['prefijo']); ?></td>
                                <td><?php echo esc_html($documento['numero']); ?></td>
                                <td><?php echo esc_html($documento['cliente_id']); ?></td>
                                <td><?php echo esc_html($documento['receptor_razon_social']); ?></td>
                                <td><?php echo esc_html(date('d/m/Y H:i', strtotime($documento['fecha_emision']))); ?></td>
                                <td><?php echo esc_html(number_format($documento['valor_total'], 2, ',', '.')); ?></td>
                                <td>
                                    <?php 
                                    $estado_class = '';
                                    switch ($documento['estado']) {
                                        case 'generado':
                                            $estado_class = 'dian-api-status-warning';
                                            break;
                                        case 'enviado':
                                            $estado_class = 'dian-api-status-info';
                                            break;
                                        case 'aceptado':
                                            $estado_class = 'dian-api-status-ok';
                                            break;
                                        case 'rechazado':
                                            $estado_class = 'dian-api-status-error';
                                            break;
                                        default:
                                            $estado_class = '';
                                    }
                                    ?>
                                    <span class="dian-api-status <?php echo $estado_class; ?>">
                                        <?php echo esc_html(ucfirst($documento['estado'])); ?>
                                    </span>
                                </td>
                                <td class="dian-api-document-actions">
                                    <button type="button" class="button button-small view-document" data-cliente-id="<?php echo esc_attr($documento['cliente_id']); ?>" data-prefijo="<?php echo esc_attr($documento['prefijo']); ?>" data-numero="<?php echo esc_attr($documento['numero']); ?>" data-tipo="<?php echo esc_attr($documento['tipo_documento']); ?>">
                                        Ver
                                    </button>
                                    
                                    <button type="button" class="button button-small view-pdf" data-id="<?php echo esc_attr($documento['id']); ?>">
                                        PDF
                                    </button>
                                    
                                    <?php if ($documento['estado'] == 'generado'): ?>
                                        <button type="button" class="button button-small send-document" data-cliente-id="<?php echo esc_attr($documento['cliente_id']); ?>" data-prefijo="<?php echo esc_attr($documento['prefijo']); ?>" data-numero="<?php echo esc_attr($documento['numero']); ?>" data-tipo="<?php echo esc_attr($documento['tipo_documento']); ?>">
                                            Enviar
                                        </button>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($documento['track_id'])): ?>
                                        <button type="button" class="button button-small check-status" data-track-id="<?php echo esc_attr($documento['track_id']); ?>">
                                            Verificar
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div id="documento-modal" class="dian-api-modal" style="display: none;">
    <div class="dian-api-modal-content">
        <span class="dian-api-modal-close">&times;</span>
        <h2>Detalles del Documento</h2>
        <div id="documento-detalles"></div>
    </div>
</div>
<script type="text/javascript">
jQuery(document).ready(function($) {
    // La mayoría de la funcionalidad ya está en admin.js
    
    // Si hay un parámetro document_id en la URL, mostrar ese documento automáticamente
    var urlParams = new URLSearchParams(window.location.search);
    var documentId = urlParams.get('document_id');
    
    if (documentId) {
        // Buscar el botón del documento y hacer clic en él
        $('.view-document[data-id="' + documentId + '"]').click();
    }
});
</script>