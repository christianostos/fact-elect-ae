/**
 * DIAN ERP AE - Administración JavaScript
 * JavaScript modernizado para la interfaz de administración del plugin
 */

(function($) {
    'use strict';

    // Objeto principal de administración
    const DianAdmin = {
        // Inicialización principal
        init: function() {
            this.initTabs();
            this.initAccordions();
            this.initAPIButtons();
            this.initCertificateUpload();
            this.initFormValidation();
            this.initTooltips();
            this.initResponseFields();
        },

        // Inicializar sistema de pestañas
        initTabs: function() {
            const $tabs = $('.dian-tab');
            const tabContent = $('.dian-tab-content');
            const tabParam = new URLSearchParams(window.location.search).get('tab');
            
            // Manejo del cambio entre pestañas
            $tabs.on('click', function(e) {
                e.preventDefault();
                
                const tabId = $(this).data('tab');
                
                // Actualizar URLs y estado activo
                $tabs.removeClass('active');
                $(this).addClass('active');
                
                // Actualizar la URL del navegador sin recargar
                const currentUrl = new URL(window.location.href);
                if (tabId === 'general') {
                    currentUrl.searchParams.delete('tab');
                } else {
                    currentUrl.searchParams.set('tab', tabId);
                }
                window.history.pushState({}, '', currentUrl.toString());
                
                // Mostrar contenido correspondiente
                tabContent.hide();
                $('#' + tabId + '-content').fadeIn(300);
                
                // Disparar evento para que otros componentes reaccionen
                $(document).trigger('dianTabChanged', [tabId]);
            });
            
            // Activar pestaña inicial
            if (tabParam) {
                $(`.dian-tab[data-tab="${tabParam}"]`).trigger('click');
            } else {
                $('.dian-tab[data-tab="general"]').trigger('click');
            }
        },
        
        // Inicializar sistema de acordeones
        initAccordions: function() {
            $('.dian-accordion-header').on('click', function() {
                const $accordion = $(this).closest('.dian-accordion');
                
                // Toggle del estado activo
                $accordion.toggleClass('active');
                
                // Animar la apertura/cierre
                const $content = $accordion.find('.dian-accordion-content');
                if ($accordion.hasClass('active')) {
                    $content.slideDown(300);
                } else {
                    $content.slideUp(300);
                }
            });
        },
        
        // Inicializar botones de API
        initAPIButtons: function() {
            // Manejar todos los botones de API de forma unificada
            $('[data-api-action]').on('click', function() {
                const $button = $(this);
                const action = $button.data('api-action');
                const confirmMsg = $button.data('confirm-msg');
                
                // Confirmar acción si es necesario
                if (confirmMsg && !confirm(confirmMsg)) {
                    return;
                }
                
                // Agregar estado de carga
                $button.prop('disabled', true);
                const buttonText = $button.text();
                $button.html('<span class="dian-loader"></span> Procesando...');
                
                // Remover mensajes anteriores
                $button.closest('.dian-card').find('.dian-notice').remove();
                
                // Realizar la petición AJAX
                $.ajax({
                    url: dianAdminData.ajaxUrl,
                    type: 'POST',
                    data: {
                        action: action,
                        nonce: dianAdminData.nonce
                    },
                    success: function(response) {
                        // Restaurar botón
                        $button.prop('disabled', false);
                        $button.html(buttonText);
                        
                        // Procesar respuesta
                        DianAdmin.handleAPIResponse(response, $button);
                    },
                    error: function(xhr, status, error) {
                        // Restaurar botón
                        $button.prop('disabled', false);
                        $button.html(buttonText);
                        
                        // Mostrar mensaje de error
                        DianAdmin.showNotice({
                            type: 'error',
                            title: 'Error de conexión',
                            message: 'No se pudo conectar con el servidor: ' + error,
                            container: $button.closest('.dian-card')
                        });
                    }
                });
            });
            
            // Manejar caso especial para el botón de empresa
            $('#config_company').on('click', function() {
                const $button = $(this);
                
                $button.prop('disabled', true);
                const buttonText = $button.text();
                $button.html('<span class="dian-loader"></span> Enviando datos...');
                
                $.ajax({
                    type: "POST",
                    url: dianAdminData.ajaxUrl,
                    data: {
                        'action': 'api_service_config_company'
                    },
                    success: function(data) {
                        $button.prop('disabled', false);
                        $button.html(buttonText);
                        
                        try {
                            // Limpiar respuesta
                            if (typeof data === 'string') {
                                data = data.split('Array').join('');
                                var obj = JSON.parse(data);
                            } else {
                                var obj = data;
                            }
                            
                            if (obj.success === true) {
                                DianAdmin.showNotice({
                                    type: 'success',
                                    title: 'Datos enviados correctamente',
                                    message: 'La configuración de empresa ha sido enviada con éxito.',
                                    container: $button.closest('.dian-card')
                                });
                                
                                var formattedJson = JSON.stringify(obj, undefined, 4);
                                $('#facturaloperu_api_config_response').val(formattedJson);
                                
                                // Actualizar token si existe
                                if (obj.token && $('#facturaloperu_api_config_token').length) {
                                    $('#facturaloperu_api_config_token').val(obj.token);
                                }
                            } else {
                                DianAdmin.showNotice({
                                    type: 'error',
                                    title: 'Error',
                                    message: obj.message || 'No se pudo procesar la respuesta',
                                    container: $button.closest('.dian-card')
                                });
                            }
                        } catch (e) {
                            DianAdmin.showNotice({
                                type: 'error',
                                title: 'Error al procesar la respuesta',
                                message: e.message,
                                container: $button.closest('.dian-card')
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        $button.prop('disabled', false);
                        $button.html(buttonText);
                        
                        DianAdmin.showNotice({
                            type: 'error',
                            title: 'Error de conexión',
                            message: 'No se pudo conectar con el servidor: ' + error,
                            container: $button.closest('.dian-card')
                        });
                    }
                });
            });
            
            // Software
            $('#config_software').on('click', function() {
                const $button = $(this);
                
                $button.prop('disabled', true);
                const buttonText = $button.text();
                $button.html('<span class="dian-loader"></span> Enviando datos...');
                
                $.ajax({
                    type: "POST",
                    url: dianAdminData.ajaxUrl,
                    data: {
                        'action': 'api_service_config_software'
                    },
                    success: function(response) {
                        $button.prop('disabled', false);
                        $button.html(buttonText);
                        
                        try {
                            // Limpiar respuesta
                            if (typeof response === 'string') {
                                response = response.split('Array').join('');
                                var obj = JSON.parse(response);
                            } else {
                                var obj = response;
                            }
                            
                            if (obj.success === true) {
                                DianAdmin.showNotice({
                                    type: 'success',
                                    title: 'Datos enviados correctamente',
                                    message: 'La configuración de software ha sido enviada con éxito.',
                                    container: $button.closest('.dian-card')
                                });
                                
                                var formattedJson = JSON.stringify(obj, undefined, 4);
                                $('#facturaloperu_api_software_response').val(formattedJson);
                            } else {
                                DianAdmin.showNotice({
                                    type: 'error',
                                    title: 'Error',
                                    message: obj.message || 'No se pudo procesar la respuesta',
                                    container: $button.closest('.dian-card')
                                });
                            }
                        } catch (e) {
                            DianAdmin.showNotice({
                                type: 'error',
                                title: 'Error al procesar la respuesta',
                                message: e.message,
                                container: $button.closest('.dian-card')
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        $button.prop('disabled', false);
                        $button.html(buttonText);
                        
                        DianAdmin.showNotice({
                            type: 'error',
                            title: 'Error de conexión',
                            message: 'No se pudo conectar con el servidor: ' + error,
                            container: $button.closest('.dian-card')
                        });
                    }
                });
            });
            
            // Resolución
            $('#config_resolution').on('click', function() {
                const $button = $(this);
                
                $button.prop('disabled', true);
                const buttonText = $button.text();
                $button.html('<span class="dian-loader"></span> Enviando datos...');
                
                $.ajax({
                    type: "POST",
                    url: dianAdminData.ajaxUrl,
                    data: {
                        'action': 'api_service_config_resolution',
                        'type': 'resolution'
                    },
                    success: function(response) {
                        $button.prop('disabled', false);
                        $button.html(buttonText);
                        
                        try {
                            // Si la respuesta ya es un objeto
                            var obj;
                            if (typeof response === 'object') {
                                obj = response;
                            } else {
                                // Limpiar y parsear la respuesta
                                response = response.replace(/Array/g, '');
                                
                                // Buscar el inicio de un objeto JSON válido
                                var jsonStart = response.indexOf('{');
                                if (jsonStart >= 0) {
                                    response = response.substring(jsonStart);
                                }
                                
                                obj = JSON.parse(response);
                            }
                            
                            if (obj.success === true) {
                                DianAdmin.showNotice({
                                    type: 'success',
                                    title: 'Resolución configurada correctamente',
                                    message: 'La resolución ha sido configurada con éxito.',
                                    container: $button.closest('.dian-card')
                                });
                                
                                var formattedJson = JSON.stringify(obj, undefined, 4);
                                $('#facturaloperu_api_resolution_response').val(formattedJson);
                            } else {
                                DianAdmin.showNotice({
                                    type: 'error',
                                    title: 'Error',
                                    message: obj.message || 'Ocurrió un error al procesar la solicitud',
                                    container: $button.closest('.dian-card')
                                });
                            }
                        } catch (e) {
                            DianAdmin.showNotice({
                                type: 'error',
                                title: 'Error al procesar la respuesta',
                                message: e.message,
                                container: $button.closest('.dian-card')
                            });
                            
                            // Guardar la respuesta cruda en el textarea
                            $('#facturaloperu_api_resolution_response').val('Error al procesar la respuesta: ' + (typeof response === 'object' ? JSON.stringify(response) : response));
                        }
                    },
                    error: function(xhr, status, error) {
                        $button.prop('disabled', false);
                        $button.html(buttonText);
                        
                        DianAdmin.showNotice({
                            type: 'error',
                            title: 'Error de conexión',
                            message: 'No se pudo conectar con el servidor: ' + error,
                            container: $button.closest('.dian-card')
                        });
                        
                        $('#facturaloperu_api_resolution_response').val('Error en la solicitud: ' + error + '\n\nRespuesta: ' + xhr.responseText);
                    }
                });
            });
            
            // Inicialización
            $('#config_initial').on('click', function(e) {
                e.preventDefault();
                const $button = $(this);
                
                // Limpiar mensajes anteriores
                $('#api_error_message, #api_success_message').hide();
                
                // Mostrar indicador de carga
                $button.prop('disabled', true);
                const buttonText = $button.text();
                $button.html('<span class="dian-loader"></span> Procesando...');
                $('#loading_indicator').show();
                
                $.ajax({
                    type: 'POST',
                    url: dianAdminData.ajaxUrl,
                    data: {
                        action: 'api_service_config_initial',
                        type: 'initial',
                        nonce: dianAdminData.nonce
                    },
                    success: function(response) {
                        $button.prop('disabled', false);
                        $button.html(buttonText);
                        $('#loading_indicator').hide();
                        
                        try {
                            // Comprobar si la respuesta ya es un objeto
                            if (typeof response === 'object') {
                                var data = response;
                            } else {
                                // Intentar analizar la respuesta como JSON
                                var data = JSON.parse(response);
                            }
                            
                            if (data.success) {
                                DianAdmin.showNotice({
                                    type: 'success',
                                    title: 'Inicialización completada',
                                    message: data.message || 'La operación se completó con éxito',
                                    container: $button.closest('.dian-card')
                                });
                                
                                $('#facturaloperu_api_initial_response').val(JSON.stringify(data, null, 4));
                            } else {
                                var errorMsg = data.message || 'Error desconocido en la respuesta';
                                
                                DianAdmin.showNotice({
                                    type: 'error',
                                    title: 'Error de inicialización',
                                    message: errorMsg,
                                    container: $button.closest('.dian-card')
                                });
                                
                                $('#facturaloperu_api_initial_response').val(JSON.stringify(data, null, 4));
                            }
                        } catch (e) {
                            DianAdmin.showNotice({
                                type: 'error',
                                title: 'Error al procesar respuesta',
                                message: e.message,
                                container: $button.closest('.dian-card')
                            });
                            
                            $('#facturaloperu_api_initial_response').val(response);
                        }
                    },
                    error: function(xhr, status, error) {
                        $button.prop('disabled', false);
                        $button.html(buttonText);
                        $('#loading_indicator').hide();
                        
                        DianAdmin.showNotice({
                            type: 'error',
                            title: 'Error de conexión',
                            message: 'No se pudo conectar con el servidor: ' + error,
                            container: $button.closest('.dian-card')
                        });
                    }
                });
            });
        },
        
        // Inicializar carga de certificados
        initCertificateUpload: function() {
            // Manejar la selección de archivos de certificado
            $('#certificate_file').on('change', function(e) {
                var file = e.target.files[0];
                if (!file) {
                    return;
                }

                var fileName = file.name;
                $('#file_name_display').text('Archivo seleccionado: ' + fileName);
                
                // Mostrar indicador de carga
                $('#upload_status')
                    .removeClass('success error')
                    .html('<span class="dian-loader"></span> Procesando archivo...')
                    .show();
                    
                // Leer el archivo como ArrayBuffer
                var reader = new FileReader();
                reader.onload = function(e) {
                    try {
                        // Convertir ArrayBuffer a Base64
                        var arrayBuffer = e.target.result;
                        var bytes = new Uint8Array(arrayBuffer);
                        var binary = '';
                        var len = bytes.byteLength;
                        for (var i = 0; i < len; i++) {
                            binary += String.fromCharCode(bytes[i]);
                        }
                        var base64 = window.btoa(binary);
                        
                        // Actualizar el campo de texto con el valor Base64
                        $('#facturaloperu_api_certificate').val(base64);
                        
                        // Mostrar mensaje de éxito
                        $('#upload_status')
                            .removeClass('error')
                            .addClass('success')
                            .html('<span class="dashicons dashicons-yes-alt"></span> ¡Archivo cargado correctamente! Por favor ingrese la contraseña del certificado.')
                            .show();
                        
                        // Enfocar en el campo de contraseña
                        $('#facturaloperu_api_certificate_password').focus();
                        
                    } catch (error) {
                        console.error('Error al procesar el archivo:', error);
                        $('#upload_status')
                            .removeClass('success')
                            .addClass('error')
                            .html('<span class="dashicons dashicons-warning"></span> Error al procesar el archivo: ' + error.message)
                            .show();
                    }
                };
                
                reader.onerror = function() {
                    $('#upload_status')
                        .removeClass('success')
                        .addClass('error')
                        .html('<span class="dashicons dashicons-warning"></span> Error al leer el archivo. Por favor, inténtelo de nuevo.')
                        .show();
                };
                
                reader.readAsArrayBuffer(file);
            });

            // Enviar certificado a la API
            $('#config_certificate').on('click', function() {
                // Verificar que hay datos antes de enviar
                var certificate = $('#facturaloperu_api_certificate').val();
                var password = $('#facturaloperu_api_certificate_password').val();
                
                if (!certificate || !password) {
                    DianAdmin.showNotice({
                        type: 'error',
                        title: 'Faltan datos',
                        message: 'Por favor, complete tanto el certificado como la contraseña antes de enviar.',
                        container: $(this).closest('.dian-card')
                    });
                    return;
                }
                
                // Mostrar indicador de procesamiento
                var $button = $(this);
                $button.prop('disabled', true);
                const buttonText = $button.text();
                $button.html('<span class="dian-loader"></span> Enviando...');
                
                // Eliminar mensajes anteriores
                $('.response-message').remove();
                
                $.ajax({
                    type: "POST",
                    url: dianAdminData.ajaxUrl,
                    data: {
                        'action': 'api_service_config_certificate'
                    },
                    success: function(response) {
                        $button.prop('disabled', false);
                        $button.html(buttonText);
                        
                        try {
                            // Intentar limpiar la respuesta si contiene "Array"
                            if (typeof response === 'string') {
                                response = response.split('Array').join('');
                                var obj = JSON.parse(response);
                            } else {
                                var obj = response;
                            }
                            
                            if (obj.success === true) {
                                DianAdmin.showNotice({
                                    type: 'success',
                                    title: 'Certificado enviado correctamente',
                                    message: obj.message || 'El certificado ha sido enviado con éxito.',
                                    container: $button.closest('.dian-card')
                                });
                                
                                var formattedResponse = JSON.stringify(obj, undefined, 4);
                                $('#facturaloperu_api_certificate_response').val(formattedResponse);
                            } else {
                                DianAdmin.showNotice({
                                    type: 'error',
                                    title: 'Error',
                                    message: obj.message || 'No se pudo procesar la respuesta',
                                    container: $button.closest('.dian-card')
                                });
                            }
                        } catch (e) {
                            DianAdmin.showNotice({
                                type: 'error',
                                title: 'Error al procesar la respuesta',
                                message: e.message,
                                container: $button.closest('.dian-card')
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        $button.prop('disabled', false);
                        $button.html(buttonText);
                        
                        DianAdmin.showNotice({
                            type: 'error',
                            title: 'Error de conexión',
                            message: 'No se pudo conectar con el servidor: ' + error,
                            container: $button.closest('.dian-card')
                        });
                    }
                });
            });
        },
        
        // Inicializar validación de formularios
        initFormValidation: function() {
            $('.dian-form').on('submit', function(e) {
                const $form = $(this);
                const $requiredFields = $form.find('[required]');
                let isValid = true;
                
                // Verificar campos requeridos
                $requiredFields.each(function() {
                    const $field = $(this);
                    if (!$field.val().trim()) {
                        isValid = false;
                        $field.addClass('dian-field-error');
                        
                        // Agregar mensaje de error si no existe
                        if (!$field.next('.dian-field-error-message').length) {
                            $field.after('<span class="dian-field-error-message">Este campo es obligatorio</span>');
                        }
                    } else {
                        $field.removeClass('dian-field-error');
                        $field.next('.dian-field-error-message').remove();
                    }
                });
                
                // Impedir envío si hay errores
                if (!isValid) {
                    e.preventDefault();
                    
                    // Mostrar mensaje general
                    DianAdmin.showNotice({
                        type: 'error',
                        title: 'Formulario incompleto',
                        message: 'Por favor, complete todos los campos requeridos.',
                        container: $form
                    });
                    
                    // Enfocar en el primer campo con error
                    $form.find('.dian-field-error').first().focus();
                }
                
                // Validar formatos específicos
                const $dateFields = $form.find('.date-field');
                $dateFields.each(function() {
                    const $field = $(this);
                    const value = $field.val().trim();
                    
                    if (value && !/^\d{4}-\d{2}-\d{2}$/.test(value)) {
                        isValid = false;
                        $field.addClass('dian-field-error');
                        
                        // Agregar mensaje de error si no existe
                        if (!$field.next('.dian-field-error-message').length) {
                            $field.after('<span class="dian-field-error-message">Formato de fecha inválido. Use YYYY-MM-DD (ejemplo: 2023-01-31)</span>');
                        }
                    }
                });
                
                return isValid;
            });
            
            // Remover errores al editar campo
            $(document).on('input', '.dian-field-error', function() {
                $(this).removeClass('dian-field-error');
                $(this).next('.dian-field-error-message').remove();
            });
        },
        
        // Inicializar tooltips
        initTooltips: function() {
            // Usar tooltips nativos donde sea posible
            $('.dian-tooltip').each(function() {
                const $tooltip = $(this);
                const title = $tooltip.attr('title');
                
                if (title) {
                    // Preservar título para tooltip nativo
                    $tooltip.attr('data-title', title);
                    // Ajustar título para mostrar ícono de ayuda
                    $tooltip.html('<span class="dashicons dashicons-editor-help"></span>');
                }
            });
        },
        
        // Inicializar campos de respuesta
        initResponseFields: function() {
            // Hacer que los campos de respuesta sean formateados como JSON
            $('.dian-response-field').each(function() {
                const $field = $(this);
                const value = $field.val();
                
                if (value) {
                    try {
                        // Intentar formatear el JSON
                        const jsonObj = JSON.parse(value);
                        const formattedJson = JSON.stringify(jsonObj, null, 2);
                        $field.val(formattedJson);
                    } catch (e) {
                        // Si no es JSON válido, dejarlo como está
                        console.log('No se pudo formatear el campo como JSON:', e);
                    }
                }
            });
        },
        
        // Mostrar notificación
        showNotice: function(options) {
            const defaults = {
                type: 'info', // info, success, error, warning
                title: '',
                message: '',
                container: null,
                duration: 0 // 0 = no auto-ocultar
            };
            
            const settings = $.extend({}, defaults, options);
            
            // Crear elemento de notificación
            const $notice = $('<div class="dian-notice"></div>').addClass(settings.type);
            
            // Agregar icono según tipo
            let icon = 'info';
            if (settings.type === 'success') icon = 'yes-alt';
            if (settings.type === 'error') icon = 'warning';
            if (settings.type === 'warning') icon = 'flag';
            
            $notice.append(`<div class="dian-notice-icon"><span class="dashicons dashicons-${icon}"></span></div>`);
            
            // Contenido
            const $content = $('<div class="dian-notice-content"></div>');
            if (settings.title) {
                $content.append(`<h4>${settings.title}</h4>`);
            }
            if (settings.message) {
                $content.append(`<p>${settings.message}</p>`);
            }
            
            $notice.append($content);
            
            // Agregar al contenedor
            if (settings.container) {
                settings.container.prepend($notice);
            } else {
                $('.dian-admin-container').prepend($notice);
            }
            
            // Auto-ocultar si se especifica duración
            if (settings.duration > 0) {
                setTimeout(function() {
                    $notice.fadeOut(300, function() {
                        $(this).remove();
                    });
                }, settings.duration);
            }
            
            return $notice;
        },
        
        // Manejar respuesta de API
        handleAPIResponse: function(response, $button) {
            try {
                // Verificar formato de respuesta
                let data;
                if (typeof response === 'string') {
                    // Limpiar respuesta
                    response = response.replace(/Array/g, '');
                    
                    // Buscar inicio de JSON válido
                    const jsonStart = response.indexOf('{');
                    if (jsonStart >= 0) {
                        response = response.substring(jsonStart);
                    }
                    
                    data = JSON.parse(response);
                } else {
                    data = response;
                }
                
                // Determinar destino de respuesta
                let $responseField = null;
                const actionType = $button.data('api-action');
                
                // Mapear acciones a campos de respuesta
                const responseFieldMap = {
                    'api_service_config_company': '#facturaloperu_api_config_response',
                    'api_service_config_software': '#facturaloperu_api_software_response',
                    'api_service_config_certificate': '#facturaloperu_api_certificate_response',
                    'api_service_config_resolution': '#facturaloperu_api_resolution_response',
                    'api_service_config_initial': '#facturaloperu_api_initial_response',
                    'api_service_config_environment': '#facturaloperu_api_environment_response',
                    'api_service_config_numbering_ranges': '#facturaloperu_api_numbering_ranges_response'
                };
                
                if (responseFieldMap[actionType]) {
                    $responseField = $(responseFieldMap[actionType]);
                }
                
                // Mostrar notificación según resultado
                if (data.success === true) {
                    DianAdmin.showNotice({
                        type: 'success',
                        title: 'Operación completada',
                        message: data.message || 'La operación se completó con éxito.',
                        container: $button.closest('.dian-card'),
                        duration: 5000
                    });
                    
                    // Actualizar campo de respuesta si existe
                    if ($responseField && $responseField.length) {
                        const formattedJson = JSON.stringify(data, null, 4);
                        $responseField.val(formattedJson);
                    }
                    
                    // Acciones especiales según tipo
                    if (actionType === 'api_service_config_company' && data.token) {
                        $('#facturaloperu_api_config_token').val(data.token);
                    }
                } else {
                    // Mostrar error
                    DianAdmin.showNotice({
                        type: 'error',
                        title: 'Error',
                        message: data.message || 'Ocurrió un error al procesar la solicitud.',
                        container: $button.closest('.dian-card')
                    });
                    
                    // Actualizar campo de respuesta con el error
                    if ($responseField && $responseField.length) {
                        const formattedJson = JSON.stringify(data, null, 4);
                        $responseField.val(formattedJson);
                    }
                }
            } catch (e) {
                // Error al procesar la respuesta
                DianAdmin.showNotice({
                    type: 'error',
                    title: 'Error al procesar la respuesta',
                    message: e.message,
                    container: $button.closest('.dian-card')
                });
                
                console.error('Error procesando respuesta:', e, response);
            }
        }
    };

    // Inicializar cuando el documento esté listo
    $(document).ready(function() {
        DianAdmin.init();
    });

})(jQuery);