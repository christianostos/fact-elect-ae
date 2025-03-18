/**
 * Scripts para el área pública del sitio.
 *
 * @link       https://accioneficaz.com
 * @since      1.0.0
 *
 * @package    Gestion_Documentos_AE
 */

jQuery(document).ready(function($) {
    // Manejar botón de 'Marcar como Contestada'
    $('.gdae-marcar-contestada').on('click', function() {
        var carpetaId = $(this).data('carpeta-id');
        var $button = $(this);
        
        $button.prop('disabled', true).text('Procesando...');
        
        $.ajax({
            url: gdae_vars.ajax_url,
            type: 'POST',
            data: {
                action: 'gdae_marcar_contestada',
                carpeta_id: carpetaId,
                nonce: gdae_vars.nonce
            },
            success: function(response) {
                if (response.success) {
                    // Actualizar estado visualmente
                    $button.closest('.gdae-carpeta-card')
                           .find('.gdae-estado')
                           .removeClass('gdae-estado--publicada')
                           .addClass('gdae-estado--contestada')
                           .text('Contestada');
                    
                    // Remover el botón
                    $button.remove();
                    
                    // Mostrar mensaje de éxito
                    mostrarMensaje(response.data.message, 'success');
                } else {
                    // Mostrar mensaje de error
                    mostrarMensaje(response.data.message, 'error');
                    
                    // Restaurar botón
                    $button.prop('disabled', false).text('Marcar como Contestada');
                }
            },
            error: function() {
                // Mostrar mensaje de error
                mostrarMensaje('Error al procesar la solicitud. Intente de nuevo.', 'error');
                
                // Restaurar botón
                $button.prop('disabled', false).text('Marcar como Contestada');
            }
        });
    });

    // Función para mostrar mensajes
    function mostrarMensaje(mensaje, tipo) {
        // Tipo puede ser 'success' o 'error'
        var $message = $('<div class="gdae-message gdae-message--' + tipo + '"></div>')
            .text(mensaje)
            .appendTo('body')
            .fadeIn();
        
        // Eliminar mensaje después de 3 segundos
        setTimeout(function() {
            $message.fadeOut(function() {
                $(this).remove();
            });
        }, 3000);
    }
    
    // Animación suave al cargar la página
    $('.gdae-carpeta-card').each(function(index) {
        $(this).css({
            'opacity': 0,
            'transform': 'translateY(20px)'
        });
        
        setTimeout(function() {
            $(this).animate({
                'opacity': 1,
                'transform': 'translateY(0)'
            }, 300);
        }.bind(this), index * 100);
    });

    // Manejar la visualización de la carpeta embebida
    $('.gdae-ver-carpeta').on('click', function(e) {
        e.preventDefault();
        var url = $(this).data('url');
        var title = $(this).data('title');
        
        // Establecer título
        $('#gdae-carpeta-preview-title').text(title || 'Contenido de la Carpeta');
        
        // Convertir la URL de visualización normal a URL de embebido
        var embedUrl = convertirUrlGoogleDrive(url);
        
        // Establecer la URL del iframe
        $('#gdae-carpeta-iframe').attr('src', embedUrl);
        
        // Mostrar el overlay y el contenedor
        $('.gdae-overlay').fadeIn();
        $('#gdae-carpeta-preview-container').fadeIn();
    });

    // Cerrar la previsualización (botón de cerrar)
    $('.gdae-carpeta-preview__close').on('click', function() {
        cerrarPreview();
    });
    
    // Cerrar la previsualización (clic en overlay)
    $('.gdae-overlay').on('click', function() {
        cerrarPreview();
    });
    
    // Función para cerrar la previsualización
    function cerrarPreview() {
        $('.gdae-overlay').fadeOut();
        $('#gdae-carpeta-preview-container').fadeOut(function() {
            // Limpiar iframe al cerrar
            $('#gdae-carpeta-iframe').attr('src', '');
        });
    }

    // Función para convertir URL de Google Drive a formato embebible
    function convertirUrlGoogleDrive(url) {
        // Extraer el ID de la carpeta de la URL
        var matches = url.match(/\/folders\/([a-zA-Z0-9-_]+)/);
        if (matches && matches[1]) {
            var folderId = matches[1];
            // Formato para vista de lista (también existe #grid para vista en cuadrícula)
            return 'https://drive.google.com/embeddedfolderview?id=' + folderId + '#list';
        }
        return url;
    }
});