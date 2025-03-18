/**
 * Scripts para el área de administración
 */
jQuery(document).ready(function($) {
    
    /**
     * Manejador genérico para pestañas
     */
    function initTabs() {
        $('.dian-api-tab-link').on('click', function(e) {
            e.preventDefault();
            var tabId = $(this).data('tab');
            
            // Activar tab
            $(this).parent().find('.dian-api-tab-link').removeClass('active');
            $(this).addClass('active');
            
            // Mostrar contenido
            $(this).closest('.dian-api-tabs').find('.dian-api-tab-content').removeClass('active');
            $('#tab-' + tabId).addClass('active');
        });
    }
    
    /**
     * Inicializar elementos de la interfaz
     */
    function initUI() {
        // Pestañas
        initTabs();
        
        // Modales
        $('.dian-api-modal-close').on('click', function() {
            $(this).closest('.dian-api-modal').hide();
        });
        
        // Cerrar modal al hacer clic fuera
        $(window).on('click', function(e) {
            if ($(e.target).hasClass('dian-api-modal')) {
                $('.dian-api-modal').hide();
            }
        });
    }
    
    /**
     * Inicializar el formulario de cliente
     */
    function initClienteForm() {
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
    }
    
    /**
     * Inicializar el formulario de resolución
     */
    function initResolucionForm() {
        $('#form-resolucion').on('submit', function(e) {
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
    }
    
    /**
     * Inicializar el formulario de API Key
     */
    function initApiKeyForm() {
        $('#form-api-key').on('submit', function(e) {
            e.preventDefault();
            
            var $form = $(this);
            var $spinner = $form.find('.spinner');
            var formData = $form.serialize();
            
            $spinner.addClass('is-active');
            
            $.post(ajaxurl, formData, function(response) {
                $spinner.removeClass('is-active');
                
                if (response.success) {
                    // Mostrar resultado
                    $('#generated_api_key').val(response.data.api_key);
                    $('#generated_api_secret').val(response.data.api_secret);
                    $('#api-key-result').show();
                    
                    // Limpiar formulario
                    $form[0].reset();
                } else {
                    alert('Error: ' + response.data);
                }
            });
        });
    }
    
    /**
     * Inicializar acciones de documentos
     */
    function initDocumentActions() {
        // Ver documento
        $('.view-document').on('click', function() {
            var clienteId = $(this).data('cliente-id');
            var prefijo = $(this).data('prefijo');
            var numero = $(this).data('numero');
            var tipo = $(this).data('tipo');
            
            var data = {
                action: 'dian_api_obtener_documento',
                nonce: dian_api_admin.nonce,
                cliente_id: clienteId,
                prefijo: prefijo,
                numero: numero,
                tipo_documento: tipo
            };
            
            $.post(ajaxurl, data, function(response) {
                if (response.success) {
                    // Mostrar detalles del documento
                    var documento = response.data.documento;
                    var html = '<div class="dian-api-document-details">';
                    html += '<p><strong>Cliente:</strong> ' + documento.cliente_id + '</p>';
                    html += '<p><strong>Número:</strong> ' + documento.prefijo + documento.numero + '</p>';
                    html += '<p><strong>Emisor:</strong> ' + documento.emisor_razon_social + ' (' + documento.emisor_nit + ')</p>';
                    html += '<p><strong>Receptor:</strong> ' + documento.receptor_razon_social + ' (' + documento.receptor_documento + ')</p>';
                    html += '<p><strong>Fecha:</strong> ' + documento.fecha_emision + '</p>';
                    html += '<p><strong>Valor:</strong> $' + parseFloat(documento.valor_total).toLocaleString() + '</p>';
                    html += '<p><strong>Estado:</strong> ' + documento.estado + '</p>';
                    
                    if (documento.track_id) {
                        html += '<p><strong>Track ID:</strong> ' + documento.track_id + '</p>';
                    }
                    
                    if (documento.cufe) {
                        html += '<p><strong>CUFE:</strong> ' + documento.cufe + '</p>';
                    }
                    
                    html += '</div>';
                    
                    $('#documento-detalles').html(html);
                    $('#documento-modal').show();
                } else {
                    alert('Error: ' + response.data);
                }
            });
        });
        
        // Enviar documento
        $('.send-document').on('click', function() {
            if (!confirm(dian_api_admin.messages.confirm_send)) {
                return;
            }
            
            var clienteId = $(this).data('cliente-id');
            var prefijo = $(this).data('prefijo');
            var numero = $(this).data('numero');
            var tipo = $(this).data('tipo');
            
            var $button = $(this);
            $button.prop('disabled', true).text(dian_api_admin.messages.sending);
            
            var data = {
                action: 'dian_api_enviar_documento',
                nonce: dian_api_admin.nonce,
                cliente_id: clienteId,
                prefijo: prefijo,
                numero: numero,
                tipo_documento: tipo
            };
            
            $.post(ajaxurl, data, function(response) {
                $button.prop('disabled', false).text('Enviar');
                
                if (response.success) {
                    alert(dian_api_admin.messages.sent);
                    window.location.reload();
                } else {
                    alert('Error: ' + response.data);
                }
            });
        });
        
        // Verificar estado
        $('.check-status').on('click', function() {
            var trackId = $(this).data('track-id');
            
            var $button = $(this);
            $button.prop('disabled', true).text(dian_api_admin.messages.checking);
            
            var data = {
                action: 'dian_api_verificar_estado',
                nonce: dian_api_admin.nonce,
                track_id: trackId
            };
            
            $.post(ajaxurl, data, function(response) {
                $button.prop('disabled', false).text('Verificar');
                
                if (response.success) {
                    var mensaje = dian_api_admin.messages.status_updated + '\n\n';
                    mensaje += 'Estado: ' + response.data.estado + '\n';
                    mensaje += 'Código: ' + response.data.codigo_estado + '\n';
                    mensaje += 'Descripción: ' + response.data.descripcion_estado + '\n';
                    mensaje += 'Es válido: ' + (response.data.es_valido ? 'Sí' : 'No');
                    
                    if (response.data.errores && response.data.errores.length > 0) {
                        mensaje += '\n\nErrores:\n';
                        for (var i = 0; i < response.data.errores.length; i++) {
                            var error = response.data.errores[i];
                            mensaje += '- ' + error.code + ': ' + error.message + '\n';
                        }
                    }
                    
                    alert(mensaje);
                    window.location.reload();
                } else {
                    alert('Error: ' + response.data);
                }
            });
        });
    }
    
    // Inicializar todo
    initUI();
    initClienteForm();
    initResolucionForm();
    initApiKeyForm();
    initDocumentActions();
});