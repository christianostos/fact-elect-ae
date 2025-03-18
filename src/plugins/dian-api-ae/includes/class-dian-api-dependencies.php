<?php
/**
 * Clase para manejar las dependencias del plugin
 *
 * Esta clase se encarga de comprobar y cargar las dependencias necesarias
 * para el funcionamiento del plugin de facturación electrónica DIAN.
 *
 * @link       https://tudominio.com/plugin-dian-api
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
 * Clase para manejar las dependencias del plugin
 *
 * @package    DIAN_API
 * @subpackage DIAN_API/includes
 * @author     Tu Nombre <email@example.com>
 */
class DIAN_API_Dependencies {

    /**
     * Comprueba si todas las dependencias están disponibles
     *
     * @since    1.0.0
     * @return   bool   True si todas las dependencias están disponibles, false en caso contrario
     */
    public static function check_dependencies() {
        $dependencies_available = true;
        
        // Verificar mPDF pero solo mostrar aviso, no intentar cargar
        if (!class_exists('\Mpdf\Mpdf')) {
            $dependencies_available = false;
            add_action('admin_notices', array(__CLASS__, 'mpdf_missing_notice'));
        }
        
        // Verificar PHP QR Code
        if (!class_exists('QRcode')) {
            // Si no está disponible, lo incluiremos manualmente
            $qr_lib_path = plugin_dir_path(dirname(__FILE__)) . 'lib/phpqrcode/qrlib.php';
            
            if (file_exists($qr_lib_path)) {
                require_once $qr_lib_path;
            } else {
                $dependencies_available = false;
                add_action('admin_notices', array(__CLASS__, 'phpqrcode_missing_notice'));
            }
        }
        
        return $dependencies_available;
    }

    /**
     * Muestra una notificación cuando mPDF no está disponible
     *
     * @since    1.0.0
     * @return   void
     */
    public static function mpdf_missing_notice() {
        ?>
        <div class="notice notice-warning">
            <p>
                <strong>Plugin Facturación Electrónica DIAN:</strong> 
                La generación de PDF está desactivada porque no se encontró la biblioteca mPDF. La generación de XML y comunicación con la DIAN sigue funcionando normalmente.
            </p>
            <p>
                Para activar la generación de PDF, instale la biblioteca mPDF:
                <ul>
                    <li>Usando Composer: <code>composer require mpdf/mpdf</code></li>
                    <li>O manualmente: Descargue mPDF desde <a href="https://github.com/mpdf/mpdf/releases" target="_blank">GitHub</a> y extraiga en <code>vendor/mpdf/mpdf</code></li>
                </ul>
            </p>
        </div>
        <?php
    }

    /**
     * Muestra una notificación cuando PHP QR Code no está disponible
     *
     * @since    1.0.0
     * @return   void
     */
    public static function phpqrcode_missing_notice() {
        ?>
        <div class="notice notice-warning">
            <p>
                <strong>Plugin Facturación Electrónica DIAN:</strong> 
                Se requiere la biblioteca PHP QR Code para generar códigos QR. Por favor, descargue la biblioteca desde
                <a href="https://phpqrcode.sourceforge.net/" target="_blank">https://phpqrcode.sourceforge.net/</a>
                y colóquela en <code>lib/phpqrcode/</code>.
            </p>
        </div>
        <?php
    }

    /**
     * Asegura que todas las carpetas necesarias existan
     *
     * @since    1.0.0
     * @return   void
     */
    public static function create_required_directories() {
        $plugin_dir = plugin_dir_path(dirname(__FILE__));
        
        // Carpeta para bibliotecas
        if (!file_exists($plugin_dir . 'lib')) {
            wp_mkdir_p($plugin_dir . 'lib');
        }
        
        // Carpeta para plantillas
        if (!file_exists($plugin_dir . 'templates')) {
            wp_mkdir_p($plugin_dir . 'templates');
        }
        
        // Carpeta para archivos temporales
        if (!file_exists($plugin_dir . 'temp')) {
            wp_mkdir_p($plugin_dir . 'temp');
        }
        
        // Crear un archivo .htaccess para proteger la carpeta temp
        $htaccess = $plugin_dir . 'temp/.htaccess';
        if (!file_exists($htaccess)) {
            file_put_contents($htaccess, "Deny from all\n");
        }
    }

    /**
     * Descarga y extrae la biblioteca PHP QR Code si no está instalada
     *
     * @since    1.0.0
     * @return   bool     True si la instalación fue exitosa, false en caso contrario
     */
    public static function download_phpqrcode() {
        $plugin_dir = plugin_dir_path(dirname(__FILE__));
        $qr_lib_path = $plugin_dir . 'lib/phpqrcode';
        
        // Si ya existe, no hacer nada
        if (file_exists($qr_lib_path . '/qrlib.php')) {
            return true;
        }
        
        return false; // Por seguridad, no intentamos descargar automáticamente
    }
}