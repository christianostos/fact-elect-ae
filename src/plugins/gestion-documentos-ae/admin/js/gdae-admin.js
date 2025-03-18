/**
 * Scripts para el ¨¢rea de administraci¨®n.
 *
 * @link       https://accioneficaz.com
 * @since      1.0.0
 *
 * @package    Gestion_Documentos_AE
 */

jQuery(document).ready(function($) {
    // Inicializar datepicker para el campo de fecha
    if ($('.gdae-datepicker').length) {
        $('.gdae-datepicker').datepicker({
            dateFormat: 'yy-mm-dd',
            changeMonth: true,
            changeYear: true,
            minDate: 0
        });
    }
    
    // Validaci¨®n de formulario
    $('form#post').on('submit', function(e) {
        var usuarioSeleccionado = $('#gdae_usuario_asignado').val();
        var urlCarpeta = $('#gdae_url_carpeta').val();
        
        if (!usuarioSeleccionado) {
            e.preventDefault();
            alert('Debe seleccionar un usuario para asignar la carpeta.');
            $('#gdae_usuario_asignado').focus();
            return false;
        }
        
        if (!urlCarpeta) {
            e.preventDefault();
            alert('Debe proporcionar una URL para la carpeta compartida.');
            $('#gdae_url_carpeta').focus();
            return false;
        }
        
        return true;
    });
    
    // Filtrado de usuarios en el selector
    $('#gdae_usuario_asignado').on('keyup', function() {
        var searchText = $(this).val().toLowerCase();
        $(this).find('option').each(function() {
            var optionText = $(this).text().toLowerCase();
            if (optionText.indexOf(searchText) !== -1) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });
});