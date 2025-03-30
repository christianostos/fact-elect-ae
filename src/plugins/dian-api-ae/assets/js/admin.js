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
     * Inicializar el formulario de API Key
     */
    function initApiKeyForm() {
        var isSubmitting = false;
        
        $('#form-api-key').on('submit', function(e) {
            e.preventDefault();
            
            // Evitar envíos duplicados
            if (isSubmitting) {
                return false;
            }
            
            isSubmitting = true;
            
            var $form = $(this);
            var $submitButton = $form.find('button[type="submit"]');
            var $spinner = $form.find('.spinner');
            var formData = $form.serialize();
            
            // Agregar un ID de solicitud único si no existe
            if (!formData.includes('_request_id')) {
                var requestId = 'request_' + Math.random().toString(36).substr(2, 9) + '_' + new Date().getTime();
                formData += '&_request_id=' + requestId;
            }
            
            $submitButton.prop('disabled', true);
            $spinner.addClass('is-active');
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: formData,
                success: function(response) {
                    $spinner.removeClass('is-active');
                    $submitButton.prop('disabled', false);
                    isSubmitting = false;
                    
                    if (response.success) {
                        // Mostrar resultado
                        $('#generated_api_key').val(response.data.api_key);
                        $('#generated_api_secret').val(response.data.api_secret);
                        $('#api-key-result').show();
                        
                        // Limpiar formulario
                        $form[0].reset();
                        
                        // Añadir un nuevo ID de solicitud para el próximo envío
                        if ($form.find('input[name="_request_id"]').length) {
                            $form.find('input[name="_request_id"]').val('request_' + Math.random().toString(36).substr(2, 9) + '_' + new Date().getTime());
                        } else {
                            $form.append('<input type="hidden" name="_request_id" value="request_' + Math.random().toString(36).substr(2, 9) + '_' + new Date().getTime() + '">');
                        }
                    } else {
                        alert('Error: ' + response.data);
                    }
                },
                error: function() {
                    $spinner.removeClass('is-active');
                    $submitButton.prop('disabled', false);
                    isSubmitting = false;
                    alert('Error en la comunicación con el servidor');
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
                    var doc = response.data.documento;
                    var html = '<div class="dian-api-document-details">';
                    
                    // Información básica
                    html += '<div class="dian-api-section">';
                    html += '<div class="dian-api-section-header"><h3>Información del Documento</h3></div>';
                    html += '<table class="wp-list-table widefat fixed">';
                    html += '<tr><th>Tipo:</th><td>' + doc.tipo_documento + '</td></tr>';
                    html += '<tr><th>Número:</th><td>' + doc.prefijo + doc.numero + '</td></tr>';
                    html += '<tr><th>Emisor:</th><td>' + doc.emisor_razon_social + ' (' + doc.emisor_nit + ')</td></tr>';
                    html += '<tr><th>Receptor:</th><td>' + doc.receptor_razon_social + ' (' + doc.receptor_documento + ')</td></tr>';
                    html += '<tr><th>Fecha:</th><td>' + doc.fecha_emision + '</td></tr>';
                    html += '<tr><th>Vencimiento:</th><td>' + doc.fecha_vencimiento + '</td></tr>';
                    html += '<tr><th>Valor:</th><td>' + Number(doc.valor_total).toLocaleString('es-CO', {style: 'currency', currency: doc.moneda}) + '</td></tr>';
                    
                    // Estado con clase de color
                    var estadoClass = '';
                    switch (doc.estado) {
                        case 'generado':
                            estadoClass = 'dian-api-status-warning';
                            break;
                        case 'enviado':
                            estadoClass = 'dian-api-status-info';
                            break;
                        case 'aceptado':
                            estadoClass = 'dian-api-status-ok';
                            break;
                        case 'rechazado':
                            estadoClass = 'dian-api-status-error';
                            break;
                    }
                    html += '<tr><th>Estado:</th><td><span class="dian-api-status ' + estadoClass + '">' + doc.estado + '</span></td></tr>';
                    
                    if (doc.track_id) {
                        html += '<tr><th>Track ID:</th><td>' + doc.track_id + '</td></tr>';
                    }
                    if (doc.cufe) {
                        html += '<tr><th>CUFE:</th><td>' + doc.cufe + '</td></tr>';
                    }
                    html += '</table>';
                    html += '</div>';
                    
                    // Mostrar XML
                    if (doc.archivo_xml) {
                        html += '<div class="dian-api-section">';
                        html += '<div class="dian-api-section-header"><h3>XML del Documento</h3></div>';
                        html += '<div class="dian-api-xml-container">';
                        html += '<textarea readonly style="width:100%; height:300px; font-family:monospace;">' + doc.archivo_xml + '</textarea>';
                        html += '<p><button type="button" class="button copy-xml">Copiar XML</button></p>';
                        html += '</div>';
                        html += '</div>';
                    }
                    
                    // Mostrar respuesta DIAN si existe
                    if (doc.respuesta_dian) {
                        html += '<div class="dian-api-section">';
                        html += '<div class="dian-api-section-header"><h3>Respuesta DIAN</h3></div>';
                        html += '<div class="dian-api-response-container">';
                        html += '<textarea readonly style="width:100%; height:150px; font-family:monospace;">' + doc.respuesta_dian + '</textarea>';
                        html += '</div>';
                        html += '</div>';
                    }
                    
                    // Acciones
                    html += '<div class="dian-api-document-modal-actions">';
                    html += '<button type="button" class="button view-pdf-modal" data-id="' + doc.id + '">Ver PDF</button> ';
                    if (doc.estado === 'generado') {
                        html += '<button type="button" class="button button-primary send-document-modal" data-cliente-id="' + doc.cliente_id + '" data-prefijo="' + doc.prefijo + '" data-numero="' + doc.numero + '" data-tipo="' + doc.tipo_documento + '">Enviar a DIAN</button> ';
                    }
                    if (doc.track_id) {
                        html += '<button type="button" class="button check-status-modal" data-track-id="' + doc.track_id + '">Verificar Estado</button> ';
                    }
                    html += '</div>';
                    
                    html += '</div>';
                    
                    // Mostrar en el modal
                    $('#documento-detalles').html(html);
                    $('#documento-modal').show();

                    $('.view-pdf-modal').on('click', function() {
                        $('#documento-modal').hide();
                        $('.dian-api-document-actions .view-pdf[data-id="' + $(this).data('id') + '"]').click();
                    });
                    
                    // Manejar el botón de copiar XML
                    $('.copy-xml').on('click', function() {
                        var $textarea = $(this).closest('.dian-api-xml-container').find('textarea');
                        $textarea.select();
                        document.execCommand('copy');
                        $(this).text('Copiado!');
                        setTimeout(function() {
                            $('.copy-xml').text('Copiar XML');
                        }, 2000);
                    });
                    
                    // Manejar botones de acción dentro del modal
                    $('.send-document-modal').on('click', function() {
                        $('#documento-modal').hide();
                        $('.dian-api-document-actions .send-document[data-cliente-id="' + $(this).data('cliente-id') + '"][data-prefijo="' + $(this).data('prefijo') + '"][data-numero="' + $(this).data('numero') + '"]').click();
                    });
                    
                    $('.check-status-modal').on('click', function() {
                        $('#documento-modal').hide();
                        $('.dian-api-document-actions .check-status[data-track-id="' + $(this).data('track-id') + '"]').click();
                    });
                    
                } else {
                    alert('Error: ' + response.data);
                }
            });
        });
        
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
                    // Actualizar el estado del documento en la interfaz para evitar recargar
                    var $parentRow = $button.closest('tr');
                    
                    // Cambiar la clase del estado
                    $parentRow.find('.dian-api-status')
                        .removeClass('dian-api-status-warning')
                        .addClass('dian-api-status-info')
                        .text('Enviado');
                    
                    // Ocultar el botón de enviar
                    $button.hide();
                    
                    // Si existe un track_id, mostrar el botón de verificar
                    if (response.data && response.data.track_id) {
                        var checkButton = '<button type="button" class="button button-small check-status" data-track-id="' + 
                            response.data.track_id + '">Verificar</button>';
                        $parentRow.find('.dian-api-document-actions').append(checkButton);
                        
                        // Reinicializar el botón de verificar
                        $('.check-status').off('click').on('click', function() {
                            var trackId = $(this).data('track-id');
                            var $verifyButton = $(this);
                            $verifyButton.prop('disabled', true).text(dian_api_admin.messages.checking);
                            
                            var verifyData = {
                                action: 'dian_api_verificar_estado',
                                nonce: dian_api_admin.nonce,
                                track_id: trackId
                            };
                            
                            $.post(ajaxurl, verifyData, function(verifyResponse) {
                                $verifyButton.prop('disabled', false).text('Verificar');
                                
                                if (verifyResponse.success) {
                                    // Mostrar resultado y recargar para reflejar cambios
                                    alert('Estado verificado. Se actualizará la página para mostrar el resultado.');
                                    location.reload();
                                } else {
                                    alert('Error al verificar: ' + verifyResponse.data);
                                }
                            }).fail(function() {
                                $verifyButton.prop('disabled', false).text('Verificar');
                                alert('Error de comunicación con el servidor');
                            });
                        });
                    }
                    
                    alert(dian_api_admin.messages.sent + '\n\nTrack ID: ' + (response.data ? response.data.track_id : 'No disponible'));
                } else {
                    alert('Error: ' + response.data);
                }
            }).fail(function() {
                $button.prop('disabled', false).text('Enviar');
                alert('Error de comunicación con el servidor');
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
                    var statusHtml = '<div class="dian-api-status-result">';
                    
                    if (response.data.es_valido) {
                        statusHtml += '<p><span class="dian-api-status dian-api-status-ok">Estado: ' + response.data.estado + '</span></p>';
                    } else {
                        statusHtml += '<p><span class="dian-api-status dian-api-status-error">Estado: ' + response.data.estado + '</span></p>';
                        
                        if (response.data.errores && response.data.errores.length > 0) {
                            statusHtml += '<p>Errores:</p><ul>';
                            response.data.errores.forEach(function(error) {
                                statusHtml += '<li><strong>' + error.code + ':</strong> ' + error.message + '</li>';
                            });
                            statusHtml += '</ul>';
                        }
                    }
                    
                    statusHtml += '</div>';
                    
                    // Crear un modal para mostrar el resultado
                    $('body').append(
                        '<div id="status-result-modal" class="dian-api-modal">' +
                        '<div class="dian-api-modal-content">' +
                        '<span class="dian-api-modal-close">&times;</span>' +
                        '<h2>Resultado de la Verificación</h2>' +
                        statusHtml +
                        '</div>' +
                        '</div>'
                    );
                    
                    $('#status-result-modal').show();
                    
                    // Cerrar modal al hacer clic en X
                    $('#status-result-modal .dian-api-modal-close').on('click', function() {
                        $('#status-result-modal').remove();
                    });
                    
                    // Actualizar la página después de cerrar el modal
                    $('#status-result-modal .dian-api-modal-close').on('click', function() {
                        location.reload();
                    });
                } else {
                    alert('Error: ' + response.data);
                }
            }).fail(function() {
                $button.prop('disabled', false).text('Verificar');
                alert('Error de comunicación con el servidor');
            });
        });

        // Ver PDF
        $('.view-pdf').on('click', function() {
            var documentoId = $(this).data('id');
            
            var $button = $(this);
            $button.prop('disabled', true);
            $button.text('Generando...');
            
            var data = {
                action: 'dian_api_generar_pdf',
                nonce: dian_api_admin.nonce,
                documento_id: documentoId
            };
            
            $.post(ajaxurl, data, function(response) {
                $button.prop('disabled', false);
                $button.text('PDF');
                
                if (response.success) {
                    // Abrir PDF en una nueva ventana
                    window.open(response.data.pdf_url, '_blank');
                } else {
                    alert('Error al generar el PDF: ' + (response.data || 'Error desconocido'));
                }
            }).fail(function(xhr) {
                $button.prop('disabled', false);
                $button.text('PDF');
                var errorMsg = xhr.responseJSON && xhr.responseJSON.data ? xhr.responseJSON.data : 'Error de comunicación con el servidor';
                alert('Error: ' + errorMsg);
            });
        });
    }
    
    /**
     * Inicializar el validador XML
     */
    function initValidadorXML() {
        $('#form-validador').on('submit', function(e) {
            e.preventDefault();
            
            var $form = $(this);
            var $spinner = $form.find('.spinner');
            var $resultado = $('#validador-resultado');
            var xmlContent = $('#xml_content').val();
            
            if (!xmlContent) {
                $resultado.html('<div class="dian-api-alert dian-api-alert-error"><p>Por favor, ingrese contenido XML para validar.</p></div>').show();
                return;
            }
            
            $spinner.addClass('is-active');
            
            var data = {
                action: 'dian_api_validar_xml',
                nonce: dian_api_admin.nonce,
                xml_content: xmlContent
            };
            
            $.post(ajaxurl, data, function(response) {
                $spinner.removeClass('is-active');
                
                if (response.success) {
                    var validation = response.data;
                    var html = '<div class="dian-api-validation-result">';
                    
                    if (validation.is_valid) {
                        html += '<div class="dian-api-alert dian-api-alert-success"><p><strong>El XML es válido según el esquema UBL 2.1</strong></p></div>';
                    } else {
                        html += '<div class="dian-api-alert dian-api-alert-error"><p><strong>El XML no es válido</strong></p>';
                        html += '<ul>';
                        for (var i = 0; i < validation.errors.length; i++) {
                            html += '<li>' + validation.errors[i] + '</li>';
                        }
                        html += '</ul></div>';
                    }
                    
                    html += '</div>';
                    $resultado.html(html).show();
                } else {
                    $resultado.html('<div class="dian-api-alert dian-api-alert-error"><p>' + response.data + '</p></div>').show();
                }
            }).fail(function() {
                $spinner.removeClass('is-active');
                $resultado.html('<div class="dian-api-alert dian-api-alert-error"><p>Error de comunicación con el servidor</p></div>').show();
            });
        });
    }

    /**
     * Inicializar el Set de Pruebas DIAN
     */
    function initSetPruebasDIAN() {
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
                                    (response.data.validation_result && response.data.validation_result.error_guardado ? 
                                        response.data.validation_result.error_guardado : 'Error desconocido') + '</p>' +
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
    }
    
    // Inicializar todo
    initUI();
    initClienteForm();
    initApiKeyForm();
    initDocumentActions();
    initValidadorXML();
    initSetPruebasDIAN();
});