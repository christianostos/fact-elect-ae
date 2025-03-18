<?php
/**
 * Clase para gestionar la estructura de directorios del plugin
 *
 * @link       https://accioneficaz.com
 * @since      1.0.0
 *
 * @package    DIAN_API
 * @subpackage DIAN_API/includes
 */

// Evitar acceso directo al archivo
if (!defined('WPINC')) {
    die;
}

/**
 * Clase para gestionar la estructura de directorios
 *
 * @package    DIAN_API
 * @subpackage DIAN_API/includes
 * @author     Christian Ostos - Acción Eficaz
 */
class DIAN_API_Directory_Structure {

    /**
     * Crea la estructura de directorios necesaria para el plugin
     *
     * @since    1.0.0
     * @return   void
     */
    public static function crear_estructura() {
        $directorios = array(
            DIAN_API_PATH . 'admin',
            DIAN_API_PATH . 'includes',
            DIAN_API_PATH . 'assets/css',
            DIAN_API_PATH . 'assets/js',
            DIAN_API_PATH . 'assets/images',
            DIAN_API_PATH . 'templates',
            DIAN_API_PATH . 'lib',
            DIAN_API_PATH . 'lib/phpqrcode',
            DIAN_API_PATH . 'vendor',
            DIAN_API_PATH . 'logs',
            DIAN_API_PATH . 'docs',
            DIAN_API_PATH . 'services',
            DIAN_API_PATH . 'core',
        );
        
        foreach ($directorios as $directorio) {
            if (!file_exists($directorio)) {
                wp_mkdir_p($directorio);
            }
        }
        
        // Crear archivo .htaccess para proteger directorios sensibles
        $directorios_protegidos = array(
            DIAN_API_PATH . 'logs',
        );
        
        foreach ($directorios_protegidos as $directorio) {
            $htaccess = $directorio . '/.htaccess';
            if (!file_exists($htaccess)) {
                file_put_contents($htaccess, "Deny from all\n");
            }
        }
        
        // Crear archivo index.php para evitar listado de directorios
        $directorios_index = array(
            DIAN_API_PATH,
            DIAN_API_PATH . 'admin',
            DIAN_API_PATH . 'includes',
            DIAN_API_PATH . 'assets',
            DIAN_API_PATH . 'assets/css',
            DIAN_API_PATH . 'assets/js',
            DIAN_API_PATH . 'assets/images',
            DIAN_API_PATH . 'templates',
            DIAN_API_PATH . 'lib',
            DIAN_API_PATH . 'logs',
            DIAN_API_PATH . 'docs',
        );
        
        $index_content = "<?php\n// Silence is golden.\n";
        
        foreach ($directorios_index as $directorio) {
            $index_file = $directorio . '/index.php';
            if (!file_exists($index_file)) {
                file_put_contents($index_file, $index_content);
            }
        }
        
        // Copiar documentación
        $doc_source = DIAN_API_PATH . 'docs/API_REST.md';
        if (file_exists($doc_source)) {
            // La documentación ya existe, no hacer nada
        } else {
            // Crear la documentación básica
            $doc_content = "# API de Facturación Electrónica DIAN\n\nDocumentación de la API REST para el plugin de Facturación Electrónica DIAN.\n";
            wp_mkdir_p(DIAN_API_PATH . 'docs');
            file_put_contents($doc_source, $doc_content);
        }
    }
}