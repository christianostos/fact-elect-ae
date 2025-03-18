/**
 * Scripts para la página de ajustes del plugin
 */
jQuery(document).ready(function($) {
    console.log('GDAE Settings JS loaded - Versión corregida');
    
    // Inicializar las pestañas
    function initTabs() {
        $('.gdae-tab-button').on('click', function(e) {
            e.preventDefault();
            
            var tabId = $(this).data('tab');
            console.log('Pestaña seleccionada:', tabId);
            
            // Quitar clase activa de todas las pestañas
            $('.gdae-tab-button').removeClass('active');
            $('.gdae-tab-panel').removeClass('active');
            
            // Agregar clase activa a la pestaña seleccionada
            $(this).addClass('active');
            $('#gdae-tab-' + tabId).addClass('active');
        });
    }
    
    // Inicializar los botones de licencia
    function initLicenseButtons() {
        $('#gdae_activate_license').on('click', function() {
            var licenseKey = $('#gdae_license_key').val();
            var $button = $(this);
            var $statusWrap = $('#gdae_license_status_wrap');
            
            if (!licenseKey) {
                alert('Por favor, ingrese una clave de licencia.');
                $('#gdae_license_key').focus();
                return;
            }
            
            $button.prop('disabled', true).text('Activando...');
            
            $.ajax({
                type: 'POST',
                url: ajaxurl,
                data: {
                    action: 'gdae_activate_license',
                    license_key: licenseKey,
                    nonce: gdae_license.nonce
                },
                success: function(response) {
                    if (response.success) {
                        $statusWrap.html('<span class="gdae-license-status gdae-license-valid"><span class="dashicons dashicons-yes"></span> Licencia activa</span>');
                        setTimeout(function() { location.reload(); }, 1500);
                    } else {
                        $statusWrap.html('<span class="gdae-license-status gdae-license-invalid"><span class="dashicons dashicons-no"></span> Licencia inactiva</span>');
                        $('<div class="notice notice-error"><p>' + (response.data || 'Error al activar la licencia') + '</p></div>')
                            .insertAfter($statusWrap);
                        $button.prop('disabled', false).text('Activar');
                    }
                },
                error: function(xhr, status, error) {
                    $statusWrap.html('<span class="gdae-license-status gdae-license-invalid"><span class="dashicons dashicons-no"></span> Licencia inactiva</span>');
                    $('<div class="notice notice-error"><p>Error al conectar con el servidor: ' + error + '</p></div>')
                        .insertAfter($statusWrap);
                    $button.prop('disabled', false).text('Activar');
                }
            });
        });
        
        $(document).on('click', '#gdae_deactivate_license', function() {
            if (!confirm('¿Está seguro de que desea desactivar esta licencia?')) {
                return;
            }
            
            var $button = $(this);
            $button.prop('disabled', true).text('Desactivando...');
            
            $.ajax({
                type: 'POST',
                url: ajaxurl,
                data: {
                    action: 'gdae_deactivate_license',
                    nonce: gdae_license.nonce
                },
                success: function(response) {
                    location.reload();
                },
                error: function() {
                    $button.prop('disabled', false).text('Desactivar');
                    alert('Error al desactivar la licencia.');
                }
            });
        });
    }
    
    // Inicializar formulario de contacto
    function initContactForm() {
        $('#gdae-quick-support-form').on('submit', function(e) {
            e.preventDefault();
            
            var $form = $(this);
            var $message = $form.find('.gdae-form-message');
            var $submitButton = $form.find('.gdae-quick-contact-btn');
            
            $submitButton.prop('disabled', true).text('Enviando...');
            
            setTimeout(function() {
                $message.html('<div class="gdae-license-status gdae-license-valid"><span class="dashicons dashicons-yes"></span> Mensaje enviado correctamente.</div>');
                $form.trigger('reset');
                $submitButton.prop('disabled', false).text('Enviar Mensaje');
            }, 1000);
        });
    }
    
    // Intentar inicializar con un pequeño retraso para asegurar que todos los elementos están cargados
    setTimeout(function() {
        initTabs();
        initLicenseButtons();
        initContactForm();
        console.log('Inicialización completa');
    }, 100);
});