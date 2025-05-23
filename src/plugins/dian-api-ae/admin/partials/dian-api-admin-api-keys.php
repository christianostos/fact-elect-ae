<?php
/**
 * Página de API Keys del panel de administración
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

// Obtener API Keys
$db = new DIAN_API_DB();
$api_keys = $db->listar_api_keys();
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <div class="dian-api-tabs">
        <div class="dian-api-tab-navigation">
            <a href="#" class="dian-api-tab-link active" data-tab="lista">API Keys</a>
            <a href="#" class="dian-api-tab-link" data-tab="nueva">Nueva API Key</a>
        </div>
        
        <div class="dian-api-tab-content active" id="tab-lista">
            <div class="dian-api-section">
                <div class="dian-api-section-header">
                    <h3>API Keys Configuradas</h3>
                </div>
                
                <div class="dian-api-table-container">
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>API Key</th>
                                <th>Permisos</th>
                                <th>Estado</th>
                                <th>Fecha Creación</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($api_keys)): ?>
                                <tr>
                                    <td colspan="6">No hay API Keys configuradas.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($api_keys as $api_key): ?>
                                    <tr>
                                        <td><?php echo esc_html($api_key['nombre']); ?></td>
                                        <td><?php echo esc_html($api_key['api_key']); ?></td>
                                        <td><?php echo esc_html($api_key['permisos']); ?></td>
                                        <td>
                                            <?php if ($api_key['estado'] == 'activo'): ?>
                                                <span class="dian-api-status dian-api-status-ok">Activo</span>
                                            <?php else: ?>
                                                <span class="dian-api-status dian-api-status-error">Inactivo</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo esc_html(date('d/m/Y H:i', strtotime($api_key['fecha_creacion']))); ?></td>
                                        <td>
                                            <button type="button" class="button button-small toggle-status" data-id="<?php echo esc_attr($api_key['id']); ?>" data-status="<?php echo esc_attr($api_key['estado']); ?>">
                                                <?php echo $api_key['estado'] == 'activo' ? 'Desactivar' : 'Activar'; ?>
                                            </button>
                                            <button type="button" class="button button-small eliminar-api-key" data-id="<?php echo esc_attr($api_key['id']); ?>" data-nombre="<?php echo esc_attr($api_key['nombre']); ?>">
                                                Eliminar
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
                    <h3>Nueva API Key</h3>
                </div>
                
                <form id="form-api-key" class="dian-api-form">
                    <input type="hidden" name="action" value="dian_api_generar_api_key">
                    <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('dian_api_nonce'); ?>">
                    <input type="hidden" name="_request_id" value="<?php echo esc_attr(uniqid('api_key_')); ?>">
                    
                    <div class="dian-api-form-row">
                        <div class="dian-api-form-field">
                            <label for="nombre">Nombre</label>
                            <input type="text" id="nombre" name="nombre" required>
                            <p class="description">Nombre descriptivo para identificar esta API Key.</p>
                        </div>
                    </div>
                    
                    <div class="dian-api-form-row">
                        <div class="dian-api-form-field">
                            <label for="permisos">Permisos</label>
                            <select id="permisos" name="permisos">
                                <option value="read">Solo lectura</option>
                                <option value="write">Lectura y escritura</option>
                                <option value="admin">Administrador</option>
                            </select>
                            <p class="description">Nivel de permisos para esta API Key.</p>
                        </div>
                    </div>
                    
                    <div class="dian-api-form-actions">
                        <button type="submit" class="button button-primary">Generar API Key</button>
                        <span class="spinner"></span>
                    </div>
                </form>
            </div>
            
            <div id="api-key-result" class="dian-api-section" style="display: none;">
                <div class="dian-api-section-header">
                    <h3>API Key Generada</h3>
                </div>
                
                <div class="dian-api-section-content">
                    <div class="dian-api-alert dian-api-alert-warning">
                        <p><strong>¡Importante!</strong> Guarde esta información en un lugar seguro. La API Secret no se mostrará nuevamente.</p>
                    </div>
                    
                    <div class="dian-api-form-row">
                        <div class="dian-api-form-field">
                            <label for="generated_api_key">API Key</label>
                            <div class="copy-field-container">
                                <input type="text" id="generated_api_key" readonly>
                                <button type="button" class="button copy-button" data-target="generated_api_key">Copiar</button>
                            </div>
                        </div>
                    </div>

                    <div class="dian-api-form-row">
                        <div class="dian-api-form-field">
                            <label for="generated_api_secret">API Secret</label>
                            <div class="copy-field-container">
                                <input type="text" id="generated_api_secret" readonly>
                                <button type="button" class="button copy-button" data-target="generated_api_secret">Copiar</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
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
    
    // Cambiar estado de API Key
    $('.toggle-status').on('click', function() {
        var $button = $(this);
        var id = $button.data('id');
        var estadoActual = $button.data('status');
        
        if (confirm('¿Está seguro de cambiar el estado de esta API Key?')) {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'dian_api_cambiar_estado_api_key',
                    nonce: '<?php echo wp_create_nonce('dian_api_nonce'); ?>',
                    id: id,
                    estado_actual: estadoActual
                },
                success: function(response) {
                    if (response.success) {
                        // Actualizar la UI
                        var nuevoEstado = response.data.nuevo_estado;
                        var $fila = $button.closest('tr');
                        
                        // Actualizar texto del estado
                        if (nuevoEstado === 'activo') {
                            $fila.find('td:nth-child(4) span').removeClass('dian-api-status-error').addClass('dian-api-status-ok').text('Activo');
                        } else {
                            $fila.find('td:nth-child(4) span').removeClass('dian-api-status-ok').addClass('dian-api-status-error').text('Inactivo');
                        }
                        
                        // Actualizar botón
                        $button.data('status', nuevoEstado);
                        $button.text(nuevoEstado === 'activo' ? 'Desactivar' : 'Activar');
                        
                        alert('Estado actualizado correctamente');
                    } else {
                        alert('Error: ' + response.data);
                    }
                },
                error: function() {
                    alert('Error en la comunicación con el servidor');
                }
            });
        }
    });

    // Copiar al portapapeles
    $('.copy-button').on('click', function() {
        var targetId = $(this).data('target');
        var $input = $('#' + targetId);
        
        // Seleccionar el texto
        $input.select();
        
        try {
            // Copiar al portapapeles
            var success = document.execCommand('copy');
            if (success) {
                $(this).text('¡Copiado!');
                setTimeout(function() {
                    $('.copy-button[data-target="' + targetId + '"]').text('Copiar');
                }, 1500);
            } else {
                alert('No se pudo copiar el texto. Por favor, cópielo manualmente.');
            }
        } catch (err) {
            alert('Error al copiar: ' + err);
        }
    });

    // Eliminar API Key
    $('.eliminar-api-key').on('click', function() {
        var $button = $(this);
        var id = $button.data('id');
        var nombre = $button.data('nombre');
        
        if (confirm('¿Está seguro de eliminar la API Key "' + nombre + '"? Esta acción no se puede deshacer.')) {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'dian_api_eliminar_api_key',
                    nonce: '<?php echo wp_create_nonce('dian_api_nonce'); ?>',
                    id: id
                },
                success: function(response) {
                    if (response.success) {
                        // Eliminar fila de la tabla
                        $button.closest('tr').fadeOut(400, function() {
                            $(this).remove();
                            
                            // Verificar si la tabla quedó vacía
                            if ($('.wp-list-table tbody tr').length === 0) {
                                $('.wp-list-table tbody').html('<tr><td colspan="6">No hay API Keys configuradas.</td></tr>');
                            }
                        });
                        
                        alert('API Key eliminada correctamente');
                    } else {
                        alert('Error: ' + response.data);
                    }
                },
                error: function() {
                    alert('Error en la comunicación con el servidor');
                }
            });
        }
    });
});
</script>