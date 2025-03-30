<?php
/**
 * Página de herramientas del panel de administración
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

// Obtener rango de numeración
$resoluciones = array();
if (!empty($cliente_seleccionado)) {
    $resoluciones = $db->obtener_resoluciones_vigentes($cliente_seleccionado, 'factura');
}
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <div class="dian-api-tabs">
        <div class="dian-api-tab-navigation">
            <a href="#" class="dian-api-tab-link active" data-tab="validador">Validador XML</a>
            <a href="#" class="dian-api-tab-link" data-tab="generador">Generador XML de Prueba</a>
            <a href="#" class="dian-api-tab-link" data-tab="test">Set de Pruebas</a>
        </div>
        
        <div class="dian-api-tab-content active" id="tab-validador">
            <div class="dian-api-section">
                <div class="dian-api-section-header">
                    <h3>Validador de Documentos XML</h3>
                </div>
                
                <form id="form-validador" class="dian-api-form">
                    <div class="dian-api-form-row">
                        <div class="dian-api-form-field">
                            <label for="xml_content">Contenido XML</label>
                            <textarea id="xml_content" name="xml_content" rows="10"></textarea>
                            <p class="description">Pegue el contenido XML a validar.</p>
                        </div>
                    </div>
                    
                    <div class="dian-api-form-actions">
                        <button type="submit" class="button button-primary">Validar XML</button>
                        <span class="spinner"></span>
                    </div>
                </form>
                
                <div id="validador-resultado" style="display: none;"></div>
            </div>
        </div>
        
        <div class="dian-api-tab-content" id="tab-generador">
            <div class="dian-api-section">
                <div class="dian-api-section-header">
                    <h3>Generador de XML de Prueba</h3>
                    <p class="description">Esta herramienta genera un XML de factura de prueba según el estándar UBL 2.1 requerido por la DIAN.</p>
                </div>
                
                <form id="form-generador-xml" class="dian-api-form">
                    <input type="hidden" name="action" value="dian_api_generar_xml_prueba">
                    <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('dian_api_nonce'); ?>">
                    
                    <div class="dian-api-form-row">
                        <div class="dian-api-form-field">
                            <label for="genxml_cliente_id">Cliente</label>
                            <select id="genxml_cliente_id" name="cliente_id" required>
                                <option value="">Seleccionar Cliente</option>
                                <?php foreach ($clientes as $cliente): ?>
                                    <option value="<?php echo esc_attr($cliente['cliente_id']); ?>" <?php selected($cliente_seleccionado, $cliente['cliente_id']); ?>>
                                        <?php echo esc_html($cliente['cliente_id']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <p class="description">Cliente para el cual se generará el XML de prueba.</p>
                        </div>
                        
                        <div class="dian-api-form-field">
                            <label for="genxml_resolucion">Resolución</label>
                            <select id="genxml_resolucion" name="resolucion_id">
                                <option value="">Seleccionar Resolución</option>
                                <?php foreach ($resoluciones as $resolucion): ?>
                                    <option value="<?php echo esc_attr($resolucion['id']); ?>">
                                        <?php echo esc_html($resolucion['prefijo'] . ' - ' . $resolucion['numero_resolucion']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <p class="description">Resolución a utilizar para la factura de prueba.</p>
                        </div>
                    </div>
                    
                    <div class="dian-api-form-row">
                        <div class="dian-api-form-field">
                            <label for="genxml_receptor_tipo">Tipo de Receptor</label>
                            <select id="genxml_receptor_tipo" name="receptor_tipo">
                                <option value="persona_natural">Persona Natural</option>
                                <option value="persona_juridica">Persona Jurídica</option>
                            </select>
                        </div>
                        
                        <div class="dian-api-form-field">
                            <label for="genxml_receptor_documento">Documento Receptor</label>
                            <input type="text" id="genxml_receptor_documento" name="receptor_documento" placeholder="NIT o Cédula" value="1095123456">
                        </div>
                    </div>
                    
                    <div class="dian-api-form-row">
                        <div class="dian-api-form-field">
                            <label for="genxml_receptor_nombre">Nombre Receptor</label>
                            <input type="text" id="genxml_receptor_nombre" name="receptor_nombre" placeholder="Razón social o nombre" value="CLIENTE DE PRUEBA">
                        </div>
                        
                        <div class="dian-api-form-field">
                            <label for="genxml_direccion">Dirección</label>
                            <input type="text" id="genxml_direccion" name="receptor_direccion" placeholder="Dirección" value="Calle 123 # 45-67, Bogotá">
                        </div>
                    </div>
                    
                    <div class="dian-api-form-row">
                        <div class="dian-api-form-field">
                            <label for="genxml_num_items">Número de Items</label>
                            <select id="genxml_num_items" name="num_items">
                                <option value="1">1 Item</option>
                                <option value="2" selected>2 Items</option>
                                <option value="3">3 Items</option>
                                <option value="5">5 Items</option>
                            </select>
                            <p class="description">Cantidad de productos a incluir en la factura de prueba.</p>
                        </div>
                        
                        <div class="dian-api-form-field">
                            <label for="genxml_incluir_impuestos">Impuestos</label>
                            <select id="genxml_incluir_impuestos" name="incluir_impuestos">
                                <option value="si" selected>Incluir IVA (19%)</option>
                                <option value="no">Sin impuestos</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="dian-api-form-row">
                        <div class="dian-api-form-field">
                            <label for="genxml_validar">Validar XML</label>
                            <input type="checkbox" id="genxml_validar" name="validar_xml" value="1" checked>
                            <span class="description">Validar el XML generado contra el esquema XSD de UBL 2.1.</span>
                        </div>
                    </div>
                    
                    <div class="dian-api-form-actions">
                        <button type="submit" id="btn-generar-xml" class="button button-primary">Generar XML de Prueba</button>
                        <span class="spinner"></span>
                    </div>
                </form>
                
                <div id="generador-resultado" style="display: none;"></div>
            </div>
        </div>
        
        <div class="dian-api-tab-content" id="tab-test">
            <div class="dian-api-section">
                <div class="dian-api-section-header">
                    <h3>Set de Pruebas DIAN</h3>
                </div>
                
                <form id="form-test" class="dian-api-form">
                    <div class="dian-api-form-row">
                        <div class="dian-api-form-field">
                            <label for="test_cliente_id">Cliente</label>
                            <select id="test_cliente_id" name="cliente_id" required>
                                <option value="">Seleccionar Cliente</option>
                                <?php foreach ($clientes as $cliente): ?>
                                    <option value="<?php echo esc_attr($cliente['cliente_id']); ?>" <?php selected($cliente_seleccionado, $cliente['cliente_id']); ?>>
                                        <?php echo esc_html($cliente['cliente_id']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="dian-api-form-row">
                        <div class="dian-api-form-field">
                            <label for="test_set_id">Test Set ID</label>
                            <input type="text" id="test_set_id" name="test_set_id" placeholder="Proporcionado por la DIAN">
                            <p class="description">ID del set de pruebas proporcionado por la DIAN.</p>
                        </div>
                    </div>
                    
                    <div class="dian-api-form-actions">
                        <button type="button" id="btn-generar-test" class="button button-primary">Generar Documentos de Prueba</button>
                        <span class="spinner"></span>
                    </div>
                </form>
                
                <div id="test-resultado" style="display: none;"></div>
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
    
    // Validador XML
    $('#form-validador').on('submit', function(e) {
        e.preventDefault();
        
        // Implementación pendiente
        alert('Funcionalidad en desarrollo');
    });
    
    // Generar XML de prueba
    $('#form-generador-xml').on('submit', function(e) {
        e.preventDefault();
        
        var $form = $(this);
        var $spinner = $form.find('.spinner');
        var $boton = $form.find('#btn-generar-xml');
        var $resultado = $('#generador-resultado');
        
        // Mostrar spinner y desactivar botón
        $spinner.addClass('is-active');
        $boton.prop('disabled', true);
        
        // Enviar solicitud AJAX
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: $form.serialize(),
            success: function(response) {
                $spinner.removeClass('is-active');
                $boton.prop('disabled', false);
                
                if (response.success) {
                    var html = '<div class="dian-api-alert dian-api-alert-success">';
                    html += '<p><strong>XML generado correctamente</strong></p>';
                    html += '</div>';

                    // Sección para mostrar si se guardó el documento
                    if (response.data.validation_result && response.data.validation_result.documento_guardado) {
                        html += '<div class="dian-api-alert dian-api-alert-success">';
                        html += '<p><strong>Documento guardado correctamente en la base de datos</strong></p>';
                        html += '<p>Ya puedes verlo en la sección de Documentos.</p>';
                        html += '<p><a href="' + ajaxurl.replace('admin-ajax.php', 'admin.php') + '?page=dian-api-documentos&cliente_id=' + $('#genxml_cliente_id').val() + '" class="button button-primary">Ver en Documentos</a></p>';
                        html += '</div>';
                    } else if (response.data.validation_result && response.data.validation_result.is_valid) {
                        html += '<div class="dian-api-alert dian-api-alert-warning">';
                        html += '<p><strong>XML válido pero no se guardó como documento</strong></p>';
                        html += '<p>Error: ' + (response.data.validation_result.error_guardado || 'Desconocido') + '</p>';
                        html += '</div>';
                    }
                    
                    if (response.data.xml) {
                        html += '<div class="dian-api-card">';
                        html += '<div class="dian-api-section-header"><h3>XML Generado</h3></div>';
                        html += '<div class="dian-api-card-content">';
                        html += '<textarea readonly style="width:100%; height:300px; font-family:monospace;">' + response.data.xml + '</textarea>';
                        html += '<p><button type="button" class="button copy-xml">Copiar XML</button></p>';
                        html += '</div></div>';
                    }
                    
                    if (response.data.validation_result) {
                        var validClass = response.data.validation_result.is_valid ? 'dian-api-alert-success' : 'dian-api-alert-error';
                        var validText = response.data.validation_result.is_valid ? 'El XML es válido' : 'El XML no es válido';
                        
                        html += '<div class="dian-api-card">';
                        html += '<div class="dian-api-section-header"><h3>Resultado de Validación</h3></div>';
                        html += '<div class="dian-api-card-content">';
                        html += '<div class="dian-api-alert ' + validClass + '">';
                        html += '<p><strong>' + validText + '</strong></p>';
                        
                        if (!response.data.validation_result.is_valid && response.data.validation_result.errors) {
                            html += '<ul>';
                            response.data.validation_result.errors.forEach(function(error) {
                                // Limpiamos el error para que sea más comprensible
                                var cleanError = error;
                                if (error.includes('Elemento obligatorio no encontrado: /ubl:Invoice')) {
                                    cleanError = 'Error al validar estructura XML. El elemento raíz debe ser &lt;Invoice&gt; con el espacio de nombres correcto.';
                                } else if (error.includes('Elemento obligatorio no encontrado: //cbc:')) {
                                    cleanError = 'Falta elemento obligatorio: ' + error.split('//cbc:')[1];
                                } else if (error.includes('Elemento obligatorio no encontrado: //cac:')) {
                                    cleanError = 'Falta elemento obligatorio: ' + error.split('//cac:')[1];
                                }
                                html += '<li>' + cleanError + '</li>';
                            });
                            html += '</ul>';
                        }
                        
                        html += '</div>';
                        
                        // Agregar información de depuración si está disponible
                        if (response.data.debug_info) {
                            html += '<div class="dian-api-debug" style="margin-top: 15px; padding: 10px; background: #f8f8f8; border: 1px solid #ddd;">';
                            html += '<h4>Información de depuración</h4>';
                            html += '<pre>' + JSON.stringify(response.data.debug_info, null, 2) + '</pre>';
                            html += '</div>';
                        }
                        
                        html += '</div></div>';
                    }
                    
                    $resultado.html(html).show();
                    
                    // Manejar el botón de copiar
                    $('.copy-xml').on('click', function() {
                        var $textarea = $(this).closest('.dian-api-card-content').find('textarea');
                        $textarea.select();
                        document.execCommand('copy');
                        $(this).text('Copiado!');
                        setTimeout(function() {
                            $('.copy-xml').text('Copiar XML');
                        }, 2000);
                    });
                } else {
                    $resultado.html('<div class="dian-api-alert dian-api-alert-error"><p><strong>Error: </strong>' + 
                        response.data + '</p></div>').show();
                }
            },
            error: function() {
                $spinner.removeClass('is-active');
                $boton.prop('disabled', false);
                $resultado.html('<div class="dian-api-alert dian-api-alert-error"><p><strong>Error: </strong>Ocurrió un error en la comunicación con el servidor.</p></div>').show();
            }
        });
    });
    
    // Generar documentos de prueba
    $('#btn-generar-test').on('click', function() {
        var $button = $(this);
        var $spinner = $button.siblings('.spinner');
        var $resultado = $('#test-resultado');
        
        var cliente_id = $('#test_cliente_id').val();
        var test_set_id = $('#test_set_id').val();
        
        if (!cliente_id) {
            alert('Debe seleccionar un cliente');
            return;
        }
        
        if (!test_set_id) {
            alert('Debe ingresar el ID del set de pruebas proporcionado por la DIAN');
            return;
        }
        
        // Mostrar spinner y desactivar botón
        $spinner.addClass('is-active');
        $button.prop('disabled', true);
        
        // Iniciar la generación de documentos de prueba
        $resultado.html('<div class="dian-api-alert dian-api-alert-info"><p>Iniciando generación de set de pruebas DIAN...</p></div>').show();
        
        // Tipos de documentos a generar
        var tipos_documentos = [
            {tipo: 'factura', nombre: 'Factura Electrónica'},
            {tipo: 'nota_credito', nombre: 'Nota Crédito'},
            {tipo: 'nota_debito', nombre: 'Nota Débito'}
        ];
        
        // Contador de documentos generados
        var documentos_generados = 0;
        var documentos_fallidos = 0;
        var documentos_total = tipos_documentos.length;
        
        // Función para actualizar el progreso
        function actualizarProgreso() {
            var porcentaje = Math.round((documentos_generados + documentos_fallidos) * 100 / documentos_total);
            var html = '<div class="dian-api-progress-bar">';
            html += '<div class="dian-api-progress" style="width: ' + porcentaje + '%"></div>';
            html += '</div>';
            html += '<p>Progreso: ' + (documentos_generados + documentos_fallidos) + ' de ' + documentos_total + ' documentos</p>';
            
            return html;
        }
        
        // Función para generar cada documento
        function generarDocumento(indice) {
            if (indice >= tipos_documentos.length) {
                // Todos los documentos han sido procesados
                var resultado_final = '';
                
                if (documentos_generados > 0) {
                    resultado_final += '<div class="dian-api-alert dian-api-alert-success">';
                    resultado_final += '<p><strong>' + documentos_generados + ' documentos generados correctamente</strong></p>';
                    resultado_final += '</div>';
                }
                
                if (documentos_fallidos > 0) {
                    resultado_final += '<div class="dian-api-alert dian-api-alert-error">';
                    resultado_final += '<p><strong>' + documentos_fallidos + ' documentos fallaron</strong></p>';
                    resultado_final += '</div>';
                }
                
                resultado_final += '<p>Puedes ver los documentos generados en la <a href="' + ajaxurl.replace('admin-ajax.php', 'admin.php') + '?page=dian-api-documentos&cliente_id=' + cliente_id + '">sección de Documentos</a>.</p>';
                
                $resultado.html(resultado_final);
                $spinner.removeClass('is-active');
                $button.prop('disabled', false);
                return;
            }
            
            var documento = tipos_documentos[indice];
            
            // Actualizar UI para mostrar el documento actual
            $resultado.html(
                '<div class="dian-api-alert dian-api-alert-info">' +
                '<p>Generando ' + documento.nombre + '...</p>' +
                '</div>' +
                actualizarProgreso()
            );
            
            // Generar el documento
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'dian_api_generar_xml_prueba',
                    nonce: $('#form-test input[name="nonce"]').val() || dian_api_admin.nonce,
                    cliente_id: cliente_id,
                    test_set_id: test_set_id,
                    tipo_documento: documento.tipo,
                    // Datos predeterminados para prueba
                    receptor_tipo: 'persona_juridica',
                    receptor_documento: '900197264',
                    receptor_nombre: 'ASOCIACION DE TRANSPORTADORES',
                    receptor_direccion: 'AV BOYACA 17 12',
                    num_items: 2,
                    incluir_impuestos: 'si',
                    validar_xml: 1,
                    generar_para_test: 1
                },
                success: function(response) {
                    if (response.success) {
                        documentos_generados++;
                        
                        // Enviar el documento a la DIAN si se generó correctamente
                        if (response.data.validation_result && response.data.validation_result.documento_guardado) {
                            var doc_id = response.data.validation_result.documento_id;
                            
                            // Mostrar información en UI
                            $resultado.html(
                                '<div class="dian-api-alert dian-api-alert-success">' +
                                '<p>' + documento.nombre + ' generada correctamente. Enviando a DIAN...</p>' +
                                '</div>' +
                                actualizarProgreso()
                            );
                            
                            // Enviar documento a DIAN
                            $.ajax({
                                url: ajaxurl,
                                type: 'POST',
                                data: {
                                    action: 'dian_api_enviar_documento_test',
                                    nonce: dian_api_admin.nonce,
                                    documento_id: doc_id
                                },
                                success: function(sendResponse) {
                                    if (sendResponse.success) {
                                        $resultado.html(
                                            '<div class="dian-api-alert dian-api-alert-success">' +
                                            '<p>' + documento.nombre + ' enviada a DIAN correctamente.</p>' +
                                            '</div>' +
                                            actualizarProgreso()
                                        );
                                    } else {
                                        $resultado.html(
                                            '<div class="dian-api-alert dian-api-alert-warning">' +
                                            '<p>' + documento.nombre + ' generada pero falló al enviar a DIAN: ' + sendResponse.data + '</p>' +
                                            '</div>' +
                                            actualizarProgreso()
                                        );
                                    }
                                    
                                    // Continuar con el siguiente documento
                                    setTimeout(function() {
                                        generarDocumento(indice + 1);
                                    }, 1000);
                                },
                                error: function() {
                                    $resultado.html(
                                        '<div class="dian-api-alert dian-api-alert-error">' +
                                        '<p>Error de comunicación al enviar ' + documento.nombre + ' a DIAN</p>' +
                                        '</div>' +
                                        actualizarProgreso()
                                    );
                                    
                                    // Continuar con el siguiente documento
                                    setTimeout(function() {
                                        generarDocumento(indice + 1);
                                    }, 1000);
                                }
                            });
                        } else {
                            // Documento generado pero no guardado
                            documentos_fallidos++;
                            $resultado.html(
                                '<div class="dian-api-alert dian-api-alert-warning">' +
                                '<p>' + documento.nombre + ' generada pero no guardada: ' + 
                                (response.data.validation_result.error_guardado || 'Error desconocido') + '</p>' +
                                '</div>' +
                                actualizarProgreso()
                            );
                            
                            // Continuar con el siguiente documento
                            setTimeout(function() {
                                generarDocumento(indice + 1);
                            }, 1000);
                        }
                    } else {
                        documentos_fallidos++;
                        $resultado.html(
                            '<div class="dian-api-alert dian-api-alert-error">' +
                            '<p>Error al generar ' + documento.nombre + ': ' + response.data + '</p>' +
                            '</div>' +
                            actualizarProgreso()
                        );
                        
                        // Continuar con el siguiente documento
                        setTimeout(function() {
                            generarDocumento(indice + 1);
                        }, 1000);
                    }
                },
                error: function() {
                    documentos_fallidos++;
                    $resultado.html(
                        '<div class="dian-api-alert dian-api-alert-error">' +
                        '<p>Error de comunicación al generar ' + documento.nombre + '</p>' +
                        '</div>' +
                        actualizarProgreso()
                    );
                    
                    // Continuar con el siguiente documento
                    setTimeout(function() {
                        generarDocumento(indice + 1);
                    }, 1000);
                }
            });
        }
        
        // Iniciar la generación
        generarDocumento(0);
    });
});
</script>