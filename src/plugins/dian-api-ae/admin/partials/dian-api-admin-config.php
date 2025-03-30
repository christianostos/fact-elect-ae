<?php
/**
 * Página de configuración del plugin
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

// Obtener datos del cliente seleccionado
$cliente_actual = null;
if (!empty($cliente_seleccionado)) {
    $cliente_actual = $db->obtener_configuracion($cliente_seleccionado);
}
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

    <div class="dian-api-debug-tools" style="margin-bottom: 15px; background: #f8f8f8; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
    <h3>Herramientas de diagnóstico</h3>
    <p>Si experimentas problemas con la base de datos, utiliza estas herramientas:</p>
        <button type="button" id="btn-diagnosticar-db" class="button">Diagnosticar Base de Datos</button>
        <button type="button" id="btn-reparar-db" class="button button-primary">Reparar Base de Datos</button>
        <span class="spinner" style="float:none;"></span>
        <div id="diagnostico-resultado" style="margin-top: 10px; display:none;"></div>
    </div>

    <script type="text/javascript">
    jQuery(document).ready(function($) {
        $('#btn-diagnosticar-db').on('click', function() {
            var $button = $(this);
            var $spinner = $('.dian-api-debug-tools .spinner');
            var $resultado = $('#diagnostico-resultado');
            
            $spinner.addClass('is-active');
            $button.prop('disabled', true);
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'dian_api_diagnosticar_db'
                },
                success: function(response) {
                    $spinner.removeClass('is-active');
                    $button.prop('disabled', false);
                    
                    if (response.success) {
                        $resultado.html('<div style="background:#fff; padding:10px; border:1px solid #ddd;">' + response.data + '</div>').show();
                    } else {
                        $resultado.html('<div class="notice notice-error inline"><p>' + response.data + '</p></div>').show();
                    }
                },
                error: function() {
                    $spinner.removeClass('is-active');
                    $button.prop('disabled', false);
                    $resultado.html('<div class="notice notice-error inline"><p>Error en la comunicación con el servidor.</p></div>').show();
                }
            });
        });
        
        $('#btn-reparar-db').on('click', function() {
            if (!confirm('¿Está seguro de reparar la estructura de la base de datos? Esta acción intentará recrear la tabla.')) {
                return;
            }
            
            var $button = $(this);
            var $spinner = $('.dian-api-debug-tools .spinner');
            var $resultado = $('#diagnostico-resultado');
            
            $spinner.addClass('is-active');
            $button.prop('disabled', true);
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'dian_api_reparar_db'
                },
                success: function(response) {
                    $spinner.removeClass('is-active');
                    $button.prop('disabled', false);
                    
                    if (response.success) {
                        $resultado.html('<div class="notice notice-success inline"><p>' + response.data + '</p><p>Por favor, <a href="' + window.location.href + '">recarga la página</a> y vuelve a intentar crear un cliente.</p></div>').show();
                    } else {
                        $resultado.html('<div class="notice notice-error inline"><p>' + response.data + '</p></div>').show();
                    }
                },
                error: function() {
                    $spinner.removeClass('is-active');
                    $button.prop('disabled', false);
                    $resultado.html('<div class="notice notice-error inline"><p>Error en la comunicación con el servidor.</p></div>').show();
                }
            });
        });
    });
    </script>
    
    <div class="dian-api-tabs">
        <div class="dian-api-tab-navigation">
            <a href="#" class="dian-api-tab-link active" data-tab="clientes">Clientes</a>
            <a href="#" class="dian-api-tab-link" data-tab="empresa">Datos de Empresa</a>
            <a href="#" class="dian-api-tab-link" data-tab="pdf">Configuración PDF</a>
        </div>
        
        <div class="dian-api-tab-content active" id="tab-clientes">
            <div class="dian-api-section">
                <div class="dian-api-section-header">
                    <h3>Clientes Configurados</h3>
                    <button type="button" class="button button-primary" id="btn-nuevo-cliente">Nuevo Cliente</button>
                </div>
                
                <div class="dian-api-table-container">
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th>ID Cliente</th>
                                <th>ID Software</th>
                                <th>Modo Operación</th>
                                <th>Fecha Creación</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($clientes)): ?>
                                <tr>
                                    <td colspan="5">No hay clientes configurados. Cree uno nuevo.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($clientes as $cliente): ?>
                                    <tr>
                                        <td><?php echo esc_html($cliente['cliente_id']); ?></td>
                                        <td><?php echo esc_html($cliente['id_software']); ?></td>
                                        <td><?php echo esc_html(ucfirst($cliente['modo_operacion'])); ?></td>
                                        <td><?php echo esc_html(date('d/m/Y H:i', strtotime($cliente['fecha_creacion']))); ?></td>
                                        <td>
                                            <a href="<?php echo admin_url('admin.php?page=dian-api-config&cliente_id=' . urlencode($cliente['cliente_id'])); ?>" class="button button-small">Editar</a>
                                            <button type="button" class="button button-small eliminar-cliente" data-id="<?php echo esc_attr($cliente['cliente_id']); ?>">Eliminar</button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="dian-api-section">
                <div class="dian-api-section-header">
                    <h3><?php echo empty($cliente_actual) ? 'Nuevo Cliente' : 'Editar Cliente: ' . esc_html($cliente_seleccionado); ?></h3>
                </div>
                
                <form id="form-cliente" class="dian-api-form">
                    <input type="hidden" name="action" value="dian_api_guardar_cliente">
                    <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('dian_api_nonce'); ?>">
                    
                    <div class="dian-api-form-row">
                        <div class="dian-api-form-field">
                            <label for="cliente_id">ID Cliente</label>
                            <input type="text" id="cliente_id" name="cliente_id" value="<?php echo esc_attr($cliente_actual ? $cliente_actual['cliente_id'] : ''); ?>" <?php echo $cliente_actual ? 'readonly' : ''; ?> required>
                            <p class="description">Identificador único del cliente (NIT sin DV).</p>
                        </div>
                        
                        <div class="dian-api-form-field">
                            <label for="id_software">ID Software</label>
                            <input type="text" id="id_software" name="id_software" value="<?php echo esc_attr($cliente_actual ? $cliente_actual['id_software'] : ''); ?>" required>
                            <p class="description">Identificador del software asignado por la DIAN.</p>
                        </div>
                    </div>
                    
                    <div class="dian-api-form-row">
                        <div class="dian-api-form-field">
                            <label for="software_pin">PIN Software</label>
                            <input type="password" id="software_pin" name="software_pin" value="<?php echo esc_attr($cliente_actual ? $cliente_actual['software_pin'] : ''); ?>" required>
                            <p class="description">PIN del software asignado durante el registro.</p>
                        </div>
                        
                        <div class="dian-api-form-field">
                            <label for="test_set_id">Test Set ID</label>
                            <input type="text" id="test_set_id" name="test_set_id" value="<?php echo esc_attr($cliente_actual ? $cliente_actual['test_set_id'] : ''); ?>">
                            <p class="description">ID del set de pruebas para habilitación.</p>
                        </div>
                    </div>
                    
                    <div class="dian-api-form-row">
                        <div class="dian-api-form-field">
                            <label for="certificado_ruta">Certificado Digital (.p12) <span class="required">*</span></label>
                            
                            <div class="certificado-upload-container">
                                <input type="text" id="certificado_ruta" name="certificado_ruta" 
                                    value="<?php echo esc_attr($cliente_actual ? $cliente_actual['certificado_ruta'] : ''); ?>" 
                                    placeholder="Ruta del certificado" required readonly>
                                
                                <div class="upload-buttons">
                                    <button type="button" id="btn-cargar-certificado" class="button">Cargar Certificado</button>
                                    <span class="spinner" style="float:none;"></span>
                                </div>
                                
                                <input type="file" id="certificado_file" style="display:none;" accept=".p12,.pfx">
                            </div>
                            
                            <p class="description">Cargue su certificado digital en formato PKCS#12 (.p12 o .pfx)</p>
                            <div id="certificado_status" class="dian-api-certificate-status" style="margin-top: 10px; display:none;"></div>
                        </div>
                        
                        <div class="dian-api-form-field">
                            <label for="certificado_clave">Clave del Certificado <span class="required">*</span></label>
                            <input type="password" id="certificado_clave" name="certificado_clave" 
                                value="<?php echo esc_attr($cliente_actual ? $cliente_actual['certificado_clave'] : ''); ?>" required>
                            <p class="description">Contraseña del certificado digital proporcionada por la entidad certificadora.</p>
                        </div>
                    </div>

                    <div class="dian-api-form-row">
                        <div class="dian-api-form-field">
                            <button type="button" id="btn-verificar-certificado" class="button">Verificar Certificado</button>
                            <span class="spinner" style="float:none;"></span>
                        </div>
                    </div>
                    
                    <div class="dian-api-form-row">
                        <div class="dian-api-form-field">
                            <label for="url_ws_validacion">URL WS Habilitación</label>
                            <input type="text" id="url_ws_validacion" name="url_ws_validacion" value="<?php echo esc_attr($cliente_actual ? $cliente_actual['url_ws_validacion'] : 'https://vpfe-hab.dian.gov.co/WcfDianCustomerServices.svc'); ?>">
                            <p class="description">URL del WebService de habilitación.</p>
                        </div>
                        
                        <div class="dian-api-form-field">
                            <label for="url_ws_produccion">URL WS Producción</label>
                            <input type="text" id="url_ws_produccion" name="url_ws_produccion" value="<?php echo esc_attr($cliente_actual ? $cliente_actual['url_ws_produccion'] : 'https://vpfe.dian.gov.co/WcfDianCustomerServices.svc'); ?>">
                            <p class="description">URL del WebService de producción.</p>
                        </div>
                    </div>
                    
                    <div class="dian-api-form-row">
                        <div class="dian-api-form-field">
                            <label for="modo_operacion">Modo de Operación</label>
                            <select id="modo_operacion" name="modo_operacion">
                                <option value="pruebas_internas" <?php selected($cliente_actual && $cliente_actual['modo_operacion'] == 'pruebas_internas'); ?>>Pruebas Internas (Sin DIAN)</option>
                                <option value="habilitacion" <?php selected($cliente_actual && $cliente_actual['modo_operacion'] == 'habilitacion'); ?>>Habilitación (Pruebas DIAN)</option>
                                <option value="produccion" <?php selected($cliente_actual && $cliente_actual['modo_operacion'] == 'produccion'); ?>>Producción</option>
                            </select>
                            <p class="description">Modo de operación del sistema.</p>
                        </div>
                    </div>
                    
                    <div class="dian-api-form-actions">
                        <button type="submit" class="button button-primary">Guardar Cliente</button>
                        <span class="spinner"></span>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="dian-api-tab-content" id="tab-empresa">
            <div class="dian-api-section">
                <div class="dian-api-section-header">
                    <h3>Datos de la Empresa</h3>
                </div>
                
                <form method="post" action="options.php" class="dian-api-form">
                    <?php settings_fields('dian_api_company_options'); ?>
                    
                    <div class="dian-api-form-row">
                        <div class="dian-api-form-field">
                            <label for="dian_api_company_name">Nombre de la Empresa</label>
                            <input type="text" id="dian_api_company_name" name="dian_api_company_name" value="<?php echo esc_attr(get_option('dian_api_company_name')); ?>">
                        </div>
                        
                        <div class="dian-api-form-field">
                            <label for="dian_api_company_nit">NIT</label>
                            <input type="text" id="dian_api_company_nit" name="dian_api_company_nit" value="<?php echo esc_attr(get_option('dian_api_company_nit')); ?>">
                        </div>
                    </div>
                    
                    <div class="dian-api-form-row">
                        <div class="dian-api-form-field">
                            <label for="dian_api_company_address">Dirección</label>
                            <input type="text" id="dian_api_company_address" name="dian_api_company_address" value="<?php echo esc_attr(get_option('dian_api_company_address')); ?>">
                        </div>
                        
                        <div class="dian-api-form-field">
                            <label for="dian_api_company_phone">Teléfono</label>
                            <input type="text" id="dian_api_company_phone" name="dian_api_company_phone" value="<?php echo esc_attr(get_option('dian_api_company_phone')); ?>">
                        </div>
                    </div>
                    
                    <div class="dian-api-form-row">
                        <div class="dian-api-form-field">
                            <label for="dian_api_company_email">Email</label>
                            <input type="email" id="dian_api_company_email" name="dian_api_company_email" value="<?php echo esc_attr(get_option('dian_api_company_email')); ?>">
                        </div>
                        
                        <div class="dian-api-form-field">
                            <label for="dian_api_company_website">Sitio Web</label>
                            <input type="url" id="dian_api_company_website" name="dian_api_company_website" value="<?php echo esc_attr(get_option('dian_api_company_website')); ?>">
                        </div>
                    </div>
                    
                    <div class="dian-api-form-row">
                        <div class="dian-api-form-field">
                            <label for="dian_api_company_logo">Logo de la Empresa (URL)</label>
                            <input type="url" id="dian_api_company_logo" name="dian_api_company_logo" value="<?php echo esc_attr(get_option('dian_api_company_logo')); ?>">
                            <p class="description">URL de la imagen del logo de la empresa.</p>
                        </div>
                    </div>
                    
                    <div class="dian-api-form-actions">
                        <?php submit_button('Guardar Datos de Empresa'); ?>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="dian-api-tab-content" id="tab-pdf">
            <div class="dian-api-section">
                <div class="dian-api-section-header">
                    <h3>Configuración de PDFs</h3>
                </div>
                
                <form method="post" action="options.php" class="dian-api-form">
                    <?php settings_fields('dian_api_pdf_options'); ?>
                    
                    <div class="dian-api-form-row">
                        <div class="dian-api-form-field">
                            <label for="dian_api_pdf_footer_text">Texto del Pie de Página</label>
                            <input type="text" id="dian_api_pdf_footer_text" name="dian_api_pdf_footer_text" value="<?php echo esc_attr(get_option('dian_api_pdf_footer_text', 'Documento generado electrónicamente')); ?>">
                        </div>
                        
                        <div class="dian-api-form-field">
                            <label for="dian_api_pdf_primary_color">Color Principal</label>
                            <input type="color" id="dian_api_pdf_primary_color" name="dian_api_pdf_primary_color" value="<?php echo esc_attr(get_option('dian_api_pdf_primary_color', '#3498db')); ?>">
                        </div>
                    </div>
                    
                    <div class="dian-api-form-row">
                        <div class="dian-api-form-field">
                            <label for="dian_api_pdf_paper_size">Tamaño de Papel</label>
                            <select id="dian_api_pdf_paper_size" name="dian_api_pdf_paper_size">
                                <option value="letter" <?php selected(get_option('dian_api_pdf_paper_size', 'letter'), 'letter'); ?>>Carta (Letter)</option>
                                <option value="legal" <?php selected(get_option('dian_api_pdf_paper_size'), 'legal'); ?>>Oficio (Legal)</option>
                                <option value="A4" <?php selected(get_option('dian_api_pdf_paper_size'), 'A4'); ?>>A4</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="dian-api-form-actions">
                        <?php submit_button('Guardar Configuración PDF'); ?>
                    </div>
                </form>
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
    
    // Guardar cliente
    $('#form-cliente').on('submit', function(e) {
        e.preventDefault();
        
        var $form = $(this);
        var $spinner = $form.find('.spinner');
        var formData = $form.serialize();
        
        $spinner.addClass('is-active');
        
        $.post(ajaxurl, formData, function(response) {
            $spinner.removeClass('is-active');
            
            if (response.success) {
                alert(response.data);
                window.location.reload();
            } else {
                alert('Error: ' + response.data);
            }
        });
    });
    
    // Nuevo cliente
    $('#btn-nuevo-cliente').on('click', function() {
        // Limpiar formulario
        $('#cliente_id').val('').prop('readonly', false);
        $('#id_software').val('');
        $('#software_pin').val('');
        $('#test_set_id').val('');
        $('#certificado_ruta').val('');
        $('#certificado_clave').val('');
        $('#url_ws_validacion').val('https://vpfe-hab.dian.gov.co/WcfDianCustomerServices.svc');
        $('#url_ws_produccion').val('https://vpfe.dian.gov.co/WcfDianCustomerServices.svc');
        $('#modo_operacion').val('habilitacion');
    });

    // Verificar certificado
    $('#btn-verificar-certificado').on('click', function() {
        var $button = $(this);
        var $spinner = $button.next('.spinner');
        var $status = $('#certificado_status');
        var certificadoRuta = $('#certificado_ruta').val();
        
        if (!certificadoRuta) {
            $status.html('<div class="notice notice-error inline"><p>Por favor ingrese la ruta del certificado primero.</p></div>').show();
            return;
        }
        
        $spinner.addClass('is-active');
        $button.prop('disabled', true);
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'dian_api_verificar_certificado',
                nonce: '<?php echo wp_create_nonce('dian_api_nonce'); ?>',
                certificado_ruta: certificadoRuta
            },
            success: function(response) {
                $spinner.removeClass('is-active');
                $button.prop('disabled', false);
                
                if (response.success) {
                    $status.html('<div class="notice notice-success inline"><p>' + response.data + '</p></div>').show();
                } else {
                    $status.html('<div class="notice notice-error inline"><p>' + response.data + '</p></div>').show();
                }
            },
            error: function() {
                $spinner.removeClass('is-active');
                $button.prop('disabled', false);
                $status.html('<div class="notice notice-error inline"><p>Error en la comunicación con el servidor.</p></div>').show();
            }
        });
    });

    // Manejo de carga de certificado
    $('#btn-cargar-certificado').on('click', function() {
        $('#certificado_file').trigger('click');
    });

    $('#certificado_file').on('change', function() {
        var file = this.files[0];
        if (!file) return;
        
        // Verificar extensión
        var extension = file.name.split('.').pop().toLowerCase();
        if (extension !== 'p12' && extension !== 'pfx') {
            alert('El archivo debe ser un certificado en formato PKCS#12 (.p12 o .pfx)');
            return;
        }
        
        // Mostrar spinner
        var $spinner = $('#btn-cargar-certificado').next('.spinner');
        $spinner.addClass('is-active');
        
        // Preparar datos para enviar
        var formData = new FormData();
        formData.append('action', 'dian_api_cargar_certificado');
        formData.append('nonce', '<?php echo wp_create_nonce('dian_api_nonce'); ?>');
        formData.append('certificado', file);
        
        // Enviar archivo vía AJAX
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
                $spinner.removeClass('is-active');
                
                if (response.success) {
                    $('#certificado_ruta').val(response.data.ruta);
                    $('#certificado_status').html('<div class="notice notice-success inline"><p>' + response.data.mensaje + '</p></div>').show();
                } else {
                    $('#certificado_status').html('<div class="notice notice-error inline"><p>' + response.data + '</p></div>').show();
                }
            },
            error: function() {
                $spinner.removeClass('is-active');
                $('#certificado_status').html('<div class="notice notice-error inline"><p>Error en la comunicación con el servidor.</p></div>').show();
            }
        });
    });

    // Eliminar cliente
    $(document).on('click', '.eliminar-cliente', function() {
        var $button = $(this);
        var clienteId = $button.data('id');
        
        if (confirm('¿Está seguro de eliminar este cliente? Esta acción no se puede deshacer.')) {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'dian_api_eliminar_cliente',
                    nonce: '<?php echo wp_create_nonce('dian_api_nonce'); ?>',
                    cliente_id: clienteId
                },
                success: function(response) {
                    if (response.success) {
                        alert(response.data);
                        // Recargar la página para mostrar los cambios
                        window.location.reload();
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