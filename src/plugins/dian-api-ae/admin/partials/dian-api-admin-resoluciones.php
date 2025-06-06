<?php
/**
 * Página de resoluciones del panel de administración
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

// Obtener resoluciones
$resoluciones = array();
if (!empty($cliente_seleccionado)) {
    $resoluciones = $db->listar_resoluciones($cliente_seleccionado);
}
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <div class="dian-api-tabs">
        <div class="dian-api-tab-navigation">
            <a href="#" class="dian-api-tab-link active" data-tab="lista">Resoluciones</a>
            <a href="#" class="dian-api-tab-link" data-tab="nueva">Nueva Resolución</a>
        </div>
        
        <div class="dian-api-tab-content active" id="tab-lista">
            <div class="dian-api-filter-bar">
                <form method="get" action="">
                    <input type="hidden" name="page" value="dian-api-resoluciones">
                    
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
                    <h3>Resoluciones de Numeración</h3>
                </div>
                
                <div class="dian-api-table-container">
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th>Prefijo</th>
                                <th>Rango</th>
                                <th>Número Resolución</th>
                                <th>Tipo Documento</th>
                                <th>Fecha Resolución</th>
                                <th>Validez</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($resoluciones)): ?>
                                <tr>
                                    <td colspan="8">No hay resoluciones disponibles.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($resoluciones as $resolucion): ?>
                                    <tr>
                                        <td><?php echo esc_html($resolucion['prefijo']); ?></td>
                                        <td><?php echo esc_html($resolucion['desde_numero'] . ' - ' . $resolucion['hasta_numero']); ?></td>
                                        <td><?php echo esc_html($resolucion['numero_resolucion']); ?></td>
                                        <td><?php echo esc_html(ucfirst($resolucion['tipo_documento'])); ?></td>
                                        <td><?php echo esc_html(date('d/m/Y', strtotime($resolucion['fecha_resolucion']))); ?></td>
                                        <td><?php echo esc_html(date('d/m/Y', strtotime($resolucion['fecha_desde'])) . ' - ' . date('d/m/Y', strtotime($resolucion['fecha_hasta']))); ?></td>
                                        <td>
                                            <?php if ($resolucion['es_vigente']): ?>
                                                <span class="dian-api-status dian-api-status-ok">Vigente</span>
                                            <?php else: ?>
                                                <span class="dian-api-status dian-api-status-error">No Vigente</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <button type="button" class="button button-small edit-resolucion" data-id="<?php echo esc_attr($resolucion['id']); ?>">
                                                Editar
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
        
        <div class="dian-api-tab-content" id="tab-nueva">
            <div class="dian-api-section">
                <div class="dian-api-section-header">
                    <h3>Nueva Resolución de Numeración</h3>
                </div>
                
                <form id="form-resolucion" class="dian-api-form">
                    <input type="hidden" name="action" value="dian_api_guardar_resolucion">
                    <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('dian_api_nonce'); ?>">
                    <input type="hidden" name="id" id="resolucion_id" value="">
                    
                    <div class="dian-api-form-row">
                        <div class="dian-api-form-field">
                            <label for="cliente_id">Cliente</label>
                            <select id="cliente_id" name="cliente_id" required>
                                <option value="">Seleccionar Cliente</option>
                                <?php foreach ($clientes as $cliente): ?>
                                    <option value="<?php echo esc_attr($cliente['cliente_id']); ?>" <?php selected($cliente_seleccionado, $cliente['cliente_id']); ?>>
                                        <?php echo esc_html($cliente['cliente_id']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="dian-api-form-field">
                            <label for="tipo_documento">Tipo de Documento</label>
                            <select id="tipo_documento" name="tipo_documento" required>
                                <option value="factura">Factura</option>
                                <option value="nota_credito">Nota Crédito</option>
                                <option value="nota_debito">Nota Débito</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="dian-api-form-row">
                        <div class="dian-api-form-field">
                            <label for="prefijo">Prefijo</label>
                            <input type="text" id="prefijo" name="prefijo" placeholder="FC">
                            <p class="description">Prefijo de la resolución (opcional).</p>
                        </div>
                        
                        <div class="dian-api-form-field">
                            <label for="numero_resolucion">Número de Resolución</label>
                            <input type="text" id="numero_resolucion" name="numero_resolucion" required>
                            <p class="description">Número de resolución DIAN (ej. 18760000001).</p>
                        </div>
                    </div>
                    
                    <div class="dian-api-form-row">
                        <div class="dian-api-form-field">
                            <label for="desde_numero">Desde Número</label>
                            <input type="text" id="desde_numero" name="desde_numero" required>
                            <p class="description">Número inicial autorizado.</p>
                        </div>
                        
                        <div class="dian-api-form-field">
                            <label for="hasta_numero">Hasta Número</label>
                            <input type="text" id="hasta_numero" name="hasta_numero" required>
                            <p class="description">Número final autorizado.</p>
                        </div>
                    </div>
                    
                    <div class="dian-api-form-row">
                        <div class="dian-api-form-field">
                            <label for="fecha_resolucion">Fecha de Resolución</label>
                            <input type="date" id="fecha_resolucion" name="fecha_resolucion" required>
                            <p class="description">Fecha en que se emitió la resolución.</p>
                        </div>
                    </div>
                    
                    <div class="dian-api-form-row">
                        <div class="dian-api-form-field">
                            <label for="fecha_desde">Fecha Desde</label>
                            <input type="date" id="fecha_desde" name="fecha_desde" required>
                            <p class="description">Fecha inicial de validez.</p>
                        </div>
                        
                        <div class="dian-api-form-field">
                            <label for="fecha_hasta">Fecha Hasta</label>
                            <input type="date" id="fecha_hasta" name="fecha_hasta" required>
                            <p class="description">Fecha final de validez.</p>
                        </div>
                    </div>
                    
                    <div class="dian-api-form-row">
                        <div class="dian-api-form-field">
                            <label for="es_vigente">
                                <input type="checkbox" id="es_vigente" name="es_vigente" value="1" checked>
                                Resolución Vigente
                            </label>
                            <p class="description">Indica si la resolución está vigente actualmente.</p>
                        </div>
                    </div>
                    
                    <div class="dian-api-form-actions">
                        <button type="submit" class="button button-primary">Guardar Resolución</button>
                        <span class="spinner"></span>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
    // Bandera para controlar envíos duplicados
    var isSubmitting = false;
    
    // Manejador de tabs
    $('.dian-api-tab-link').on('click', function(e) {
        e.preventDefault();
        var tabId = $(this).data('tab');
        
        // Activar tab
        $('.dian-api-tab-link').removeClass('active');
        $(this).addClass('active');
        
        // Mostrar contenido
        $('.dian-api-tab-content').removeClass('active');
        $('#tab-' + tabId).addClass('active');
    });
    
    // Guardar resolución
    $('#form-resolucion').on('submit', function(e) {
        e.preventDefault();
        
        // Si ya se está enviando, ignorar este clic
        if (isSubmitting) {
            console.log('Ya se está procesando una solicitud, ignorando clic adicional');
            return false;
        }
        
        var $form = $(this);
        var $spinner = $form.find('.spinner');
        var $boton = $form.find('button[type="submit"]');
        
        // Activar bandera para prevenir envíos duplicados
        isSubmitting = true;
        
        // Deshabilitar botón y mostrar spinner
        $boton.prop('disabled', true);
        $spinner.addClass('is-active');
        
        // Añadir un ID único para esta solicitud
        var formData = $form.serialize() + '&request_id=' + Date.now();
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: formData,
            success: function(response) {
                $spinner.removeClass('is-active');
                
                if (response.success) {
                    // Mostrar mensaje de éxito
                    alert(response.data);
                    
                    // Redirigir con un parámetro único para evitar caché
                    window.location.href = window.location.pathname + 
                        '?page=dian-api-resoluciones&t=' + Date.now();
                } else {
                    // Mostrar error y reactivar el botón
                    alert('Error: ' + response.data);
                    $boton.prop('disabled', false);
                    isSubmitting = false;
                }
            },
            error: function() {
                // En caso de error, reactivar el botón
                $spinner.removeClass('is-active');
                $boton.prop('disabled', false);
                isSubmitting = false;
                alert('Error de comunicación con el servidor');
            }
        });
    });
    
    // Editar resolución
    $('.edit-resolucion').on('click', function() {
        var id = $(this).data('id');
        $('#tab-nueva').addClass('active').siblings('.dian-api-tab-content').removeClass('active');
        $('.dian-api-tab-link[data-tab="nueva"]').addClass('active').siblings('.dian-api-tab-link').removeClass('active');
        
        // Cargar datos de la resolución
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'dian_api_obtener_resolucion',
                nonce: '<?php echo wp_create_nonce('dian_api_nonce'); ?>',
                id: id
            },
            success: function(response) {
                if (response.success) {
                    var resolucion = response.data;
                    
                    // Llenar formulario con datos de la resolución
                    $('#resolucion_id').val(resolucion.id);
                    $('#cliente_id').val(resolucion.cliente_id);
                    $('#prefijo').val(resolucion.prefijo);
                    $('#desde_numero').val(resolucion.desde_numero);
                    $('#hasta_numero').val(resolucion.hasta_numero);
                    $('#numero_resolucion').val(resolucion.numero_resolucion);
                    $('#fecha_resolucion').val(resolucion.fecha_resolucion);
                    $('#fecha_desde').val(resolucion.fecha_desde);
                    $('#fecha_hasta').val(resolucion.fecha_hasta);
                    $('#tipo_documento').val(resolucion.tipo_documento);
                    $('#es_vigente').prop('checked', resolucion.es_vigente == 1);
                    
                    // Cambiar botón a "Actualizar"
                    $('button[type="submit"]').text('Actualizar Resolución');
                    
                    // Añadir botón para eliminar
                    if ($('#btn-eliminar-resolucion').length === 0) {
                        $('<button>')
                            .attr('type', 'button')
                            .attr('id', 'btn-eliminar-resolucion')
                            .addClass('button button-secondary')
                            .css('margin-left', '10px')
                            .text('Eliminar Resolución')
                            .insertAfter('button[type="submit"]')
                            .on('click', function() {
                                eliminarResolucion(id);
                            });
                    }
                } else {
                    alert('Error: ' + response.data);
                }
            }
        });
    });
    
    // Función para eliminar resolución
    function eliminarResolucion(id) {
        if (confirm('¿Está seguro de eliminar esta resolución? Esta acción no se puede deshacer.')) {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'dian_api_eliminar_resolucion',
                    nonce: '<?php echo wp_create_nonce('dian_api_nonce'); ?>',
                    id: id
                },
                success: function(response) {
                    if (response.success) {
                        alert(response.data);
                        window.location.reload();
                    } else {
                        alert('Error: ' + response.data);
                    }
                }
            });
        }
    }
});
</script>