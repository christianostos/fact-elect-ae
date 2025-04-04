jQuery(document).ready(function($) {
    // Manejar la selección de archivos
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
            .text('Procesando archivo...')
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
                    .text('¡Archivo cargado y convertido exitosamente! Por favor ingrese la contraseña del certificado.')
                    .show();
                
                // Enfocar en el campo de contraseña
                $('#facturaloperu_api_certificate_password').focus();
                
            } catch (error) {
                console.error('Error al procesar el archivo:', error);
                $('#upload_status')
                    .removeClass('success')
                    .addClass('error')
                    .text('Error al procesar el archivo: ' + error.message)
                    .show();
            }
        };
        
        reader.onerror = function() {
            $('#upload_status')
                .removeClass('success')
                .addClass('error')
                .text('Error al leer el archivo. Por favor, inténtelo de nuevo.')
                .show();
        };
        
        reader.readAsArrayBuffer(file);
    });

    // Enviar certificado a la API
    $('#config_certificate').click(function() {
        // Verificar que hay datos antes de enviar
        var certificate = $('#facturaloperu_api_certificate').val();
        var password = $('#facturaloperu_api_certificate_password').val();
        
        if (!certificate || !password) {
            alert('Por favor, complete tanto el certificado como la contraseña antes de enviar.');
            return;
        }
        
        // Mostrar indicador de procesamiento
        var $button = $(this);
        $button.prop('disabled', true).text('Enviando...');
        
        // Eliminar mensajes anteriores
        $button.nextAll('.response-message').remove();
        
        // Añadir indicador de carga
        $button.after('<span class="response-message">Enviando datos a la API, por favor espere...</span>');
        
        $.ajax({
            type: "POST",
            url: api_script.ajaxurl,
            data: {
                'action': 'api_service_config_certificate'
            },
            success: function(response) {
                // Eliminar indicador de carga
                $('.response-message').remove();
                
                try {
                    // Intentar limpiar la respuesta si contiene "Array"
                    if (typeof response === 'string') {
                        response = response.split('Array').join('');
                        var obj = JSON.parse(response);
                    } else {
                        var obj = response;
                    }
                    
                    if (obj.success == true) {
                        $button.after('<span class="response-message" style="color:green;">Certificado enviado correctamente</span>');
                        var formattedResponse = JSON.stringify(obj, undefined, 4);
                        $('#facturaloperu_api_certificate_response').val(formattedResponse);
                    } else {
                        $button.after('<span class="response-message" style="color:red;">Error: ' + (obj.message || 'No se pudo procesar la respuesta') + '</span>');
                        console.log(obj);
                    }
                } catch (e) {
                    $button.after('<span class="response-message" style="color:red;">Error al procesar la respuesta: ' + e.message + '</span>');
                    console.log('Error al procesar respuesta:', e);
                    console.log('Datos recibidos:', response);
                }
                
                // Re-habilitar el botón
                $button.prop('disabled', false).text('Enviar a API');
            },
            error: function(xhr, status, error) {
                $('.response-message').remove();
                $button.after('<span class="response-message" style="color:red;">Error de conexión: ' + error + '</span>');
                console.log(xhr.responseText);
                
                // Re-habilitar el botón
                $button.prop('disabled', false).text('Enviar a API');
            }
        });
    });
});