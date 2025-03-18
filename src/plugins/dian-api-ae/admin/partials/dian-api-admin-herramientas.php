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
    
    // Generar documentos de prueba
    $('#btn-generar-test').on('click', function() {
        // Implementación pendiente
        alert('Funcionalidad en desarrollo');
    });
});
</script>