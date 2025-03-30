/**
 * Script para manejar las notificaciones administrativas
 */
(function($) {
    'use strict';

    // Manejar el cierre de notificaciones
    $(document).on('click', '.dian-api-notice .notice-dismiss', function() {
        var $notice = $(this).closest('.dian-api-notice');
        var noticeId = $notice.attr('id').replace('dian-api-notice-', '');
        
        // Añadir clase para animación de salida
        $notice.addClass('is-dismissing');
        
        // Esperar a que termine la animación
        setTimeout(function() {
            $notice.remove();
        }, 300);
        
        // Si es una notificación persistente, enviar AJAX para removerla
        if ($notice.hasClass('dian-api-dismissible')) {
            $.ajax({
                url: dianApiNotices.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'dian_api_dismiss_notice',
                    notice_id: noticeId,
                    nonce: dianApiNotices.nonce
                }
            });
        }
    });
    
    // Función para mostrar notificaciones desde JavaScript
    window.dianApiShowNotice = function(message, type, dismissible, duration) {
        type = type || 'info';
        dismissible = dismissible !== false;
        duration = duration || 0;
        
        // Generar ID único
        var noticeId = 'js_notice_' + Date.now();
        
        // Crear clase
        var noticeClass = 'notice notice-' + type + ' dian-api-notice';
        if (dismissible) {
            noticeClass += ' is-dismissible';
        }
        
        // Crear HTML de la notificación
        var $notice = $(
            '<div id="dian-api-notice-' + noticeId + '" class="' + noticeClass + '">' +
            '<p>' + message + '</p>' +
            (dismissible ? '<button type="button" class="notice-dismiss"><span class="screen-reader-text">Descartar este aviso.</span></button>' : '') +
            '</div>'
        );
        
        // Insertar al principio del contenedor de notificaciones o al inicio del contenido
        if ($('.wrap > h1').length) {
            $notice.insertAfter('.wrap > h1');
        } else {
            $notice.prependTo('.wrap');
        }
        
        // Si tiene duración, desaparecer después
        if (duration > 0) {
            setTimeout(function() {
                $notice.find('.notice-dismiss').trigger('click');
            }, duration);
        }
        
        return noticeId;
    };
    
    // Reemplazar los alert nativos en las páginas del plugin
    if (window.location.href.indexOf('dian-api') > -1) {
        window.originalAlert = window.alert;
        
        window.alert = function(message) {
            // Solo reemplazar en las páginas del plugin
            if (window.location.href.indexOf('dian-api') > -1) {
                dianApiShowNotice(message, 'warning', true, 5000);
            } else {
                window.originalAlert(message);
            }
        };
    }
    
    // Capturar errores de AJAX
    $(document).ajaxError(function(event, jqXHR, ajaxSettings, thrownError) {
        // Solo para peticiones del plugin
        if (ajaxSettings.url && ajaxSettings.url.indexOf('dian-api') > -1) {
            var errorMessage = 'Error en la petición: ';
            
            if (jqXHR.responseJSON && jqXHR.responseJSON.message) {
                errorMessage += jqXHR.responseJSON.message;
            } else if (jqXHR.responseText) {
                errorMessage += jqXHR.responseText;
            } else {
                errorMessage += thrownError || 'Error desconocido';
            }
            
            dianApiShowNotice(errorMessage, 'error', true, 7000);
        }
    });
    
})(jQuery);